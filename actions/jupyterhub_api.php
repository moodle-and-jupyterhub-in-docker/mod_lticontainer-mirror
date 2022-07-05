<?php
/**
 * jupyterhub_api.php
 *
 * @package     mod_lticontainer
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../local_lib.php');

require_once(__DIR__.'/../include/tabs.php');    // for echo_tabs()
require_once(__DIR__.'/../classes/event/jupyterhub_api.php');


$cmid = required_param('id', PARAM_INT);                                                        // コースモジュール ID
$cm   = get_coursemodule_from_id('lticontainer', $cmid, 0, false, MUST_EXIST);                  // コースモジュール
$course    = $DB->get_record('course', array('id'=>$cm->course),   '*', MUST_EXIST);            // コースデータ from DB
$minstance = $DB->get_record('lticontainer',  array('id'=>$cm->instance), '*', MUST_EXIST);     // モジュールインスタンス

$mcontext = context_module::instance($cm->id);                                                  // モジュールコンテキスト
$ccontext = context_course::instance($course->id);                                              // コースコンテキスト

$courseid = $course->id;
$user_id  = $USER->id;

///////////////////////////////////////////////////////////////////////////
// Check
require_login($course, true, $cm);

$lticontainer_jupyterhub_api_cap = false;
if (has_capability('mod/lticontainer:jupyterhub_api', $mcontext)) {
    $lticontainer_jupyterhub_api_cap = true;
}

///////////////////////////////////////////////////////////////////////////
$urlparams = array();
$urlparams['id']       = $cmid;
$urlparams['courseid'] = $courseid;

$current_tab = 'jupyterhub_api_tab';
$this_action = 'jupyterhub_api';

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
// Print the page header
$PAGE->navbar->add(get_string('lticontainer:jupyterhub_api', 'mod_lticontainer'));
$PAGE->set_url($this_url, $urlparams);
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);

echo $OUTPUT->header();
echo_tabs($current_tab, $courseid, $cmid, $mcontext, $minstance);

if ($lticontainer_jupyterhub_api_cap) {
    require_once(__DIR__.'/../classes/jupyterhub_api.class.php');
    $jupyterhub_api = new AdminTools($cmid, $courseid, $minstance);
    $jupyterhub_api->set_condition();
    $jupyterhub_api->execute();
    $jupyterhub_api->print_page();
}

///////////////////////////////////////////////////////////////////////////
/// Finish the page
echo $OUTPUT->footer($course);

