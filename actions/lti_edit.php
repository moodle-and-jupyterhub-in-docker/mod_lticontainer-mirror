<?php
/**
 * lti_edit.php
 *
 * @package     mod_lticontainer
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../locallib.php');

require_once(__DIR__.'/../include/tabs.php');    // for echo_tabs()
require_once(__DIR__.'/../classes/event/lti_view.php');
require_once(__DIR__.'/../classes/event/lti_edit.php');


$cmid = required_param('id', PARAM_INT);                                                    // コースモジュール ID
$cm   = get_coursemodule_from_id('lticontainer', $cmid, 0, false, MUST_EXIST);              // コースモジュール

$course    = $DB->get_record('course', array('id'=>$cm->course),   '*', MUST_EXIST);        // コースデータ from DB
$minstance = $DB->get_record('lticontainer',  array('id'=>$cm->instance), '*', MUST_EXIST); // モジュールインスタンス

$mcontext = context_module::instance($cm->id);                                              // モジュールコンテキスト
$ccontext = context_course::instance($course->id);                                          // コースコンテキスト

$courseid = $course->id;
$user_id  = $USER->id;

$lti_id = required_param('lti_id', PARAM_INT);


///////////////////////////////////////////////////////////////////////////
// Check
require_login($course, true, $cm);
//
$lticontainer_lti_view_cap = false;
if (has_capability('mod/lticontainer:lti_view', $mcontext)) {
    $lticontainer_lti_view_cap = true;
}

///////////////////////////////////////////////////////////////////////////
$urlparams = array('id' => $cmid, 'lti_id' => $lti_id);
$current_tab = 'lti_edit_tab';
$this_action = 'lti_edit';

///////////////////////////////////////////////////////////////////////////
// URL
$base_url = new moodle_url('/mod/lticontainer/actions/'.$this_action.'.php');
$base_url->params($urlparams);
$this_url = new moodle_url($base_url);

///////////////////////////////////////////////////////////////////////////
// Event
if (data_submitted()) {
    $event = lticontainer_get_event($cmid, $this_action, $urlparams);
}
else {
    $event = lticontainer_get_event($cmid, 'lti_view',   $urlparams);
}
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('lticontainer', $minstance);
$event->trigger();


///////////////////////////////////////////////////////////////////////////
// Print the page header
$PAGE->navbar->add(get_string('lticontainer:lti_edit', 'mod_lticontainer'));
$PAGE->set_url($this_url, $urlparams);
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);

echo $OUTPUT->header();
echo_tabs($current_tab, $courseid, $cmid, $mcontext, $minstance);

if ($lticontainer_lti_view_cap) { 
    require_once(__DIR__.'/../classes/lti_edit.class.php');
    $lti_edit = new LTIEdit($cmid, $courseid, $minstance);
    $lti_edit->set_condition();
    $lti_edit->execute();
    $lti_edit->print_page();
}

echo $OUTPUT->footer($course);
