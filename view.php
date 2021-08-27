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
 * Prints an instance of mod_mdlds.
 *
 * @package     mod_mdlds
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/locallib.php');
require_once(__DIR__.'/classes/event/over_view.php');

// Course module id.
$cmid       = optional_param('id', 0, PARAM_INT);           // コースモジュール ID
$instanceid = optional_param('m',  0, PARAM_INT);           // インスタンス ID
$courseid   = optional_param('course', false, PARAM_INT);

$current_tab = 'over_view';
$this_action = 'over_view';


////////////////////////////////////////////////////////
//get the objects
if ($cmid) {
    $cm = get_coursemodule_from_id('mdlds', $cmid, 0, false, MUST_EXIST);                  // コースモジュール
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);      // コースデータ from DB
    $minstance = $DB->get_record('mdlds', array('id' => $cm->instance), '*', MUST_EXIST);  // モジュールインスタンス
} 
else {
    $minstance = $DB->get_record('mdlds', array('id' => $instanceid), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $minstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('mdlds', $minstance->id, $course->id, false, MUST_EXIST);
}

$mcontext = context_module::instance($cm->id);
$ccontext = context_course::instance($course->id);
if (!$courseid)   $courseid = $course->id;
if (!$cmid)       $cmid = $cm->id;
if (!$instanceid) $instanceid = $minstance->id;

////////////////////////////////////////////////////////
// Check
require_login($course, true, $cm);

//
$event = mdlds_get_event($cmid, $this_action);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('mdlds',  $minstance);
$event->trigger();


///////////////////////////////////////////////////////////////////////////
// Print the page header
$PAGE->navbar->add(get_string('mdlds:over_view', 'mod_mdlds'));
$PAGE->set_url('/mod/mdlds/view.php', array('id' => $cm->id, 'm' => $instanceid));
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);

echo $OUTPUT->header();


///////////////////////////////////////////////////////////////////////////
require('include/tabs.php');

echo '<div align="center">';
echo $OUTPUT->heading(format_text($minstance->name), 3);
echo '</div>';
//

include('html/overview.html');

include('version.php');
echo '<div align="center">';
echo '<a href="'.get_string('wiki_url', 'mod_mdlds').'" target="_blank"><i>mod_mdlds '.$plugin->release.'</i></a>';
echo '<br />';
echo '</div>';
///////////////////////////////////////////////////////////////////////////
/// Finish the page
echo $OUTPUT->footer();

