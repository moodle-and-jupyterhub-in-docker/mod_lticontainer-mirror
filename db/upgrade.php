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
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_ltids
 * @category    upgrade
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/upgradelib.php');

/**
 * Execute mod_ltids upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_ltids_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();

    // For further information please read {@link https://docs.moodle.org/dev/Upgrade_API}.
    //
    // You will also have to create the db/install.xml file by using the XMLDB Editor.
    // Documentation for the XMLDB Editor can be found at {@link https://docs.moodle.org/dev/XMLDB_editor}.

    // 2021080603
    if ($oldversion < 2021080603) {
        $table = new xmldb_table('ltids');
        //
        $field = new xmldb_field('docker_host', XMLDB_TYPE_CHAR, '128', null, null, null, 'localhost', 'introformat');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        //
        $field = new xmldb_field('docker_user', XMLDB_TYPE_CHAR, '64', null, null, null, 'docker', 'docker_host');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        //
        $field = new xmldb_field('docker_pass', XMLDB_TYPE_CHAR, '64', null, null, null, '', 'docker_user');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    // 2021080605
    if ($oldversion < 2021080605) {
        $table = new xmldb_table('ltids');
        //
        $field = new xmldb_field('custom_params', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'docker_pass');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    // 2021082701
    if ($oldversion < 2021082701) {
        $table = new xmldb_table('ltids_websock_session');
        //
        $field = new xmldb_field('inst_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'course');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    // 2021082804
    if ($oldversion < 2021082804) {
        $table = new xmldb_table('ltids');
        //
        $field = new xmldb_field('no_disp_lti', XMLDB_TYPE_CHAR, '255', null, null, null, '', 'custom_params');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    // 2021083000
    if ($oldversion < 2021083000) {
        $table = new xmldb_table('ltids');
        //
        $field = new xmldb_field('make_volumes', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'custom_params');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    // 2021091000
    if ($oldversion < 2021091000) {
        $table = new xmldb_table('ltids');
        //
        $field = new xmldb_field('imgname_fltr', XMLDB_TYPE_CHAR, '255', null, null, null, 'jupyter, notebook, ltids', 'custom_params');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    // 20210902101
    if ($oldversion < 2021092101) {
        $table = new xmldb_table('ltids');
        //
        $field = new xmldb_field('use_podman', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'no_disp_lti');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }


    // 2021122701
    if ($oldversion < 2021122701) {
        $table = new xmldb_table('ltids_websock_server_data');

        $table->add_field('id',       XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('session',  XMLDB_TYPE_CHAR,    '42', null, XMLDB_NOTNULL, null, '');
        $table->add_field('message',  XMLDB_TYPE_CHAR,    '42', null, XMLDB_NOTNULL, null, '');
        $table->add_field('status',   XMLDB_TYPE_CHAR,    '10', null, null,          null, null);
        $table->add_field('username', XMLDB_TYPE_CHAR,    '32', null, null,          null, null);
        $table->add_field('date',     XMLDB_TYPE_CHAR,    '32', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('updatetm', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    // 2021122701
    if ($oldversion < 2021122701) {
        $table = new xmldb_table('ltids_websock_client_data');

        $table->add_field('id',       XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('session',  XMLDB_TYPE_CHAR,    '42', null, XMLDB_NOTNULL, null, '');
        $table->add_field('message',  XMLDB_TYPE_CHAR,    '42', null, XMLDB_NOTNULL, null, '');
        $table->add_field('cell_id',  XMLDB_TYPE_CHAR,    '42', null, null,          null, null);
        $table->add_field('date',     XMLDB_TYPE_CHAR,    '32', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('updatetm', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    // 2021122702
    if ($oldversion < 2021122702) {
        $table = new xmldb_table('ltids_websock_tags');
        //
        $field = new xmldb_field('filename', XMLDB_TYPE_CHAR, '256', null, null, null, null, 'tags');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    // 2021122702
    if ($oldversion < 2021122702) {
        $table = new xmldb_table('ltids_websock_tags');
        //
        $field = new xmldb_field('codenum', XMLDB_TYPE_CHAR, '12', null, null, null, null, 'filename');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }


    // 2021122817
    if ($oldversion < 2021122817) {
        $table = new xmldb_table('ltids_websock_client_data');
        $index = new xmldb_index('session', XMLDB_INDEX_NOTUNIQUE, array('session'));
        $dbman->add_index($table, $index);
        $index = new xmldb_index('message', XMLDB_INDEX_NOTUNIQUE, array('message'));
        $dbman->add_index($table, $index);
    }

    // 2021122817
    if ($oldversion < 2021122817) {
        $table = new xmldb_table('ltids_websock_server_data');
        $index = new xmldb_index('session', XMLDB_INDEX_NOTUNIQUE, array('session'));
        $dbman->add_index($table, $index);
        $index = new xmldb_index('message', XMLDB_INDEX_NOTUNIQUE, array('message'));
        $dbman->add_index($table, $index);
    }

    // 2021122817
    if ($oldversion < 2021122817) {
        $table = new xmldb_table('ltids_websock_session');
        $index = new xmldb_index('session', XMLDB_INDEX_NOTUNIQUE, array('session'));
        $dbman->add_index($table, $index);
    }

    // 2021122817
    if ($oldversion < 2021122817) {
        $table = new xmldb_table('ltids_websock_tags');
        $index = new xmldb_index('cell_id', XMLDB_INDEX_UNIQUE, array('cell_id'));
        $dbman->add_index($table, $index);
    }


    // 2022010501
    if ($oldversion < 2022010501) {
        $table = new xmldb_table('ltids');

        $field = new xmldb_field('use_dashboard',       XMLDB_TYPE_INTEGER, '1',  null, XMLDB_NOTNULL, null, '0',     'use_podman');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('during_dashboard',    XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '86400', 'use_dashboard');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('during_chart',        XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '5400',  'during_dashboard');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('chart_bar_usernum',   XMLDB_TYPE_INTEGER, '5',  null, XMLDB_NOTNULL, null, '10',    'during_chart');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('chart_bar_codenum',   XMLDB_TYPE_INTEGER, '5',  null, XMLDB_NOTNULL, null, '15',    'chart_bar_usernum');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('chart_line_usernum',  XMLDB_TYPE_INTEGER, '5',  null, XMLDB_NOTNULL, null, '15',    'chart_bar_codenum');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('chart_line_interval', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1800',  'chart_line_usernum');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }


    // 2022011100
    if ($oldversion < 2022011100) {
        $table = new xmldb_table('ltids');

        $field = new xmldb_field('during_realtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '5400',  'use_dashboard');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('during_anytime',  XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '604800', 'during_realtime');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    // 2022011101
    if ($oldversion < 2022011101) {
        $table = new xmldb_table('ltids');
        //
        $field = new xmldb_field('during_dashboard');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        //
        $field = new xmldb_field('during_chart');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
    }


    return true;
}
