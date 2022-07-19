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
 * @package     mod_lticontainer
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


function setup_tabs($current_tab, $course_id, $cm_id, $context, $minstance) 
{
    global $CFG, $PAGE;

    $row = array();
    $url_params = ['id' => $cm_id];

    // Overview
    $row[] = make_tabobj( 'over_view_tab', get_string('over_view_tab', 'mod_lticontainer'), '/mod/lticontainer/view.php', $url_params);

    // for Demo
    //$row[] = make_tabobj('show_demo_tab', get_string('show_demo_tab', 'mod_lticontainer'), '/mod/lticontainer/actions/show_demo.php', $url_params);

    // Dashboard Tab
    if ($minstance->use_dashboard==1 and has_capability('mod/lticontainer:dashboard_view', $context)) {
        $row[] = make_tabobj('dashboard_view_tab', get_string('dashboard_view_tab', 'mod_lticontainer'), '/mod/lticontainer/actions/dashboard_view.php', $url_params);
    }

    // View Chart Tab
    if ($current_tab=='chart_view_tab' and has_capability('mod/lticontainer:chart_view', $context)) {
        $row[] = make_tabobj('chart_view_tab', get_string('chart_view_tab', 'mod_lticontainer'), '/mod/lticontainer/actions/chart_view.php', $url_params);
    }

    // View LTI Setting
    if ($current_tab=='lti_setting_tab' and has_capability('mod/lticontainer:lti_setting', $context)) {
        $row[] = make_tabobj('lti_setting_tab', get_string('lti_setting_tab', 'mod_lticontainer'), '/mod/lticontainer/actions/lti_etting.php', $url_params);
    }

    // View LTI Connections
    if (has_capability('mod/lticontainer:lti_view', $context)) {
        $row[] = make_tabobj('lti_view_tab', get_string('lti_view_tab', 'mod_lticontainer'), '/mod/lticontainer/actions/lti_view.php', $url_params);
    }

    // View LTI Edit
    if ($current_tab=='lti_edit_tab' and has_capability('mod/lticontainer:lti_view', $context)) {
        $lti_id = required_param('lti_id', PARAM_INT);
        $url_params = $url_params;
        $edit_params['lti_id'] = $lti_id;
        $row[] = make_tabobj('lti_edit_tab', get_string('lti_edit_tab', 'mod_lticontainer'), '/mod/lticontainer/actions/lti_edit.php', $edit_params);
    }

    // View Volumes
    if (has_capability('mod/lticontainer:volume_view', $context)) {
        $row[] = make_tabobj('volume_view_tab', get_string('volume_view_tab', 'mod_lticontainer'), '/mod/lticontainer/actions/volume_view.php', $url_params);
    }

    // JupyterHub API
/*
    $show_jhuser_tab_student = true;
    if (has_capability('mod/lticontainer:jupyterhub_api', $context)) {
        $row[] = make_tabobj('jupyterhub_api_tab', get_string('jupyterhub_api_tab', 'mod_lticontainer'), '/mod/lticontainer/actions/jupyterhub_api.php', $url_params);
        $show_jhuser_tab_student = false;
    }

    // JupyterHub API for user
    if ($current_tab=='jupyterhub_user_tab' and has_capability('mod/lticontainer:jupyterhub_user', $context)) {
        $row[] = make_tabobj('jupyterhub_user_tab', get_string('jupyterhub_user_tab', 'mod_lticontainer'), '/mod/lticontainer/actions/jupyterhub_user.php', $url_params);
        $show_jhuser_tab_student = false;
    }
*/

    // JupyterHub API for student user
    //if ($show_jhuser_tab_student) {
        $row[] = make_tabobj('jupyterhub_user_tab', get_string('jupyterhub_user_tab', 'mod_lticontainer'), '/mod/lticontainer/actions/jupyterhub_user.php', $url_params);
    //}

    // Admin Tools
    if (has_capability('mod/lticontainer:admin_tools', $context)) {
        //$row[] = make_tabobj('admin_tools_tab', get_string('admin_tools_tab', 'mod_lticontainer'), '/mod/lticontainer/actions/admin_tools.php', $url_params);
    }

    // Return to Course
    $row[] = make_tabobj('', get_string('returnto_course_tab', 'mod_lticontainer'), $CFG->wwwroot.'/course/view.php', ['id' => $course_id]);

    return $row;
}


function  echo_tabs($current_tab, $course_id, $cm_id, $context, $instance) 
{
    isset($cm_id)     || die();
    $cm_id > 0        || die();
    isset($course_id) || die();
    isset($context)   || die();

    if (!isset($current_tab)) {
        $current_tab = '';
    }

    $tabs = array();
    $row  = setup_tabs($current_tab, $course_id, $cm_id, $context, $instance); 
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
