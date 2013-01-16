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
 * webex module admin settings and defaults
 *
 * @package    mod
 * @subpackage webex
 * @copyright  2012 Carlos kiyan, Walter Castillo , Luis Fukay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
														   RESOURCELIB_DISPLAY_FRAME,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_OPEN,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('webex/framesize',
        get_string('framesize', 'webex'), get_string('configframesize', 'webex'), 130, PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('webex/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 1));
    $settings->add(new admin_setting_configpasswordunmask('webex/secretphrase', get_string('password'),
        get_string('configsecretphrase', 'webex'), ''));
    $settings->add(new admin_setting_configcheckbox('webex/rolesinparams',
        get_string('rolesinparams', 'webex'), get_string('configrolesinparams', 'webex'), false));
    $settings->add(new admin_setting_configmultiselect('webex/displayoptions',
        get_string('displayoptions', 'webex'), get_string('configdisplayoptions', 'webex'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('webexmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox_with_advanced('webex/printheading',
        get_string('printheading', 'webex'), get_string('printheadingexplain', 'webex'),
        array('value'=>0, 'adv'=>false)));
    $settings->add(new admin_setting_configcheckbox_with_advanced('webex/printintro',
        get_string('printintro', 'webex'), get_string('printintroexplain', 'webex'),
        array('value'=>1, 'adv'=>false)));
    $settings->add(new admin_setting_configselect_with_advanced('webex/display',
        get_string('displayselect', 'webex'), get_string('displayselectexplain', 'webex'),
        array('value'=>RESOURCELIB_DISPLAY_AUTO, 'adv'=>false), $displayoptions));
    $settings->add(new admin_setting_configtext_with_advanced('webex/popupwidth',
        get_string('popupwidth', 'webex'), get_string('popupwidthexplain', 'webex'),
        array('value'=>620, 'adv'=>true), PARAM_INT, 7));
    $settings->add(new admin_setting_configtext_with_advanced('webex/popupheight',
        get_string('popupheight', 'webex'), get_string('popupheightexplain', 'webex'),
        array('value'=>450, 'adv'=>true), PARAM_INT, 7));
}
