<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Private webex module utility functions
 *
 * @package    mod
 * @subpackage webex
 * @copyright  2012 Carlos kiyan, Walter Castillo , Luis Fukay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/webex/lib.php");

/**
 * This methods does weak webex validation, we are looking for major problems only,
 * no strict RFE validation.
 *
 * @param $url
 * @return bool true is seems valid, false if definitely not valid webex
 */
function webex_appears_valid_webex($url) {
    if (preg_match('/^(\/|https?:|ftp:)/i', $url)) {
        // note: this is not exact validation, we look for severely malformed webexs only
        return preg_match('/^[a-z]+:\/\/([^:@\s]+:[^@\s]+@)?[a-z0-9_\.\-]+(:[0-9]+)?(\/[^#]*)?(#.*)?$/i', $url);
    } else {
        return preg_match('/^[a-z]+:\/\/...*$/i', $url);
    }
}

/**
 * Fix common webex problems that we want teachers to see fixed
 * the next time they edit the resource.
 *
 * This function does not include any XSS protection.
 *
 * @param string $url
 * @return string
 */
function webex_fix_submitted_webex($url) {
    // note: empty webexs are prevented in form validation
    $url = trim($url);

    // remove encoded entities - we want the raw URI here
    $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');

    if (!preg_match('|^[a-z]+:|i', $url) and !preg_match('|^/|', $url)) {
        // invalid URI, try to fix it by making it normal webex,
        // please note relative webexs are not allowed, /xx/yy links are ok
        $url = $url;
    }

    return $url;
}

/***************************************************************************************************************************************************
 * Return full webex with all extra parameters
 *
 * This function does not include any XSS protection.
 *
 * @param string $url
 * @param object $cm
 * @param object $course
 * @param object $config
 * @return string webex with & encoded as &amp;
 */
function webex_get_full_webex($url, $cm, $course, $config=null) {
global $USER;
    $parameters = empty($url->parameters) ? array() : unserialize($url->parameters);

    // make sure there are no encoded entities, it is ok to do this twice
    $fullurl = html_entity_decode($url->externalurl, ENT_QUOTES, 'UTF-8');

    if (preg_match('/^(\/|https?:|ftp:)/i', $fullurl) or preg_match('|^/|', $fullurl)) {
        // encode extra chars in webexs - this does not make it always valid, but it helps with some UTF-8 problems
        $allowed = "a-zA-Z0-9".preg_quote(';/?:@=&$_.+!*(),-#%', '/');
        $fullurl = preg_replace_callback("/[^$allowed]/", 'webex_filter_callback', $fullurl);
    } else {
        // encode special chars only
        $fullurl = str_replace('"', '%22', $fullurl);
        $fullurl = str_replace('\'', '%27', $fullurl);
        $fullurl = str_replace(' ', '%20', $fullurl);
        $fullurl = str_replace('<', '%3C', $fullurl);
        $fullurl = str_replace('>', '%3E', $fullurl);
    }

    // add variable webex parameters
    if (!empty($parameters)) {
        if (!$config) {
            $config = get_config('webex');
        }
        $paramvalues = webex_get_variable_values($url, $cm, $course, $config);

        foreach ($parameters as $parse=>$parameter) {
            if (isset($paramvalues[$parameter])) {
                $parameters[$parse] = rawurlencode($paramvalues[$parameter]).'='.rawurlencode($parse);
            } else {
                unset($parameters[$parse]);
            }
        }
		if (!empty($parameters)) {
				$fullurl = 'https://'.$fullurl.'.webex.com/'.$fullurl.'/m.php?'.implode('&', $parameters).'&AN='.fullname($USER).'&AE='.$USER->email;       
				 }
    }

    // encode all & to &amp; entity
    $fullurl = str_replace('&', '&amp;', $fullurl);

    return $fullurl;
}


/**
 * Unicode encoding helper callback
 * @internal
 * @param array $matches
 * @return string
 */
function webex_filter_callback($matches) {
    return rawurlencode($matches[0]);
}

/**
 * Print webex header.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @return void
 */
function webex_print_header($url, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$url->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($url);
    echo $OUTPUT->header();
}

