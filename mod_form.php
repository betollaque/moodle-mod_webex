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
 * webex configuration form
 *
 * @package    mod
 * @subpackage webex
 * @copyright  2012 Carlos kiyan, Walter Castillo , Luis Fukay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/webex/locallib.php');

class mod_webex_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        $config = get_config('webex');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $this->add_intro_editor($config->requiremodintro);

        //-------------------------------------------------------
        $mform->addElement('header', 'content', get_string('contentheader', 'webex'));
        $mform->addElement('text', 'externalurl', get_string('externalurl', 'webex'), array('size'=>'48')); 
		$mform->addElement('static', 'parametersinfo', '', 'http://<b style="color:red">yoursite</b>.webex.com/');
        $mform->addRule('externalurl', null, 'required', null, 'client');
        //-------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('optionsheader', 'webex'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'webex'), $options);
            $mform->setDefault('display', $config->display);
            $mform->setAdvanced('display', $config->display_adv);
            $mform->addHelpButton('display', 'displayselect', 'webex');
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'webex'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);
            $mform->setAdvanced('popupwidth', $config->popupwidth_adv);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'webex'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
            $mform->setAdvanced('popupheight', $config->popupheight_adv);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_FRAME, $options)) {
            $mform->addElement('checkbox', 'printheading', get_string('printheading', 'webex'));
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printheading', $config->printheading);
            $mform->setAdvanced('printheading', $config->printheading_adv);

            $mform->addElement('checkbox', 'printintro', get_string('printintro', 'webex'));
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printintro', $config->printintro);
            $mform->setAdvanced('printintro', $config->printintro_adv);
        }

        //-------------------------------------------------------
        $mform->addElement('header', 'parameterssection', get_string('parametersheader', 'webex'));
        $mform->addElement('static', 'parametersinfo', '', get_string('parametersheader_help', 'webex'));
        //$mform->setAdvanced('parametersinfo');
      		
				$mform->addElement('text', 'variable_0', '', array('value'=>'AT','style'=>'display:none'));
                $mform->addElement('text', 'parameter_0', '', array('value'=>'JM','style'=>'display:none'));
			
			
              $mform->addElement('text', 'variable_1', '', array('value'=>'MK','readonly'=>'readonly','style'=>'display:none'));
			  $mform->addElement('text', 'parameter_1', get_string('mettingkey', 'webex'), array('size'=>'12'));
			  $mform->addElement('text', 'variable_2', '', array('value'=>'PW','readonly'=>'yes','style'=>'display:none'));
			  $mform->addElement('text', 'parameter_2', get_string('password', 'webex'), array('size'=>'12'));

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $default_values['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
        }
        if (!empty($default_values['parameters'])) {
            $parameters = unserialize($default_values['parameters']);
            $i = 0;
            foreach ($parameters as $parameter=>$variable) {
                $default_values['parameter_'.$i] = $parameter;
                $default_values['variable_'.$i]  = $variable;
                $i++;
            }
        }
    }

    function validation($data, $files) {
         $errors = parent::validation($data, $files);

		// Validating Entered webex, we are looking for obvious problems only,
        // teachers are responsible for testing if it actually works.

        // This is not a security validation!! Teachers are allowed to enter "javascript:alert(666)" for example.

        // NOTE: do not try to explain the difference between webex and URI, people would be only confused...

        if (empty($data['externalurl'])) {
            $errors['externalurl'] = get_string('required');

        } else {
            $url = trim($data['externalurl']);
           
        }
        return $errors;
    }

}
