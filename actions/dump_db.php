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
 * This tab is for dumping and displaying database records.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../locallib.php');

require_once(__DIR__.'/../include/tabs.php'); // for echo_tabs()
require_once(__DIR__.'/../classes/data_provider.class.php'); // for DataProvider
//require_once(__DIR__.'/../classes/event/dump_db.php');

// Course module id.
$cmid = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$l = optional_param('l', 0, PARAM_INT);

if ($cmid) {
    $cm = get_coursemodule_from_id('ltids', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $minstance = $DB->get_record('ltids', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $minstance = $DB->get_record('ltids', array('id' => $l), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $minstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('ltids', $minstance->id, $course->id, false, MUST_EXIST);
}

$mcontext = context_module::instance($cm->id);
$courseid = $course->id;
$cmid     = $cm->id;
$user_id  = $USER->id;

$mcontext = context_module::instance($cm->id);

require_login($course, true, $cm);

$urlparams = array('id' => $cmid);
$current_tab = 'dump_db_tab';
$this_action = 'dump_db';


// URL
$PAGE->set_url('/mod/ltids/dump_db.php', array('id' => $cm->id));
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);

echo $OUTPUT->header();

echo_tabs($current_tab, $courseid, $cmid, $mcontext);

// 取得するレコードの範囲
$start_date = '2021-10-06 00:00:00';
$end_date   = '2021-10-07 23:59:59';

// データベースアクセス
$dp = DataProvider::instance_generation($start_date, $end_date);
$records  = $dp->get_records();
$userdata = $dp->get_userdata();
$usernames = array_keys($userdata);

// ダンプ
echo '<pre>';
foreach($usernames as $username) {
    echo $username.', データ数 = '.count($userdata[$username]).PHP_EOL;
}
echo '<br><br>';
print_r($records);
//print_r($userdata);
echo '</pre>';


echo $OUTPUT->footer();
