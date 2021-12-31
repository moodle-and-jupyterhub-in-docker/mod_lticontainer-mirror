<?php

/**
 * chart_view.php 
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp> and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../locallib.php');

require_once(__DIR__.'/../include/tabs.php');    // for echo_tabs()
require_once(__DIR__.'/../classes/event/chart_view.php');

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

$ltids_chart_view_cap = false;
if (has_capability('mod/ltids:chart_view', $mcontext)) {
    $ltids_dashdoard_view_cap = true;
}

///////////////////////////////////////////////////////////////////////////
$urlparams = array('id' => $cmid);
$current_tab = 'chart_view_tab';
$this_action = 'chart_view';

///////////////////////////////////////////////////////////////////////////
// URL
$base_url = new moodle_url('/mod/ltids/actions/'.$this_action.'.php');
$base_url->params($urlparams);
$this_url = new moodle_url($base_url);

///////////////////////////////////////////////////////////////////////////
// Event
$event = ltids_get_event($cmid, $this_action, $urlparams);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('ltids',  $minstance);
$event->trigger();


///////////////////////////////////////////////////////////////////////////
// Print the page header
$PAGE->navbar->add(get_string('ltids:chart_view', 'mod_ltids'));
$PAGE->set_url($this_url, $urlparams);
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);


echo $OUTPUT->header();
echo_tabs($current_tab, $courseid, $cmid, $mcontext);

if ($ltids_dashdoard_view_cap) {
    require_once(__DIR__.'/../classes/chart_view.class.php');
    $chart_view = new ChartView($cmid, $courseid, $minstance);
    $chart_view->set_condition();
    $chart_view->execute();
    $chart_view->print_page();
}

echo $OUTPUT->footer($course);

