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

require_once(__DIR__.'/../include/tabs.php');    // for echo_tabs()
require_once(__DIR__.'/../classes/event/show_demo.php');


//ltids_init_session();
//$SESSION->ltids->is_started = false;

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

///////////////////////////////////////////////////////////////////////////
$urlparams = array();
$urlparams['id']       = $cmid;
$urlparams['courseid'] = $courseid;

$current_tab = 'show_demo_tab';
$this_action = 'show_demo';

// Event
$event = ltids_get_event($cmid, $this_action, $urlparams);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('ltids', $minstance);
$event->trigger();

///////////////////////////////////////////////////////////////////////////
// URL
$base_url = new moodle_url('/mod/ltids/actions/'.$this_action.'.php');
$base_url->params($urlparams);
$this_url = new moodle_url($base_url);

///////////////////////////////////////////////////////////////////////////
// Event
//$event = apply_get_event($cm, 'over_view', $urlparams);
//jbxl_add_to_log($event);

///////////////////////////////////////////////////////////////////////////
// Print the page header
$PAGE->navbar->add(get_string('ltids:show_demo', 'mod_ltids'));
$PAGE->set_url($this_url, $urlparams);
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);

echo $OUTPUT->header();
echo_tabs($current_tab, $courseid, $cmid, $mcontext);

require_once(__DIR__.'/../classes/show_demo.class.php');

$show_demo = new ShowDemo($cmid, $courseid);

$show_demo->set_condition();
$show_demo->execute();
$show_demo->print_page();

///////////////////////////////////////////////////////////////////////////
/// Finish the page
echo $OUTPUT->footer($course);

