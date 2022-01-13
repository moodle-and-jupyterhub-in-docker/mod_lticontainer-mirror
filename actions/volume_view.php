<?php
/**
 * volume_view.php
 *
 * @package     mod_lticontainer
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../local_lib.php');

require_once(__DIR__.'/../include/tabs.php');    // for echo_tabs()
require_once(__DIR__.'/../classes/event/volume_view.php');
require_once(__DIR__.'/../classes/event/volume_delete.php');


$cmid = required_param('id', PARAM_INT);                                                    // コースモジュール ID
$cm   = get_coursemodule_from_id('lticontainer', $cmid, 0, false, MUST_EXIST);                     // コースモジュール

$course    = $DB->get_record('course', array('id'=>$cm->course),   '*', MUST_EXIST);        // コースデータ from DB
$minstance = $DB->get_record('lticontainer',  array('id'=>$cm->instance), '*', MUST_EXIST);        // モジュールインスタンス

$mcontext = context_module::instance($cm->id);                                              // モジュールコンテキスト
$ccontext = context_course::instance($course->id);                                          // コースコンテキスト

$courseid = $course->id;
$user_id  = $USER->id;


///////////////////////////////////////////////////////////////////////////
// Check
require_login($course, true, $cm);
//
$lticontainer_volume_view_cap = false;
if (has_capability('mod/lticontainer:volume_view', $mcontext)) {
    $lticontainer_volume_view_cap = true;
}

///////////////////////////////////////////////////////////////////////////
$urlparams = array();
$urlparams['id'] = $cmid;

$current_tab = 'volume_view_tab';
$this_action = 'volume_view';

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
$PAGE->navbar->add(get_string('lticontainer:volume_view', 'mod_lticontainer'));
$PAGE->set_url($this_url, $urlparams);
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);

echo $OUTPUT->header();
echo_tabs($current_tab, $courseid, $cmid, $mcontext, $minstance);

if ($lticontainer_volume_view_cap) {
    require_once(__DIR__.'/../classes/volume_view.class.php');
    $volume_view = new VolumeView($cmid, $courseid, $minstance);
    $volume_view->set_condition();
    $volume_view->execute();
    $volume_view->print_page();
}

echo $OUTPUT->footer($course);

