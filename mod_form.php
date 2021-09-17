<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The main mod_ltids configuration form.
 *
 * @package     mod_ltids
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_ltids
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_ltids_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition()
    {
        global $CFG;

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('ltidsname', 'mod_ltids'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'ltidsname', 'mod_ltids');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements(get_string('description', 'mod_ltids'));
        } else {
            $this->add_intro_editor(true, get_string('description', 'mod_ltids'));
        }

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'ltidsfieldset', get_string('ltidsfieldset', 'mod_ltids'));

        $mform->addElement('text', 'docker_host', get_string('docker_host', 'mod_ltids'), array('size' => '64'));
        $mform->addHelpButton('docker_host', 'docker_host', 'mod_ltids');
        $mform->setType('docker_host', PARAM_TEXT);
        $mform->setDefault('docker_host', 'localhost');

        $mform->addElement('text', 'docker_user', get_string('docker_user', 'mod_ltids'), array('size' => '32'));
        $mform->addHelpButton('docker_user', 'docker_user', 'mod_ltids');
        $mform->setType('docker_user', PARAM_TEXT);
        $mform->setDefault('docker_user', 'docker');

        $mform->addElement('password', 'docker_pass', get_string('docker_pass', 'mod_ltids'), array('size' => '32'));
        $mform->addHelpButton('docker_pass', 'docker_pass', 'mod_ltids');
        $mform->setType('docker_pass', PARAM_TEXT);
        $mform->setDefault('docker_pass', 'pass');

        $mform->addElement('selectyesno', 'custom_params', get_string('show_custom_params', 'mod_ltids'));
        $mform->addHelpButton('custom_params', 'show_custom_params', 'mod_ltids');
        $mform->setType('custom_params', PARAM_INT);
        $mform->setDefault('custom_params', 0);

        $mform->addElement('text', 'imgname_fltr', get_string('imagename_filter', 'mod_ltids'), array('size' => '96'));
        $mform->addHelpButton('imgname_fltr', 'imagename_filter', 'mod_ltids');
        $mform->setType('imgname_fltr', PARAM_TEXT);
        $mform->setDefault('imgname_fltr', 'jupyter, notebook, ltids');

        $mform->addElement('selectyesno', 'make_volumes', get_string('make_docker_volumes', 'mod_ltids'));
        $mform->addHelpButton('make_volumes', 'make_docker_volumes', 'mod_ltids');
        $mform->setType('make_volumes', PARAM_INT);
        $mform->setDefault('make_volumes', 0);

        //-------------------------------------------------------------------------------
        // Add standard elements.
        $this->standard_coursemodule_elements();
        //$mform->setAdvanced('cmidnumber');

        //-------------------------------------------------------------------------------
        // Add standard buttons.
        $this->add_action_buttons();
    }
}
