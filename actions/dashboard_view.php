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
 * Use Moodle's Charts API to visualize learning data.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../locallib.php');

require_once(__DIR__.'/../include/tabs.php');    // for echo_tabs()
require_once(__DIR__.'/../classes/data_provider.class.php'); // for DataProvider
require_once(__DIR__.'/../classes/event/lti_edit.php');

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
//$ltiid    = required_param('ltiid', PARAM_INT);

//
require_login($course, true, $cm);

$ltids_dashboard_view_cap = false;
if (has_capability('mod/ltids:dashboard_view', $mcontext)) {
    $ltids_dashdoard_view_cap = true;
}

//
//$urlparams = array('id' => $cmid, 'ltiid' => $ltiid);
$urlparams = array('id' => $cmid);
$current_tab = 'dashboard_view_tab';
$this_action = 'dashboard_view';

// Event
$event = ltids_get_event($cmid, $this_action, $urlparams);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('ltids', $minstance);
$event->trigger();

// 
$PAGE->set_url('/mod/ltids/dashboard.php', array('id' => $cm->id));
$PAGE->set_title(format_string($minstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($mcontext);

echo $OUTPUT->header();
echo_tabs($current_tab, $courseid, $cmid, $mcontext);

if (!$ltids_dashdoard_view_cap) {
    echo $OUTPUT->footer();
    exit();
}


/////////////////////////////////////////////////////////////////////////////////////

// 取得するレコードの範囲 (デフォルト)
$start_date = '2021-10-06 00:00';
$end_date = (new DateTime())->format('Y-m-d H:i');

// ユーザ名を受け取る
$selected_username = optional_param('user_select_box', '*', PARAM_CLEAN);

// LTI IDを受け取る
$selected_lti_inst_id = optional_param('lti_inst_select_box', '*', PARAM_CLEAN);

// 開始日(start_date)と終了日(end_date)があるならそれを受け取る
$_start_date = optional_param('start_date_input', '*', PARAM_CLEAN);
if($_start_date !== '*')
    $start_date = (new DateTime($_start_date))->format('Y-m-d H:i');
$_end_date = optional_param('end_date_input', '*', PARAM_CLEAN);
if($_end_date !== '*')
    $end_date = (new DateTime($_end_date))->format('Y-m-d H:i');

// DBからデータ取得
$dp = DataProvider::instance_generation($start_date, $end_date, '*', $selected_lti_inst_id);
// 本環境では多分以下のようにする
//$dp = DataProvider::instance_generation($start_date, $end_date, $course->id, $selected_lti_inst_id);
$records = $dp->get_records();
$userdata = $dp->get_userdata();
$usernames = array_keys($userdata);
$lti_inst_info = $DB->get_records('lti', ['course'=>$course->id], '', 'id,name');

//
// ユーザ, LTI，日付の範囲を入力するフォームを描画
//

// ユーザ選択用セレクトボックス生成
$user_select_box = '<select name="user_select_box">';
$user_select_box .= '<option value="*">*</option>'; // All user symbol charcter '*'
foreach($usernames as $username) {
    if($username === $selected_username)
        $user_select_box .= '<option value="'.$username.'" selected>'.$username.'</option>';
    else
        $user_select_box .= '<option value="'.$username.'">'.$username.'</option>';
}
$user_select_box .= '</select>';

// LTI選択用セレクトボックス生成
$lti_inst_select_box = '<select name="lti_inst_select_box">';
$lti_inst_select_box .= '<option value="*">*</option>'; // All LTI symbol charcter '*'
foreach($lti_inst_info as $lti_inst) {
    if($lti_inst->id === $selected_lti_inst_id)
        $lti_inst_select_box .= '<option value="'.$lti_inst->id.'" selected>'.$lti_inst->name.'</option>';
    else
        $lti_inst_select_box .= '<option value="'.$lti_inst->id.'">'.$lti_inst->name.'</option>';
}
$lti_inst_select_box .= '</select>';


// フォーム描画
echo '<table align="center" style="margin-bottom:0.0em;"><tr><td>';
echo '<style type="text/css">';
include(__DIR__.'/../html/styles.css');
echo '</style>';

echo '<form action="'.basename(__FILE__).'?id='.$cm->id.'" method="POST">';
echo '<label for="user_select_box">User Name:&nbsp;</label>';
echo $user_select_box;
echo '&emsp;';
echo '<label for="lti_inst_select_box">LTI:&nbsp;</label>';
echo $lti_inst_select_box;
echo '&emsp;';
echo '<input type="datetime-local" name="start_date_input" value="'.(new DateTime($start_date))->format('Y-m-d\TH:i').'">';
echo '&ensp;～&ensp;';
echo '<input type="datetime-local" name="end_date_input" value="'.(new DateTime($end_date))->format('Y-m-d\TH:i').'">';
echo '&emsp;';
echo '<input type="hidden" id="id" name="id" value="'.$cm->id.'">';
echo '<input type="submit" value="Display">';
echo '<div align="right">';
echo 'Last update:&nbsp;'.(new DateTime())->format('Y-m-d H:i:s');
echo '</div">';
echo '</form>';

echo '</td></tr></table>';
// // //



//
// チャートのインスタンスを生成するための情報を収集する
//

// All activities ('*')
if(empty($selected_username) || $selected_username === '*'):

    $ipynbdata = [];

    $ttl_userdata = [];

    $userdata = [];
    $ok_cnt = 0;
    $error_cnt = 0;

    $unk_userdata = [];
    $unk_ok_cnt = 0;
    $unk_error_cnt = 0;
    //
    foreach($records as $record) {
        $filename = 'unknown';
        $codenum  = -1;
        $username = $record->username;
        $status   = $record->status;
        $tags     = $record->tags;
        if (!empty($tags)) {
            $filename = $tags->filename;
            $codenum  = $tags->codenum;
        }

        // Total activities per user
        if(!array_key_exists($username, $ttl_userdata)) {
            if($status === 'ok') {
                $ttl_userdata[$username] = ['ok' => 1, 'error' => 0];
            } else {
                $ttl_userdata[$username] = ['ok' => 0, 'error' => 1];
            }
        } else if($status === 'ok') {
            $ttl_userdata[$username]['ok'] = $ttl_userdata[$username]['ok'] + 1;
        } else {
            $ttl_userdata[$username]['error'] = $ttl_userdata[$username]['error'] + 1;
        }

        // Unknown activities
        if(empty($record->tags)) {
            if($status === 'ok')
                $unk_ok_cnt++;
            else
                $unk_error_cnt++;

            if(!array_key_exists($username, $unk_userdata)) {
                if($status === 'ok') {
                    $unk_userdata[$username] = ['ok' => 1, 'error' => 0];
                } else {
                    $unk_userdata[$username] = ['ok' => 0, 'error' => 1];
                }
            } else if($status === 'ok') {
                $unk_userdata[$username]['ok'] = $unk_userdata[$username]['ok'] + 1;
            } else {
                $unk_userdata[$username]['error'] = $unk_userdata[$username]['error'] + 1;
            }

            continue;
        }

        // Known activities
        if($status === 'ok')
            $ok_cnt++;
        else
            $error_cnt++;

        if(!array_key_exists($username, $userdata)) {
            if($status === 'ok') {
                $userdata[$username] = ['ok' => 1, 'error' => 0];
            } else {
                $userdata[$username] = ['ok' => 0, 'error' => 1];
            }
        } else if($status === 'ok') {
            $userdata[$username]['ok'] = $userdata[$username]['ok'] + 1;
        } else {
            $userdata[$username]['error'] = $userdata[$username]['error'] + 1;
        }

        // 各ファイルの各コードセル毎の実行結果(ok/error)
        if(!array_key_exists($filename, $ipynbdata)) {
            if($status === 'ok') {
                $ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>1,'error'=>0]];
                $ipynbdata[$filename]['ok'] = 1;
                $ipynbdata[$filename]['error'] = 0;
            } else {
                $ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>0,'error'=>1]];
                $ipynbdata[$filename]['ok'] = 0;
                $ipynbdata[$filename]['error'] = 1;
            }
            continue;
        }

        if(!array_key_exists($codenum, $ipynbdata[$filename]['codenums'])) {
            if($status === 'ok') {
                $ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>1,'error'=>0];
                $ipynbdata[$filename]['ok'] = $ipynbdata[$filename]['ok'] + 1;
            } else {
                $ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>0,'error'=>1];
                $ipynbdata[$filename]['error'] = $ipynbdata[$filename]['error'] + 1;
            }
            continue;
        }

        if($status === 'ok') {
            $ipynbdata[$filename]['codenums'][$codenum]['ok'] = $ipynbdata[$filename]['codenums'][$codenum]['ok'] + 1;
            $ipynbdata[$filename]['ok'] = $ipynbdata[$filename]['ok'] + 1;
        } else {
            $ipynbdata[$filename]['codenums'][$codenum]['error'] = $ipynbdata[$filename]['codenums'][$codenum]['error'] + 1;
            $ipynbdata[$filename]['error'] = $ipynbdata[$filename]['error'] + 1;
        }
    }
    //

    $ttl_ok_s = [];
    $ttl_error_s = [];
    foreach($ttl_userdata as $userdatum) {
        $ttl_ok_s[] = $userdatum['ok'];
        $ttl_error_s[] = $userdatum['error'];
    }
    $ok_s = [];
    $error_s = [];
    foreach($userdata as $userdatum) {
        $ok_s[] = $userdatum['ok'];
        $error_s[] = $userdatum['error'];
    }
    $unk_ok_s = [];
    $unk_error_s = [];
    foreach($unk_userdata as $userdatum) {
        $unk_ok_s[] = $userdatum['ok'];
        $unk_error_s[] = $userdatum['error'];
    }


