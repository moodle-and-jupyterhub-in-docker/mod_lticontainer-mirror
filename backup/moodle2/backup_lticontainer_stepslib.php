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
 * Backup steps for lticontainer are defined here.
 *
 * @package     mod_lticontainer
 * @category    backup
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// More information about the backup process:  {@link https://docs.moodle.org/dev/Backup_API}.
// More information about the restore process: {@link https://docs.moodle.org/dev/Restore_API}.

/**
 * Define the complete structure for backup, with file and id annotations.
 */
class backup_lticontainer_activity_structure_step extends backup_activity_structure_step
{
    /**
     * Defines the structure of the resulting xml file.
     *
     * @return backup_nested_element The structure wrapped by the common 'activity' element.
     */
    protected function define_structure() 
    {
        //$userinfo = $this->get_setting_value('userinfo');

        // Replace with the attributes and final elements that the element will handle.
        $attributes = array('id');
        $finalelements = array('name', 'timecreated', 'timemodified', 'intro', 'introformat', 'docker_host', 'docker_user', 'docker_pass', 
                               'jupyterhub_url', 'custom_params', 'imgname_fltr', 'make_volumes', 'display_lti', 'use_podman', 'api_token', 'rpc_token',
                               'use_dashboard', 'during_realtime', 'during_anytime', 'chart_bar_usernum', 'chart_bar_codenum', 'chart_line_usernum', 'chart_line_interval');
        $lticontainer = new backup_nested_element('lticontainer', $attributes, $finalelements);

        $finalelements = array('session', 'lti_id', 'updatetm');
        $session = new backup_nested_element('lticontainer_session', $attributes, $finalelements);

        $sessions = new backup_nested_element('sessions');
        
        // Build the tree with these elements with $root as the root of the backup tree.
        $lticontainer->add_child($sessions);
        $sessions->add_child($session);

        // Define the source tables for the elements.
        $lticontainer->set_source_table('lticontainer', array('id' => backup::VAR_ACTIVITYID));
        $session->set_source_table('lticontainer_session', array('inst_id' => backup::VAR_PARENTID));

        // Define id annotations.

        // Define file annotations.
        $lticontainer->annotate_files('lticontainer', 'intro', null); 

        return $this->prepare_activity_structure($lticontainer);
    }
}
