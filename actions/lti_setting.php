<?php
/**
 * lti_setting.php
 *
 * @package     mod_lticontainer
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
//require_once(__DIR__.'/../local_lib.php');

require_once(__DIR__.'/../include/tabs.php');    // for echo_tabs()


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
$lticontainer_lti_edit_cap = false;
if (has_capability('mod/lticontainer:lti_edit', $mcontext)) {
    $lticontainer_lti_edit_cap = true;
}

///////////////////////////////////////////////////////////////////////////
$urlparams = array();
$urlparams['id'] = $cmid;

$current_tab = 'lti_setting_tab';
$this_action = 'lti_setting';

///////////////////////////////////////////////////////////////////////////
// Event
//$event = lticontainer_get_event($cmid, $this_action, $urlparams);
//$event->add_record_snapshot('course', $course);
//$event->add_record_snapshot('lticontainer', $minstance);
//$event->trigger();

///////////////////////////////////////////////////////////////////////////
// URL
$base_url = new moodle_url('/mod/lticontainer/actions/'.$this_action.'.php');
$base_url->params($urlparams);
$this_url = new moodle_url($base_url);


///////////////////////////////////////////////////////////////////////////
// Print the page header
$PAGE->navbar->add(get_string('lticontainer:lti_setting', 'mod_lticontainer'));
$PAGE->set_url($this_url, $urlparams);
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);

echo $OUTPUT->header();
echo_tabs($current_tab, $courseid, $cmid, $mcontext, $minstance);

if ($lticontainer_lti_edit_cap) {
    require_once(__DIR__.'/../classes/lti_setting.class.php');
    $lti_setting = new LTIConnect($cmid, $courseid, $minstance);
    $lti_setting->set_condition();
    $lti_setting->execute();
    $lti_setting->print_page();
}

echo $OUTPUT->footer($course);