// User activities
else:

    $ipynbdata = [];
    $ok_cnt = 0;
    $error_cnt = 0;
    $unk_ok_cnt = 0;
    $unk_error_cnt = 0;
    $ttl_first_exec_datetime = new DateTime();
    $ttl_last_exec_datetime =  new DateTime();
    $ttl_xcoords = [];
    $ttl_ycoords = [];
    $ttl_ycoords1 = [];
    $first_exec_datetime = new DateTime();
    $last_exec_datetime = new DateTime();
    $xcoords = [];
    $ycoords = [];
    $ycoords1 = [];
    $unk_first_exec_datetime = new DateTime();
    $unk_last_exec_datetime = new DateTime();
    $unk_xcoords = [];
    $unk_ycoords = [];
    $unk_ycoords1 = [];
    foreach($userdata[$selected_username] as $record) {
        $filename = $record->tags->filename;
        $codenum = $record->tags->codenum;
        $status = $record->status;
        $s_datetime = new DateTime($record->s_date);

        // Total progress view
        if(empty($ttl_xcoords) && empty($ttl_ycoords) && empty($ttl_ycoords1)) {
            $ttl_first_exec_datetime = $s_datetime;
            $ttl_last_exec_datetime = $s_datetime;
            $ttl_xcoords[] = 0;
            $ttl_ycoords[] = 0;
            $ttl_ycoords1[] = $status === 'error' ? 1 : 0;
        } else {
            $ttl_last_exec_datetime = $s_datetime;
            $size = count($ttl_xcoords);
            $ttl_xcoords[] = $size;
            $diff = $ttl_first_exec_datetime->diff($s_datetime);
            $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
            $ttl_ycoords[] = $diff;
            $ttl_ycoords1[] = $status === 'error' ? $ttl_ycoords1[$size - 1] + 1 : $ttl_ycoords1[$size - 1];
        }

        // Unknown activities
        if(empty($record->tags)) {
            if($status === 'ok')
                $unk_ok_cnt++;
            else
                $unk_error_cnt++;

            // Unknown progress view
            if(empty($unk_xcoords) && empty($unk_ycoords) && empty($unk_ycoords1)) {
                $unk_first_exec_datetime = $s_datetime;
                $unk_last_exec_datetime = $s_datetime;
                $unk_xcoords[] = 0;
                $unk_ycoords[] = 0;
                $unk_ycoords1[] = $status === 'error' ? 1 : 0;
            } else {
                $unk_last_exec_datetime = $s_datetime;
                $size = count($unk_xcoords);
                $unk_xcoords[] = $size;
                $diff = $unk_first_exec_datetime->diff($s_datetime);
                $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                $unk_ycoords[] = $diff;
                $unk_ycoords1[] = $status === 'error' ? $unk_ycoords1[$size - 1] + 1 : $unk_ycoords1[$size - 1];
            }

            continue;
        }

        // Known activities
        if($status === 'ok')
            $ok_cnt++;
        else
            $error_cnt++;

        // Known progress view
        if(empty($xcoords) && empty($ycoords) && empty($ycoords1)) {
            $first_exec_datetime = $s_datetime;
            $last_exec_datetime = $s_datetime;
            $xcoords[] = 0;
            $ycoords[] = 0;
            $ycoords1[] = $status === 'error' ? 1 : 0;
        } else {
            $last_exec_datetime = $s_datetime;
            $size = count($xcoords);
            $xcoords[] = $size;
            $diff = $first_exec_datetime->diff($s_datetime);
            $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
            $ycoords[] = $diff;
            $ycoords1[] = $status === 'error' ? $ycoords1[$size - 1] + 1 : $ycoords1[$size - 1];
        }

        // 各ファイルの各コードセル毎の実行結果(ok/error)
        if(!array_key_exists($filename, $ipynbdata)) {
            if($status === 'ok') {
                $ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>1,'error'=>0]];
                $ipynbdata[$filename]['ok'] = 1;
                $ipynbdata[$filename]['error'] = 0;
            } else {
                $ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>0,'error'=>1]];
                $ipynbdata[$filename]['ok'] = 0;
                $ipynbdata[$filename]['error'] = 1;
            }

            $ipynbdata[$filename]['first_exec_datetime'] = $s_datetime;
            $ipynbdata[$filename]['last_exec_datetime'] = $s_datetime;
            $ipynbdata[$filename]['xcoords'] = [$codenum];
            $ipynbdata[$filename]['ycoords'] = [0];
            if($status === 'error')
                $ipynbdata[$filename]['ycoords1'] = [1];
            else
                $ipynbdata[$filename]['ycoords1'] = [0];

            continue;
        }

        $diff = $ipynbdata[$filename]['first_exec_datetime']->diff($s_datetime);
        $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
        $ipynbdata[$filename]['last_exec_datetime'] = $s_datetime;
        $ipynbdata[$filename]['xcoords'][] = $codenum;
        $ipynbdata[$filename]['ycoords'][] = $diff;
        $size = count($ipynbdata[$filename]['ycoords1']);
        $last_val = $ipynbdata[$filename]['ycoords1'][$size - 1];
        if($status === 'error')
            $ipynbdata[$filename]['ycoords1'][] = $last_val + 1;
        else
            $ipynbdata[$filename]['ycoords1'][] = $last_val;


        if(!array_key_exists($codenum, $ipynbdata[$filename]['codenums'])) {
            if($status === 'ok') {
                $ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>1,'error'=>0];
                $ipynbdata[$filename]['ok'] = $ipynbdata[$filename]['ok'] + 1;
            } else {
                $ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>0,'error'=>1];
                $ipynbdata[$filename]['error'] = $ipynbdata[$filename]['error'] + 1;
            }
            continue;
        }

        if($status === 'ok') {
            $ipynbdata[$filename]['codenums'][$codenum]['ok'] = $ipynbdata[$filename]['codenums'][$codenum]['ok'] + 1;
            $ipynbdata[$filename]['ok'] = $ipynbdata[$filename]['ok'] + 1;
        } else {
            $ipynbdata[$filename]['codenums'][$codenum]['error'] = $ipynbdata[$filename]['codenums'][$codenum]['error'] + 1;
            $ipynbdata[$filename]['error'] = $ipynbdata[$filename]['error'] + 1;
        }
    }



