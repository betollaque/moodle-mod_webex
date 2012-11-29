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
 * webex module main user interface
 *
 * @package    mod
 * @subpackage webex
 * @copyright  2012 Carlos kiyan, Walter Castillo , Luis Fukay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/webex/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // webex instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

if ($u) {  // Two ways to specify the module
    $url = $DB->get_record('webex', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('webex', $url->id, $url->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('webex', $id, 0, false, MUST_EXIST);
    $url = $DB->get_record('webex', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/webex:view', $context);

add_to_log($course->id, 'webex', 'view', 'view.php?id='.$cm->id, $url->id, $cm->id);

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/webex/view.php', array('id' => $cm->id));

// Make sure webex exists before generating output - some older sites may contain empty webexs
// Do not use PARAM_webex here, it is too strict and does not support general URIs!
$extwebex = trim($url->externalurl);
if (empty($extwebex) or $extwebex === 'http://') {
    webex_print_header($url, $cm, $course);
    webex_print_heading($url, $cm, $course);
    webex_print_intro($url, $cm, $course);
    notice(get_string('invalidstoredwebex', 'webex'), new moodle_url('/course/view.php', array('id'=>$cm->course)));
    die;
}
unset($extwebex);

if ($redirect) {
    // coming from course page or webex index page,
    // the redirection is needed for completion tracking and logging
    $fullurl = webex_get_full_webex($url, $cm, $course);
    redirect(str_replace('&amp;', '&', $fullurl));
}

switch (webex_get_final_display_type($url)) {
    case RESOURCELIB_DISPLAY_EMBED:
        webex_display_embed($url, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        webex_display_frame($url, $cm, $course);
        break;
    default:
        webex_print_workaround($url, $cm, $course);
        break;
}
