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
 * Use Moodle's Charts API to visualize learning data.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../locallib.php');

require_once(__DIR__.'/../include/tabs.php');    // for echo_tabs()
require_once(__DIR__.'/../classes/data_provider.class.php'); // for DataProvider
require_once(__DIR__.'/../classes/event/lti_edit.php');

// Course module id.
$cmid = required_param('id', PARAM_INT);
$cm   = get_coursemodule_from_id('ltids', $cmid, 0, false, MUST_EXIST);

$course    = $DB->get_record('course', array('id' => $cm->course),   '*', MUST_EXIST);
$minstance = $DB->get_record('ltids',  array('id' => $cm->instance), '*', MUST_EXIST);

$mcontext = context_module::instance($cm->id);
$courseid = $course->id;


///////////////////////////////////////////////////////////////////////////
// Check
require_login($course, true, $cm);

$ltids_dashboard_view_cap = false;
if (has_capability('mod/ltids:dashboard_view', $mcontext)) {
    $ltids_dashdoard_view_cap = true;
}

///////////////////////////////////////////////////////////////////////////
$urlparams = array('id' => $cmid);
$current_tab = 'dashboard_view_tab';
$this_action = 'dashboard_view';

///////////////////////////////////////////////////////////////////////////
// URL
$base_url = new moodle_url('/mod/ltids/actions/'.$this_action.'.php');
$base_url->params($urlparams);
$this_url = new moodle_url($base_url);

///////////////////////////////////////////////////////////////////////////
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

require_once(__DIR__.'/../classes/dashboard_view.class.php');

if ($ltids_dashdoard_view_cap) {
    $dashboard_view = new DashboardView($cmid, $courseid, $minstance);
    $dashboard_view->set_condition();
    $dashboard_view->execute();
    $dashboard_view->print_page();
}

echo $OUTPUT->footer($course);