endif; // // //



//
// チャートのインスタンスを生成
//

$charts = [];

// All activities ('*')
if(empty($selected_username) || $selected_username === '*'):

    // Total(Known + Unknown) activities
    $pie_series = new core\chart_series('total', [$ok_cnt+$unk_ok_cnt, $error_cnt+$unk_error_cnt]);
    $labels = ['ok', 'error'];
    $chart = new \core\chart_pie();
    $chart->add_series($pie_series);
    $chart->set_labels($labels);
    $chart->set_title('Total activities');
    $charts[] = $chart;

    // Known activities
    $pie_series = new core\chart_series('known', [$ok_cnt, $error_cnt]);
    $labels = ['ok', 'error'];
    $chart = new \core\chart_pie();
    $chart->add_series($pie_series);
    $chart->set_labels($labels);
    $chart->set_title('Known activities');
    $charts[] = $chart;

    // Unknown activities
    $pie_series = new core\chart_series('unknown', [$unk_ok_cnt, $unk_error_cnt]);
    $labels = ['ok', 'error'];
    $chart = new \core\chart_pie();
    $chart->add_series($pie_series);
    $chart->set_labels($labels);
    $chart->set_title('Unknown activities');
    $charts[] = $chart;

    // Total(Known + Unknown) activities per user
    $labels = array_keys($ttl_userdata);
    $ok_series = new core\chart_series('ok', $ttl_ok_s);
    $error_series = new core\chart_series('error', $ttl_error_s);
    $chart = new core\chart_bar();
    $chart->set_horizontal(true);
    $chart->set_stacked(true);
    $chart->add_series($ok_series);
    $chart->add_series($error_series);
    $chart->set_labels($labels);
    $chart->set_title('Total activities per user');
    $charts[] = $chart;

    // Known activities per user
    $labels = array_keys($userdata);
    $ok_series = new core\chart_series('ok', $ok_s);
    $error_series = new core\chart_series('error', $error_s);
    $chart = new core\chart_bar();
    $chart->set_horizontal(true);
    $chart->set_stacked(true);
    $chart->add_series($ok_series);
    $chart->add_series($error_series);
    $chart->set_labels($labels);
    $chart->set_title('Known activities per user');
    $charts[] = $chart;

    // Unknown activities per user
    $labels = array_keys($unk_userdata);
    $ok_series = new core\chart_series('ok', $unk_ok_s);
    $error_series = new core\chart_series('error', $unk_error_s);
    $chart = new core\chart_bar();
    $chart->set_horizontal(true);
    $chart->set_stacked(true);
    $chart->add_series($ok_series);
    $chart->add_series($error_series);
    $chart->set_labels($labels);
    $chart->set_title('Unknown activities per user');
    $charts[] = $chart;

    // 各ファイルのコードセルの実行回数とその内訳
    $filenames = array_keys($ipynbdata);
    sort($filenames);
    $file_ok_s = [];
    $file_error_s = [];
    $charts_per_file = [];
    foreach($filenames as $filename) {
        $ipynb = $ipynbdata[$filename];

        $file_ok_s[] = $ipynb['ok'];
        $file_error_s[] = $ipynb['error'];

        ksort($ipynb['codenums']);
        $labels = array_keys($ipynb['codenums']);
        $ok_s = [];
        $error_s = [];
        foreach($labels as $label) {
            $ok_s[] = $ipynb['codenums'][$label]['ok'];
            $error_s[] = $ipynb['codenums'][$label]['error'];
        }

        $ok_series = new core\chart_series('ok', $ok_s);
        $error_series = new core\chart_series('error', $error_s);

        $chart = new core\chart_bar();
        $chart->set_stacked(true);
        $chart->set_horizontal(true);
        $chart->add_series($ok_series);
        $chart->add_series($error_series);
        $chart->set_labels($labels);
        $chart->set_title($filename);
        $charts_per_file[] = $chart;
    }
    $labels = $filenames;
    $ok_series = new core\chart_series('ok', $file_ok_s);
    $error_series = new core\chart_series('error', $file_error_s);
    $chart = new core\chart_bar();
    $chart->set_horizontal(true);
    $chart->set_stacked(true);
    $chart->add_series($ok_series);
    $chart->add_series($error_series);
    $chart->set_labels($labels);
    $chart->set_title('Known activities per file');
    $charts[] = $chart;
    $charts = array_merge($charts, $charts_per_file);


