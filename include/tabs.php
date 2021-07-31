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
 * prints the tabbed bar
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mdlds
 */

defined('MOODLE_INTERNAL') OR die('not allowed');

$tabs = array();
$row  = array();
$inactive  = array();
$activated = array();

//some pages deliver the cmid instead the id
if (isset($cmid) and intval($cmid) and $cmid>0) {
    $used_id = $cmid;
}
else {
    $used_id = $id;
}
if (!$courseid) $courseid = optional_param('courseid', false, PARAM_INT);

//
$context = context_module::instance($used_id);

if (!isset($current_tab)) {
    $current_tab = '';
}

// Overview
$viewurl = new moodle_url('/mod/mdlds/view.php', array('id'=>$used_id, 'do'=>'over_view'));
$row[]   = new tabobject('over_view', $viewurl->out(), get_string('over_view', 'mdlds'));

// for Demo
$demourl = new moodle_url('/mod/mdlds/actions/show_demo.php', array('id'=>$used_id, 'do'=>'show_demo'));
$row[]   = new tabobject('show_demo', $demourl->out(), get_string('show_demo', 'mdlds'));

// View Volumes
if (has_capability('mod/mdlds:view_volumes', $context)) {
    $url_params = array('id'=>$used_id, 'do'=>'view_volumes', 'sort'=>'time_modified', 'order'=>'DESC');
    $volume_url = new moodle_url('/mod/mdlds/actions/view_volumes.php', $url_params);
    $row[]      = new tabobject('view_volumes', $volume_url->out(), get_string('view_volumes', 'mdlds'));
}

// View LTI Connections
if (has_capability('mod/mdlds:lti_connect', $context)) {
    $url_params = array('id'=>$used_id, 'do'=>'lti_connect');
    $cnnect_url = new moodle_url('/mod/mdlds/actions/lti_connect.php', $url_params);
    $row[]      = new tabobject('lti_connect', $cnnect_url->out(), get_string('lti_connect', 'mdlds'));
}

// Return Course
$row[] = new tabobject('', $CFG->wwwroot.'/course/view.php?id='.$courseid, get_string('returnto_course', 'mdlds'));

//
if (count($row) > 1) {
    $tabs[] = $row;
    echo '<table align="center" style="margin-bottom:0.0em;"><tr><td>';
    echo '<style type="text/css">';
    include(__DIR__.'/../html/styles.css');
    echo '</style>';
    print_tabs($tabs, $current_tab, $inactive, $activated);
    echo '</td></tr></table>';
}

