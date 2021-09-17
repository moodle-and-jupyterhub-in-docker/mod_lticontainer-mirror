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
 * Display information about all the mod_ltids modules in the requested course.
 *
 * @package     mod_ltids
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

$courseid = required_param('id', PARAM_INT);
$course   = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
require_course_login($course);

$ccontext = context_course::instance($course->id);

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/ltids/index.php', array('id' => $cmid));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($ccontext);

echo $OUTPUT->header();

echo '<style type="text/css">';
include(__DIR__.'/html/styles.css');
echo '</style>';


$modulenameplural = get_string('modulenameplural', 'mod_ltids');
echo $OUTPUT->heading($modulenameplural);

$ltidss = get_all_instances_in_course('ltids', $course);

if (empty($ltidss)) {
    notice(get_string('no$ltidsinstances', 'mod_ltids'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($course->format == 'weeks') {
    $table->head  = array(get_string('week'), get_string('name'));
    $table->align = array('center', 'left');
} 
else if ($course->format == 'topics') {
    $table->head  = array(get_string('topic'), get_string('name'));
    $table->align = array('center', 'left', 'left', 'left');
} 
else {
    $table->head  = array(get_string('name'));
    $table->align = array('left', 'left', 'left');
}

//
foreach ($ltidss as $ltids) {
    if (!$ltids->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/ltids/view.php', array('id' => $ltids->coursemodule)),
            format_string($ltids->name, true),
            array('class' => 'dimmed'));
    }
    else {
        $link = html_writer::link(
            new moodle_url('/mod/ltids/view.php', array('id' => $ltids->coursemodule)),
            format_string($ltids->name, true));
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array($ltids->section, $link);
    }
    else {
        $table->data[] = array($link);
    }
}

echo html_writer::table($table);
echo $OUTPUT->footer();

