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
 * Prints an instance of mod_ltids.
 *
 * @package     mod_ltids
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../locallib.php');

require_once(__DIR__.'/../include/tabs.php');    // for echo_tabs()
require_once(__DIR__.'/../classes/event/volume_view.php');
require_once(__DIR__.'/../classes/event/volume_delete.php');


$cmid = required_param('id', PARAM_INT);                                                    // コースモジュール ID
$cm   = get_coursemodule_from_id('ltids', $cmid, 0, false, MUST_EXIST);                     // コースモジュール

$course    = $DB->get_record('course', array('id'=>$cm->course),   '*', MUST_EXIST);        // コースデータ from DB
$minstance = $DB->get_record('ltids',  array('id'=>$cm->instance), '*', MUST_EXIST);        // モジュールインスタンス

$mcontext = context_module::instance($cm->id);                                              // モジュールコンテキスト
$ccontext = context_course::instance($course->id);                                          // コースコンテキスト

$courseid = $course->id;
$user_id  = $USER->id;


///////////////////////////////////////////////////////////////////////////
// Check
require_login($course, true, $cm);
//
$ltids_volume_view_cap = false;
if (has_capability('mod/ltids:volume_view', $mcontext)) {
    $ltids_volume_view_cap = true;
}

///////////////////////////////////////////////////////////////////////////
$urlparams = array();
$urlparams['id'] = $cmid;

$current_tab = 'volume_view_tab';
$this_action = 'volume_view';

///////////////////////////////////////////////////////////////////////////
// URL
$base_url = new moodle_url('/mod/ltids/actions/'.$this_action.'.php');
$base_url->params($urlparams);
$this_url = new moodle_url($base_url);

// Event
$event = ltids_get_event($cmid, $this_action, $urlparams);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('ltids', $minstance);
$event->trigger();


///////////////////////////////////////////////////////////////////////////
// Print the page header
$PAGE->navbar->add(get_string('ltids:volume_view', 'mod_ltids'));
$PAGE->set_url($this_url, $urlparams);
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);

echo $OUTPUT->header();
echo_tabs($current_tab, $courseid, $cmid, $mcontext);

require_once(__DIR__.'/../classes/volume_view.class.php');

if ($ltids_volume_view_cap) {
    $volume_view = new VolumeView($cmid, $courseid, $minstance);
    $volume_view->set_condition();
    $volume_view->execute();
    $volume_view->print_page();
}

echo $OUTPUT->footer($course);

