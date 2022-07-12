<?php
/**
 * dashboard_view.php
 *
 * @package     mod_lticontainer
 * @copyright   2021 Urano Masanori and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../locallib.php');

require_once(__DIR__.'/../include/tabs.php');    // for echo_tabs()
require_once(__DIR__.'/../classes/event/dashboard_view.php');

// Course module id.
$cmid = required_param('id', PARAM_INT);
$cm   = get_coursemodule_from_id('lticontainer', $cmid, 0, false, MUST_EXIST);

$course    = $DB->get_record('course', array('id' => $cm->course),   '*', MUST_EXIST);
$minstance = $DB->get_record('lticontainer',  array('id' => $cm->instance), '*', MUST_EXIST);

$mcontext = context_module::instance($cm->id);
$courseid = $course->id;


///////////////////////////////////////////////////////////////////////////
// Check
require_login($course, true, $cm);

$lticontainer_dashboard_view_cap = false;
if (has_capability('mod/lticontainer:dashboard_view', $mcontext)) {
    $lticontainer_dashdoard_view_cap = true;
}

///////////////////////////////////////////////////////////////////////////
$urlparams = array('id' => $cmid);
$current_tab = 'dashboard_view_tab';
$this_action = 'dashboard_view';

///////////////////////////////////////////////////////////////////////////
// URL
$base_url = new moodle_url('/mod/lticontainer/actions/'.$this_action.'.php');
$base_url->params($urlparams);
$this_url = new moodle_url($base_url);

///////////////////////////////////////////////////////////////////////////
// Event
$event = lticontainer_get_event($cmid, $this_action, $urlparams);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('lticontainer', $minstance);
$event->trigger();


///////////////////////////////////////////////////////////////////////////
// Print the page header
$PAGE->navbar->add(get_string('lticontainer:dashboard_view', 'mod_lticontainer'));
$PAGE->set_url($this_url, $urlparams);
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);


echo $OUTPUT->header();
echo_tabs($current_tab, $courseid, $cmid, $mcontext, $minstance);

if ($lticontainer_dashdoard_view_cap) {
    require_once(__DIR__.'/../classes/dashboard_view.class.php');
    $dashboard_view = new DashboardView($cmid, $courseid, $minstance);
    $dashboard_view->set_condition();
    $dashboard_view->execute();
    $dashboard_view->print_page();
}

echo $OUTPUT->footer($course);

