<?php

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');


$cmid = required_param('id', PARAM_INT);                                                    // コースモジュール ID
$cm   = get_coursemodule_from_id('mdlds', $cmid, 0, false, MUST_EXIST);                     // コースモジュール

$course    = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);          // コースデータ from DB
$minstance = $DB->get_record('mdlds', array('id' => $cm->instance), '*', MUST_EXIST);       // モジュールインスタンス

$mcontext = context_module::instance($cm->id);                                              // モジュールコンテキスト
$ccontext = context_course::instance($course->id);                                          // コースコンテキスト

$courseid = $course->id;
$user_id  = $USER->id;


///////////////////////////////////////////////////////////////////////////
// Check
require_login($courseid);
//
$mdlds_lti_connection_cap = false;
if (has_capability('mod/mdlds:lti_connect', $mcontext)) {
    $mdlds_lti_connection_cap = true;
}


///////////////////////////////////////////////////////////////////////////
$urlparams = array();
$urlparams['id'] = $cmid;

$current_tab = 'lti_connect';
$this_action = 'lti_connect';

///////////////////////////////////////////////////////////////////////////
// URL
$base_url = new moodle_url('/mod/mdlds/actions/'.$this_action.'.php');
$base_url->params($urlparams);
$this_url = new moodle_url($base_url);

// Event
//$event =  mdlds_get_event($cmid, $minstance->id, $this_action, $urlparams);
//jbxl_add_to_log($event);


///////////////////////////////////////////////////////////////////////////
// Print the page header
$PAGE->navbar->add(get_string('mdlds:lti_connect', 'mdlds'));
$PAGE->set_url($this_url, $urlparams);
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();

require(__DIR__.'/../include/tabs.php');
require_once(__DIR__.'/../classes/lti_connect.class.php');

$lti_connection = new LTIConnect($cmid, $courseid);

$lti_connection->set_condition();
$lti_connection->execute();
$lti_connection->print_page();

echo $OUTPUT->footer($course);