// User activities
else:

    // Total(Known + Unknown) user activities
    $pie_series = new core\chart_series('total', [$ok_cnt+$unk_ok_cnt, $error_cnt+$unk_error_cnt]);
    $labels = ['ok', 'error'];
    $chart = new \core\chart_pie();
    $chart->add_series($pie_series);
    $chart->set_labels($labels);
    $chart->set_title('Total activities');
    $charts[] = $chart;

    // Total progress view
    $ttl_ycoords = new \core\chart_series('activities', $ttl_ycoords);
    $ttl_ycoords1 = new \core\chart_series('error', $ttl_ycoords1);
    $chart = new \core\chart_line();
    $chart->set_title('Total activities progress view');
    $chart->set_labels($ttl_xcoords);
    $chart->add_series($ttl_ycoords);
    $chart->add_series($ttl_ycoords1);
    $xaxis = new \core\chart_axis();
    $xaxis->set_stepsize(1);
    $yaxis = new \core\chart_axis();
    $yaxis->set_stepsize(1);
    $yaxis->set_label('Time (minutes) & Number of errors');
    $yaxis1 = new \core\chart_axis();
    $yaxis1->set_labels([$ttl_first_exec_datetime->format('Y-m-d H:i'), $ttl_last_exec_datetime->format('Y-m-d H:i')]);
    $chart->set_xaxis($xaxis);
    $chart->set_yaxis($yaxis);     // 0番目のy軸 (Time minutes)
    $chart->set_yaxis($yaxis1, 1); // 1番目のy軸 (first_exec_datetime ~ last_exec_datetime)
    $charts[] = $chart;

    // Known user activities
    $pie_series = new core\chart_series('known', [$ok_cnt, $error_cnt]);
    $labels = ['ok', 'error'];
    $chart = new \core\chart_pie();
    $chart->add_series($pie_series);
    $chart->set_labels($labels);
    $chart->set_title('Known activities');
    $charts[] = $chart;

    // Known progress view
    $ycoords = new \core\chart_series('activities', $ycoords);
    $ycoords1 = new \core\chart_series('error', $ycoords1);
    $chart = new \core\chart_line();
    $chart->set_title('Known activities progress view');
    $chart->set_labels($xcoords);
    $chart->add_series($ycoords);
    $chart->add_series($ycoords1);
    $xaxis = new \core\chart_axis();
    $xaxis->set_stepsize(1);
    $yaxis = new \core\chart_axis();
    $yaxis->set_stepsize(1);
    $yaxis->set_label('Time (minutes) & Number of errors');
    $yaxis1 = new \core\chart_axis();
    $yaxis1->set_labels([$first_exec_datetime->format('Y-m-d H:i'), $last_exec_datetime->format('Y-m-d H:i')]);
    $chart->set_xaxis($xaxis);
    $chart->set_yaxis($yaxis);     // 0番目のy軸 (Time minutes)
    $chart->set_yaxis($yaxis1, 1); // 1番目のy軸 (first_exec_datetime ~ last_exec_datetime)
    $charts[] = $chart;

    // Unknown user activities
    $pie_series = new core\chart_series('unknown', [$unk_ok_cnt, $unk_error_cnt]);
    $labels = ['ok', 'error'];
    $chart = new \core\chart_pie();
    $chart->add_series($pie_series);
    $chart->set_labels($labels);
    $chart->set_title('Unknown activities');
    $charts[] = $chart;

    // Unknown progress view
    $unk_ycoords = new \core\chart_series('activities', $unk_ycoords);
    $unk_ycoords1 = new \core\chart_series('error', $unk_ycoords1);
    $chart = new \core\chart_line();
    $chart->set_title('Unknown activities progress view');
    $chart->set_labels($unk_xcoords);
    $chart->add_series($unk_ycoords);
    $chart->add_series($unk_ycoords1);
    $xaxis = new \core\chart_axis();
    $xaxis->set_stepsize(1);
    $yaxis = new \core\chart_axis();
    $yaxis->set_stepsize(1);
    $yaxis->set_label('Time (minutes) & Number of errors');
    $yaxis1 = new \core\chart_axis();
    $yaxis1->set_labels([$unk_first_exec_datetime->format('Y-m-d H:i'), $unk_last_exec_datetime->format('Y-m-d H:i')]);
    $chart->set_xaxis($xaxis);
    $chart->set_yaxis($yaxis);     // 0番目のy軸 (Time minutes)
    $chart->set_yaxis($yaxis1, 1); // 1番目のy軸 (first_exec_datetime ~ last_exec_datetime)
    $charts[] = $chart;

    // 各ファイルのコードセルの実行回数とその内訳
    $filenames = array_keys($ipynbdata);
    sort($filenames);
    $file_ok_s = [];
    $file_error_s = [];
    $charts_per_file = [];
    foreach($filenames as $filename) {
        $ipynb = $ipynbdata[$filename];

        $file_ok_s[] = $ipynb['ok'];
        $file_error_s[] = $ipynb['error'];

        ksort($ipynb['codenums']);
        $labels = array_keys($ipynb['codenums']);
        $ok_s = [];
        $error_s = [];
        foreach($labels as $label) {
            $ok_s[] = $ipynb['codenums'][$label]['ok'];
            $error_s[] = $ipynb['codenums'][$label]['error'];
        }

        $ok_series = new core\chart_series('ok', $ok_s);
        $error_series = new core\chart_series('error', $error_s);

        $chart = new core\chart_bar();
        $chart->set_stacked(true);
        $chart->set_horizontal(true);
        $chart->add_series($ok_series);
        $chart->add_series($error_series);
        $chart->set_labels($labels);
        $chart->set_title($filename);
        $charts_per_file[] = $chart;


        $ipynbdata[$filename]['ycoords'] = new \core\chart_series('activities', $ipynbdata[$filename]['ycoords']);
        $ipynbdata[$filename]['ycoords1'] = new \core\chart_series('error', $ipynbdata[$filename]['ycoords1']);

        $chart = new \core\chart_line();
        $chart->set_title($filename);
        $chart->set_labels($ipynbdata[$filename]['xcoords']);
        $chart->add_series($ipynbdata[$filename]['ycoords']);
        $chart->add_series($ipynbdata[$filename]['ycoords1']);

        $xaxis = new \core\chart_axis();
        $xaxis->set_label('codenum');
        $yaxis = new \core\chart_axis();
        $yaxis->set_label('Time (minutes) & Number of errors');
        $yaxis->set_stepsize(1);
        $yaxis1 = new \core\chart_axis();
        $yaxis1->set_labels([$ipynbdata[$filename]['first_exec_datetime']->format('Y-m-d H:i'), $ipynbdata[$filename]['last_exec_datetime']->format('Y-m-d H:i')]);
        $chart->set_xaxis($xaxis);
        $chart->set_yaxis($yaxis);
        $chart->set_yaxis($yaxis1, 1);

        $charts_per_file[] = $chart;
    }
    $labels = $filenames;
    $ok_series = new core\chart_series('ok', $file_ok_s);
    $error_series = new core\chart_series('error', $file_error_s);
    $chart = new core\chart_bar();
    $chart->set_horizontal(true);
    $chart->set_stacked(true);
    $chart->add_series($ok_series);
    $chart->add_series($error_series);
    $chart->set_labels($labels);
    $chart->set_title('Known activities per file');
    $charts[] = $chart;
    $charts = array_merge($charts, $charts_per_file);


endif; // // //



//
// チャートを描画
//

echo '<center>';
echo '<font size="5">';
echo $start_date.'～'.$end_date.'<br>';
echo $selected_username;
echo '&emsp;';
if($selected_lti_inst_id !== '*')
    echo $lti_inst_info[$selected_lti_inst_id]->name;
else
    echo $selected_lti_inst_id;
echo '</font>';
echo '</center>';
foreach($charts as $chart)
    echo $OUTPUT->render($chart);

// // //


echo $OUTPUT->footer();
