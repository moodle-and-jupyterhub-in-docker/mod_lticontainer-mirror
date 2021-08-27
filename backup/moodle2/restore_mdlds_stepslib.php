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
 * All the steps to restore mod_mdlds are defined here.
 *
 * @package     mod_mdlds
 * @category    backup
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// More information about the backup process: {@link https://docs.moodle.org/dev/Backup_API}.
// More information about the restore process: {@link https://docs.moodle.org/dev/Restore_API}.

/**
 * Defines the structure step to restore one mod_mdlds activity.
 */
class restore_mdlds_activity_structure_step extends restore_activity_structure_step 
{
    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[].
     */
    protected function define_structure() {
        $paths = array();
        //$userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('mdlds', '/activity/mdlds');
        $paths[] = new restore_path_element('mdlds_websock_session', '/activity/mdlds/sessions/session');

        return $this->prepare_activity_structure($paths);
    }


    protected function process_mdlds($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->course       = $this->get_courseid();
        $data->timecreated  = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('mdlds', $data);
        $this->apply_activity_instance($newitemid);
    }


    protected function process_mdlds_websock_session($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->course   = $this->get_courseid();
        $data->inst_id  = $this->get_new_parentid('mdlds');
        $data->updatetm = $this->apply_date_offset($data->updatetm);

        $newitemid = $DB->insert_record('mdlds_wedsock_session', $data);
        $this->apply_activity_instance($newitemid);
    }


    /**
     * Defines post-execution actions.
     */
    protected function after_execute() {
        return;
    }
}