/**
 * Print webex heading.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function webex_print_heading($url, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions);

    if ($ignoresettings or !empty($options['printheading'])) {
        echo $OUTPUT->heading(format_string($url->name), 2, 'main', 'webexheading');
    }
}

/**
 * Print webex introduction.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function webex_print_intro($url, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($url->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'webexintro');
            echo format_module_intro('webex', $url, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Display webex frames.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function webex_display_frame($url, $cm, $course) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        webex_print_header($url, $cm, $course);
        webex_print_heading($url, $cm, $course);
        webex_print_intro($url, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('webex');
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        $extewebex = webex_get_full_webex($url, $cm, $course, $config);
        $navwebex = "$CFG->wwwroot/mod/webex/view.php?id=$cm->id&amp;frameset=top";
        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
        $title = strip_tags($courseshortname.': '.format_string($url->name));
        $framesize = $config->framesize;
        $modulename = s(get_string('modulename','webex'));
        $dir = get_string('thisdirection', 'langconfig');

        $extframe = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navwebex" title="$modulename"/>
    <frame src="$extewebex" title="$modulename"/>
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $extframe;
        die;
    }
}

/******************************************************************************************************************************************
 * Print webex info and link.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function webex_print_workaround($url, $cm, $course) {
    global $OUTPUT;

    webex_print_header($url, $cm, $course);
    webex_print_heading($url, $cm, $course, true);
    webex_print_intro($url, $cm, $course, true);

    $fullurl = webex_get_full_webex($url, $cm, $course);

    $display = webex_get_final_display_type($url);
    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $jsfullwebex = addslashes_js($fullurl);
        $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$jsfullwebex', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="urlworkaround"><a href="'.$fullurl.'" >';
	//get_string('clicktoopen', 'webex');
    print_string('clicktoopen', 'webex', "");
    echo '</a></div>';

    echo $OUTPUT->footer();
    die;
}

/*******************************************************************************************************************************************************
 * Display embedded webex file.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function webex_display_embed($url, $cm, $course) {
    global $CFG, $PAGE, $OUTPUT;

    $mimetype = resourcelib_guess_webex_mimetype($url->externalurl);
    $fullurl  = webex_get_full_webex($url, $cm, $course);
    $title    = $url->name;

    $link = html_writer::tag('a', $fullurl, array('href'=>str_replace('&amp;', '&', $fullurl)));
    $clicktoopen = get_string('clicktoopen', 'webex', $link);

    $extension = resourcelib_get_extension($url->externalurl);

    if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {  // It's an image
        $code = resourcelib_embed_image($fullurl, $title);

    } else if ($mimetype == 'audio/mp3') {
        // MP3 audio file
        $code = resourcelib_embed_mp3($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/x-flv' or $extension === 'f4v') {
        // Flash video file
        $code = resourcelib_embed_flashvideo($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'application/x-shockwave-flash') {
        // Flash file
        $code = resourcelib_embed_flash($fullurl, $title, $clicktoopen);

    } else if (substr($mimetype, 0, 10) == 'video/x-ms') {
        // Windows Media Player file
        $code = resourcelib_embed_mediaplayer($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/quicktime') {
        // Quicktime file
        $code = resourcelib_embed_quicktime($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/mpeg') {
        // Mpeg file
        $code = resourcelib_embed_mpeg($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'audio/x-pn-realaudio-plugin') {
        // RealMedia file
        $code = resourcelib_embed_real($fullurl, $title, $clicktoopen);

    } else {
        // anything else - just try object tag enlarged as much as possible
        $code = resourcelib_embed_general($fullurl, $title, $clicktoopen, "text/html"); //--------------------------------------------------------------------------
    }

    webex_print_header($url, $cm, $course);
    webex_print_heading($url, $cm, $course);

    echo $code;

    webex_print_intro($url, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

/********************************************************************************************************************************************************
 * Decide the best display format.
 * @param object $url
 * @return int display type constant
 */
function webex_get_final_display_type($url) {
    global $CFG;

    if ($url->display != RESOURCELIB_DISPLAY_AUTO) {
        return $url->display;
    }


    // detect links to local moodle pages
    if (strpos($url->externalurl, $CFG->wwwroot) === 0) {
        if (strpos($url->externalurl, 'file.php') === false and strpos($url->externalurl, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg', 'video/mp4',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = resourcelib_guess_webex_mimetype($url->externalurl);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}


/*********************************************************************************************************************************************************
 * Get the parameter values that may be appended to webex
 * @param object $url module instance
 * @param object $cm
 * @param object $course
 * @param object $config module config options
 * @return array of parameter values
 */
function webex_get_variable_values($url, $cm, $course, $config) {
    global $USER, $CFG;

    $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);

    $values = array (
        'AT' => 'AT',
        'MK' => 'MK',
		'PW' => 'PW',
    );

    // weak imitation of Single-Sign-On, for backwards compatibility only
    // NOTE: login hack is not included in 2.0 any more, new contrib auth plugin
    //       needs to be createed if somebody needs the old functionality!
    if (!empty($config->secretphrase)) {
        $values['encryptedcode'] = webex_get_encrypted_parameter($url, $config);
    }
 

    return $values;
}

/**
 * BC internal function
 * @param object $url
 * @param object $config
 * @return string
 */
function webex_get_encrypted_parameter($url, $config) {
    global $CFG;

    if (file_exists("$CFG->dirroot/local/externserverfile.php")) {
        require_once("$CFG->dirroot/local/externserverfile.php");
        if (function_exists('extern_server_file')) {
            return extern_server_file($url, $config);
        }
    }
    return md5(getremoteaddr().$config->secretphrase);
}