<?php
/**
 * show_demo.php
 *
 * @package     mod_lticontainer
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');

require_once(__DIR__.'/../include/tabs.php');    // for echo_tabs()
require_once(__DIR__.'/../classes/event/show_demo.php');


//lticontainer_init_session();
//$SESSION->lticontainer->is_started = false;

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

///////////////////////////////////////////////////////////////////////////
$urlparams = array();
$urlparams['id']       = $cmid;
$urlparams['courseid'] = $courseid;

$current_tab = 'show_demo_tab';
$this_action = 'show_demo';

// Event
$event = lticontainer_get_event($cmid, $this_action, $urlparams);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('lticontainer', $minstance);
$event->trigger();

///////////////////////////////////////////////////////////////////////////
// URL
$base_url = new moodle_url('/mod/lticontainer/actions/'.$this_action.'.php');
$base_url->params($urlparams);
$this_url = new moodle_url($base_url);

///////////////////////////////////////////////////////////////////////////
// Event
//$event = apply_get_event($cm, 'over_view', $urlparams);
//jbxl_add_to_log($event);

///////////////////////////////////////////////////////////////////////////
// Print the page header
$PAGE->navbar->add(get_string('lticontainer:show_demo', 'mod_lticontainer'));
$PAGE->set_url($this_url, $urlparams);
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);

echo $OUTPUT->header();
echo_tabs($current_tab, $courseid, $cmid, $mcontext, $minstance);

require_once(__DIR__.'/../classes/show_demo.class.php');

$show_demo = new ShowDemo($cmid, $courseid);

$show_demo->set_condition();
$show_demo->execute();
$show_demo->print_page();

///////////////////////////////////////////////////////////////////////////
/// Finish the page
echo $OUTPUT->footer($course);

