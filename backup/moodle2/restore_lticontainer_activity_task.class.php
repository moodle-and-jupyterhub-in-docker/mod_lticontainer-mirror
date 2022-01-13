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
 * The task that provides a complete restore of mod_lticontainer is defined here.
 *
 * @package     mod_lticontainer
 * @category    backup
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// More information about the backup process: {@link https://docs.moodle.org/dev/Backup_API}.
// More information about the restore process: {@link https://docs.moodle.org/dev/Restore_API}.

require_once($CFG->dirroot.'//mod/lticontainer/backup/moodle2/restore_lticontainer_stepslib.php');

/**
 * Restore task for mod_lticontainer.
 */
class restore_lticontainer_activity_task extends restore_activity_task
{
    /**
     * Defines particular settings that this activity can have.
     */
    protected function define_my_settings() {
        return;
    }

    /**
     * Defines particular steps that this activity can have.
     *
     * @return base_step.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_lticontainer_activity_structure_step('lticontainer_structure', 'lticontainer.xml'));
    }

    /**
     * Defines the contents in the activity that must be processed by the link decoder.
     *
     * @return array.
     */
    public static function define_decode_contents() {
        $contents = array();

        // Define the contents.

        $contents[] = new restore_decode_content('lticontainer', array('intro'), 'lticontainer');

        return $contents;
    }

    /**
     * Defines the decoding rules for links belonging to the activity to be executed by the link decoder.
     *
     * @return array.
     */
    public static function define_decode_rules() {
        $rules = array();

        // Define the rules.

        return $rules;
    }

    /**
     * Defines the restore log rules that will be applied by the
     * {@see restore_logs_processor} when restoring mod_lticontainer logs. It
     * must return one array of {@see restore_log_rule} objects.
     *
     * @return array.
     */
    public static function define_restore_log_rules() {
        $rules = array();

        // Define the rules.
        $rules[] = new restore_log_rule('lticontainer', 'over_view',     'view.php?id={course_module}',                '{lticontainer}');
        $rules[] = new restore_log_rule('lticontainer', 'lti_view',      'actions/lti_view.php?id={course_module}',    '{lticontainer}');
        $rules[] = new restore_log_rule('lticontainer', 'lti_setting',   'actions/lti_view.php?id={course_module}',    '{lticontainer}');
        $rules[] = new restore_log_rule('lticontainer', 'lti_edit',      'actions/lti_edit.php?id={course_module}',    '{lticontainer}');
        $rules[] = new restore_log_rule('lticontainer', 'volume_view',   'actions/volume_view.php?id={course_module}', '{lticontainer}');
        $rules[] = new restore_log_rule('lticontainer', 'volume_delete', 'actione/volume_view.php?id={course_module}', '{lticontainer}');
        $rules[] = new restore_log_rule('lticontainer', 'dashboard_view','actions/dashboard.php?id={course_module}',   '{lticontainer}');

        return $rules;
    }
}
