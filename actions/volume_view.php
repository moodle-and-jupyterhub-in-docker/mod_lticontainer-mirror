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
$mdlds_volume_view_cap = false;
if (has_capability('mod/mdlds:view_vol', $mcontext)) {
    $mdlds_volume_view = true;
}


///////////////////////////////////////////////////////////////////////////
$urlparams = array();
$urlparams['id'] = $cmid;

$current_tab = 'volume_view';
$this_action = 'volume_view';

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
$PAGE->navbar->add(get_string('mdlds:volume_view', 'mdlds'));
$PAGE->set_url($this_url, $urlparams);
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();

require(__DIR__.'/../include/tabs.php');
require_once(__DIR__.'/../classes/volume_view.class.php');

$volume_view = new VolumeView($cmid, $courseid);

$volume_view->set_condition();
$volume_view->execute();
$volume_view->print_page();

echo $OUTPUT->footer($course);
