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
 * Display the tab menu.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Modified by Fumi.Iseki
 */


defined('MOODLE_INTERNAL') || die();


function make_tabobj($uniqueid, $title, $filepath, $urlparams) 
{
    $urltogo = new moodle_url($filepath, $urlparams);
    $tabobj  = new tabobject( $uniqueid, $urltogo->out(), $title);

    return $tabobj;
}


function setup_tabs($current_tab, $course_id, $cm_id, $context) 
{
    global $CFG;

    $row = array();
    $url_params = ['id' => $cm_id];

    // Overview
    $row[] = make_tabobj( 'over_view_tab', get_string('over_view_tab', 'mod_ltids'), '/mod/ltids/view.php', $url_params);

    // for Demo
    //$row[] = make_tabobj('show_demo_tab', get_string('show_demo_tab', 'mod_ltids'), '/mod/ltids/actions/show_demo.php', $url_params);

    // View LTI Connections
    if (has_capability('mod/ltids:lti_view', $context)) {
        $row[] = make_tabobj('lti_view_tab', get_string('lti_view_tab', 'mod_ltids'), '/mod/ltids/actions/lti_view.php', $url_params);
    }

    // View LTI Edit
    if ($current_tab=='lti_edit_tab' and has_capability('mod/ltids:lti_edit', $context)) {
        $row[] = make_tabobj('lti_edit_tab', get_string('lti_edit_tab', 'mod_ltids'), '/mod/ltids/actions/lti_edit.php', $url_params);
    }

    // Dashboard Tab
    if (has_capability('mod/ltids:dashboard_view', $context)) {
        $row[] = make_tabobj('dashboard_view_tab', get_string('dashboard_view_tab', 'mod_ltids'), '/mod/ltids/actions/dashboard_view.php', $url_params);
    }

    // View Volumes
    if (has_capability('mod/ltids:volume_view', $context)) {
        $row[] = make_tabobj('volume_view_tab', get_string('volume_view_tab', 'mod_ltids'), '/mod/ltids/actions/volume_view.php', $url_params);
    }

    // View LTI Setting
    if ($current_tab=='lti_setting_tab' and has_capability('mod/ltids:lti_setting', $context)) {
        $row[] = make_tabobj('lti_setting_tab', get_string('lti_setting_tab', 'mod_ltids'), '/mod/ltids/actions/lti_etting.php', $url_params);
    }

    // Dump DB Tab
    //if (has_capability('mod/ltids:check_db', $context)) {
        $row[] = make_tabobj('dump_db_tab', get_string('dump_db_tab', 'mod_ltids'), '/mod/ltids/actions/dump_db.php', $url_params);
    //}
    
    // Return to Course
    $row[] = make_tabobj('', get_string('returnto_course_tab', 'mod_ltids'), $CFG->wwwroot.'/course/view.php', ['id' => $course_id]);

    return $row;
}


function  echo_tabs($current_tab, $course_id, $cm_id, $context) 
{
    isset($cm_id)     || die();
    $cm_id > 0        || die();
    isset($course_id) || die();
    isset($context)   || die();

    if (!isset($current_tab)) {
        $current_tab = '';
    }

    $tabs = array();
    $row  = setup_tabs($current_tab, $course_id, $cm_id, $context); 
    $inactive  = array();
    $activated = array();

    if(count($row) > 1) {
        $tabs[] = $row;
        echo '<table align="center" style="margin-bottom:0.0em;"><tr><td>';
        echo '<style type="text/css">';
        include(__DIR__.'/../html/styles.css');
        echo '</style>';
        print_tabs($tabs, $current_tab, $inactive, $activated);
        echo '</td></tr></table>';
    }
}
