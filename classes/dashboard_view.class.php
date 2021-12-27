<?php
/**
 * Use Moodle's Charts API to visualize learning data.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../locallib.php');
require_once(__DIR__.'/../localdblib.php');


class  DashboardView
{
    var $cmid;
    var $courseid   = 0;
    var $course;
    var $minstance;
    var $mcontext;

    var $isGuest    = true;

    var $action_url = '';
    var $url_params = array();

    var $items;

    var $start_date = '2021-10-06 00:00';
    var $end_date   = '';
    var $username   = '*';
    var $lti_id     = '*';
    var $lti_inst_info;

    var $ttl_data;
    var $kwn_data;
    var $unk_data;

    var $ttl_coords;
    var $kwn_coords;
    var $unk_coords;

    var $ipynbdata = [];
    var $charts    = [];

    var $dp;


    function  __construct($cmid, $courseid, $minstance)
    {
        global $DB;

        $this->cmid      = $cmid;
        $this->courseid  = $courseid;
        $this->course    = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance = $minstance;

        $this->url_params = array('id'=>$cmid);
        $this->action_url = new moodle_url('/mod/ltids/actions/dashboard_view.php', $this->url_params);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_ltids', $this->action_url);
        }
        //
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/ltids:dashboard_view', $this->mcontext)) {
            print_error('access_forbidden', 'mod_ltids', $this->action_url);
        }

        ////////////////////////////////////////////////////////////////////////////
        // 取得するレコードの範囲 (デフォルト)
        $this->end_date = (new DateTime())->format('Y-m-d H:i');

        // ユーザ名とLTI ID を受け取る
        $this->username = optional_param('user_select_box',     '*', PARAM_CLEAN);
        $this->lti_id   = optional_param('lti_inst_select_box', '*', PARAM_CLEAN);

        // 開始日(start_date)と終了日(end_date)があるならそれを受け取る
        $_start_date = optional_param('start_date_input', '*', PARAM_CLEAN);
        $_end_date   = optional_param('end_date_input',   '*', PARAM_CLEAN);

        if($_start_date !== '*') $this->start_date = (new DateTime($_start_date))->format('Y-m-d H:i');
        if($_end_date   !== '*') $this->end_date   = (new DateTime($_end_date)  )->format('Y-m-d H:i');

        $this->ttl_data = new StdClass();
        $this->ttl_data->ok_cnt    = 0;
        $this->ttl_data->error_cnt = 0;
        $this->ttl_data->ok_s      = [];
        $this->ttl_data->error_s   = [];
        $this->ttl_data->userdata  = [];
        $this->ttl_data->exec_datetime = new StdClass();
        $this->ttl_data->exec_datetime->first = new DateTime();
        $this->ttl_data->exec_datetime->last  = new DateTime();

        $this->kwn_data = new StdClass();
        $this->kwn_data->ok_cnt    = 0;
        $this->kwn_data->error_cnt = 0;
        $this->kwn_data->ok_s      = [];
        $this->kwn_data->error_s   = [];
        $this->kwn_data->userdata  = [];
        $this->kwn_data->exec_datetime = new StdClass();
        $this->kwn_data->exec_datetime->first = new DateTime();
        $this->kwn_data->exec_datetime->last  = new DateTime();

        $this->unk_data = new StdClass();
        $this->unk_data->ok_cnt   = 0;
        $this->unk_data->error_cnt  = 0;
        $this->unk_data->ok_s     = [];
        $this->unk_data->error_s    = [];
        $this->unk_data->userdata = [];
        $this->unk_data->exec_datetime = new StdClass();
        $this->unk_data->exec_datetime->first = new DateTime();
        $this->unk_data->exec_datetime->last  = new DateTime();

        $this->ttl_coords = new StdClass();
        $this->ttl_coords->x  = [];
        $this->ttl_coords->y0 = [];
        $this->ttl_coords->y1 = [];

        $this->kwn_coords = new StdClass();
        $this->kwn_coords->x  = [];
        $this->kwn_coords->y0 = [];
        $this->kwn_coords->y1 = [];

        $this->unk_coords = new StdClass();
        $this->unk_coords->x  = [];
        $this->unk_coords->y0 = [];
        $this->unk_coords->y1 = [];
    }


    function  set_condition()
    {
        //$this->order = optional_param('order', '', PARAM_TEXT);

        return true;
    }


    function  execute()
    {
        global $DB;

        $this->dp = DataProvider::instance_generation($this->course->id, $this->lti_id, $this->start_date, $this->end_date);
        $this->lti_inst_info = db_get_valid_ltis($this->courseid, $this->minstance);
        $this->get_chart_info();
        $this->charts = $this->create_chart_inst();

        return true;
    }


    function  print_page()
    {
        global $OUTPUT;

        include(__DIR__.'/../html/dashboard_view.html');
    }


    //
    // チャートのインスタンスを生成するための情報を収集する
    //
    function  get_chart_info()
    {
        // All activities ('*')
        if($this->username === '*'):
            //
            $records = $this->dp->get_records();
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
                if(!array_key_exists($username, $this->ttl_data->userdata)) {
                    if($status === 'ok') {
                        $this->ttl_data->userdata[$username] = ['ok' => 1, 'error' => 0];
                    } else {
                        $this->ttl_data->userdata[$username] = ['ok' => 0, 'error' => 1];
                    }
                } else if($status === 'ok') {
                    $this->ttl_data->userdata[$username]['ok']    = $this->ttl_data->userdata[$username]['ok']    + 1;
                } else {
                    $this->ttl_data->userdata[$username]['error'] = $this->ttl_data->userdata[$username]['error'] + 1;
                }
        
                // Unknown activities
                if(empty($record->tags)) {
                    if($status === 'ok') $this->unk_data->ok_cnt++;
                    else                 $this->unk_data->error_cnt++;
        
                    if(!array_key_exists($username, $this->unk_data->userdata)) {
                        if($status === 'ok') $this->unk_data->userdata[$username] = ['ok' => 1, 'error' => 0];
                        else                 $this->unk_data->userdata[$username] = ['ok' => 0, 'error' => 1];
                    } 
                    else if($status === 'ok') {
                        $this->unk_data->userdata[$username]['ok']    = $this->unk_data->userdata[$username]['ok']    + 1;
                    } 
                    else {
                        $this->unk_data->userdata[$username]['error'] = $this->unk_data->userdata[$username]['error'] + 1;
                    }
        
                    continue;
                }
        
                // Known activities
                if($status === 'ok')
                    $this->kwn_data->ok_cnt++;
                else
                    $this->kwn_data->error_cnt++;
        
                if(!array_key_exists($username, $this->kwn_data->userdata)) {
                    if($status === 'ok') {
                        $this->kwn_data->userdata[$username] = ['ok' => 1, 'error' => 0];
                    } else {
                        $this->kwn_data->userdata[$username] = ['ok' => 0, 'error' => 1];
                    }
                } else if($status === 'ok') {
                    $this->kwn_data->userdata[$username]['ok']    = $this->kwn_data->userdata[$username]['ok']    + 1;
                } else {
                    $this->kwn_data->userdata[$username]['error'] = $this->kwn_data->userdata[$username]['error'] + 1;
                }
        
                // 各ファイルの各コードセル毎の実行結果(ok/error)
                if(!array_key_exists($filename, $this->ipynbdata)) {
                    if($status === 'ok') {
                        $this->ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>1,'error'=>0]];
                        $this->ipynbdata[$filename]['ok']    = 1;
                        $this->ipynbdata[$filename]['error'] = 0;
                    } else {
                        $this->ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>0,'error'=>1]];
                        $this->ipynbdata[$filename]['ok']    = 0;
                        $this->ipynbdata[$filename]['error'] = 1;
                    }
                    continue;
                }
        
                if(!array_key_exists($codenum, $this->ipynbdata[$filename]['codenums'])) {
                    if($status === 'ok') {
                        $this->ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>1,'error'=>0];
                        $this->ipynbdata[$filename]['ok']    = $this->ipynbdata[$filename]['ok']    + 1;
                    } else {
                        $this->ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>0,'error'=>1];
                        $this->ipynbdata[$filename]['error'] = $this->ipynbdata[$filename]['error'] + 1;
                    }
                    continue;
                }
        
                if($status === 'ok') {
                    $this->ipynbdata[$filename]['codenums'][$codenum]['ok']    = $this->ipynbdata[$filename]['codenums'][$codenum]['ok']    + 1;
                    $this->ipynbdata[$filename]['ok']    = $this->ipynbdata[$filename]['ok']   + 1;
                } else {
                    $this->ipynbdata[$filename]['codenums'][$codenum]['error'] = $this->ipynbdata[$filename]['codenums'][$codenum]['error'] + 1;
                    $this->ipynbdata[$filename]['error'] = $this->ipynbdata[$filename]['error'] + 1;
                }
            }
            //
        
            foreach($this->ttl_data->userdata as $userdatum) {
                $this->ttl_data->ok_s[]    = $userdatum['ok'];
                $this->ttl_data->error_s[] = $userdatum['error'];
            }
            foreach($this->kwn_data->userdata as $userdatum) {
                $this->kwn_data->ok_s[]    = $userdatum['ok'];
                $this->kwn_data->error_s[] = $userdatum['error'];
            }
            foreach($this->unk_data->userdata as $userdatum) {
                $this->unk_data->ok_s[]    = $userdatum['ok'];
                $this->unk_data->error_s[] = $userdatum['error'];
            }
        
        
        // User activities
        else:
        
            $this->kwn_data->userdata = $this->dp->get_userdata();

            foreach($this->kwn_data->userdata[$this->username] as $record) {
                if (empty($record->tags->filename) or empty($record->tags->codenum)) continue;

                $filename   = $record->tags->filename;
                $codenum    = $record->tags->codenum;
                $status     = $record->status;
                $s_datetime = new DateTime($record->s_date);
        
                // Total progress view
                if(empty($this->ttl_coords->x) && empty($this->ttl_coords->y0) && empty($this->ttl_coords->y1)) {
                    $this->ttl_data->exec_datetime->first = $s_datetime;
                    $this->ttl_data->exec_datetime->last  = $s_datetime;
                    $this->ttl_coords->x[]  = 0;
                    $this->ttl_coords->y0[] = 0;
                    $this->ttl_coords->y1[] = $status === 'error' ? 1 : 0;
                } else {
                    $this->ttl_data->exec_datetime->last = $s_datetime;
                    $size = count($this->ttl_coords->x);
                    $this->ttl_coords->x[] = $size;
                    $diff = $this->ttl_data->exec_datetime->first->diff($s_datetime);
                    $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                    $this->ttl_coords->y0[] = $diff;
                    $this->ttl_coords->y1[] = $status === 'error' ? $this->ttl_coords->y1[$size - 1] + 1 : $this->ttl_coords->y1[$size - 1];
                }
        
                // Unknown activities
                if(empty($record->tags)) {
                    if($status === 'ok')
                        $this->unk_data->ok_cnt++;
                    else
                        $this->unk_data->error_cnt++;
        
                    // Unknown progress view
                    if(empty($this->unk_coords->x) && empty($this->unk_coords->y0) && empty($this->unk_coords->y1)) {
                        $this->unk_data->exec_datetime->first = $s_datetime;
                        $this->unk_data->exec_datetime->last  = $s_datetime;
                        $this->unk_coords->x[]  = 0;
                        $this->unk_coords->y0[] = 0;
                        $this->unk_coords->y1[] = $status === 'error' ? 1 : 0;
                    } else {
                        $this->unk_data->exec_datetime->last = $s_datetime;
                        $size = count($this->unk_coords->x);
                        $this->unk_coords->x[]  = $size;
                        $diff = $this->unk_data->exec_datetime->first->diff($s_datetime);
                        $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                        $this->unk_coords->y0[] = $diff;
                        $this->unk_coords->y1[] = $status === 'error' ? $this->unk_coords->y1[$size - 1] + 1 : $this->unk_coords->y1[$size - 1];
                    }
        
                    continue;
                }
        
                // Known activities
                if($status === 'ok')
                    $this->kwn_data->ok_cnt++;
                else
                    $this->kwn_data->error_cnt++;
        
                // Known progress view
                if(empty($this->kwn_coords->x) && empty($this->kwn_coords->y0) && empty($this->kwn_coords->y1)) {
                    $this->kwn_data->exec_datetime->first = $s_datetime;
                    $this->kwn_data->exec_datetime->last = $s_datetime;
                    $this->kwn_coords->x[]  = 0;
                    $this->kwn_coords->y0[] = 0;
                    $this->kwn_coords->y1[] = $status === 'error' ? 1 : 0;
                } else {
                    $this->kwn_data->exec_datetime->last = $s_datetime;
                    $size = count($this->kwn_coords->x);
                    $this->kwn_coords->x[]  = $size;
                    $diff = $this->kwn_data->exec_datetime->first->diff($s_datetime);
                    $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                    $this->kwn_coords->y0[] = $diff;
                    $this->kwn_coords->y1[] = $status === 'error' ? $this->kwn_coords->y1[$size - 1] + 1 : $this->kwn_coords->y1[$size - 1];
                }
        
                // 各ファイルの各コードセル毎の実行結果(ok/error)
                if(!array_key_exists($filename, $this->ipynbdata)) {
                    if($status === 'ok') {
                        $this->ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>1,'error'=>0]];
                        $this->ipynbdata[$filename]['ok']  = 1;
                        $this->ipynbdata[$filename]['error'] = 0;
                    } else {
                        $this->ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>0,'error'=>1]];
                        $this->ipynbdata[$filename]['ok']  = 0;
                        $this->ipynbdata[$filename]['error'] = 1;
                    }
        
                    $this->ipynbdata[$filename]['exec_datetime_first'] = $s_datetime;
                    $this->ipynbdata[$filename]['exec_datetime_last']  = $s_datetime;
                    $this->ipynbdata[$filename]['coords_x'] = [$codenum];
                    $this->ipynbdata[$filename]['coords_y0'] = [0];
                    if($status === 'error')
                        $this->ipynbdata[$filename]['coords_y1'] = [1];
                    else
                        $this->ipynbdata[$filename]['coords_y1'] = [0];
        
                    continue;
                }
        
                $diff = $this->ipynbdata[$filename]['exec_datetime_first']->diff($s_datetime);
                $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                $this->ipynbdata[$filename]['exec_datetime_last'] = $s_datetime;
                $this->ipynbdata[$filename]['coords_x'][] = $codenum;
                $this->ipynbdata[$filename]['coords_y0'][] = $diff;
                $size = count($this->ipynbdata[$filename]['coords_y1']);
                $last_val = $this->ipynbdata[$filename]['coords_y1'][$size - 1];
                if($status === 'error')
                    $this->ipynbdata[$filename]['coords_y1'][] = $last_val + 1;
                else
                    $this->ipynbdata[$filename]['coords_y1'][] = $last_val;
        
        
                if(!array_key_exists($codenum, $this->ipynbdata[$filename]['codenums'])) {
                    if($status === 'ok') {
                        $this->ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>1,'error'=>0];
                        $this->ipynbdata[$filename]['ok']    = $this->ipynbdata[$filename]['ok']    + 1;
                    } else {
                        $this->ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>0,'error'=>1];
                        $this->ipynbdata[$filename]['error'] = $this->ipynbdata[$filename]['error'] + 1;
                    }
                    continue;
                }
        
                if($status === 'ok') {
                    $this->ipynbdata[$filename]['codenums'][$codenum]['ok']    = $this->ipynbdata[$filename]['codenums'][$codenum]['ok']    + 1;
                    $this->ipynbdata[$filename]['ok']    = $this->ipynbdata[$filename]['ok']    + 1;
                } else {
                    $this->ipynbdata[$filename]['codenums'][$codenum]['error'] = $this->ipynbdata[$filename]['codenums'][$codenum]['error'] + 1;
                    $this->ipynbdata[$filename]['error'] = $this->ipynbdata[$filename]['error'] + 1;
                }
            }
        
        endif; 
    }
        

    //
    // チャートのインスタンスを生成
    //
    function  create_chart_inst()
    {
        $charts = [];
        
        // All activities ('*')
        if($this->username === '*'):
        
            // Total(Known + Unknown) activities
            $pie_series = new core\chart_series('total', [$this->kwn_data->ok_cnt    + $this->unk_data->ok_cnt, 
                                                          $this->kwn_data->error_cnt + $this->unk_data->error_cnt]);
            $labels = ['ok', 'error'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Total activities');
            $charts[] = $chart;
        
            // Known activities
            $pie_series = new core\chart_series('known', [$this->kwn_data->ok_cnt, $this->kwn_data->error_cnt]);
            $labels = ['ok', 'error'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Known activities');
            $charts[] = $chart;
        
            // Unknown activities
            $pie_series = new core\chart_series('unknown', [$this->unk_data->ok_cnt, $this->unk_data->error_cnt]);
            $labels = ['ok', 'error'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Unknown activities');
            $charts[] = $chart;
        
            // Total(Known + Unknown) activities per user
            $labels = array_keys($this->ttl_data->userdata);
            $ok_series    = new core\chart_series('ok',    $this->ttl_data->ok_s);
            $error_series = new core\chart_series('error', $this->ttl_data->error_s);
            $chart = new core\chart_bar();
            $chart->set_horizontal(true);
            $chart->set_stacked(true);
            $chart->add_series($ok_series);
            $chart->add_series($error_series);
            $chart->set_labels($labels);
            $chart->set_title('Total activities per user');
            $charts[] = $chart;
        
            // Known activities per user
            $labels = array_keys($this->kwn_data->userdata);
            $ok_series    = new core\chart_series('ok',    $this->kwn_data->ok_s);
            $error_series = new core\chart_series('error', $this->kwn_data->error_s);
            $chart = new core\chart_bar();
            $chart->set_horizontal(true);
            $chart->set_stacked(true);
            $chart->add_series($ok_series);
            $chart->add_series($error_series);
            $chart->set_labels($labels);
            $chart->set_title('Known activities per user');
            $charts[] = $chart;
        
            // Unknown activities per user
            $labels = array_keys($this->unk_data->userdata);
            $ok_series    = new core\chart_series('ok',    $this->unk_data->ok_s);
            $error_series = new core\chart_series('error', $this->unk_data->error_s);

            $chart = new core\chart_bar();
            $chart->set_horizontal(true);
            $chart->set_stacked(true);
            $chart->add_series($ok_series);
            $chart->add_series($error_series);
            $chart->set_labels($labels);
            $chart->set_title('Unknown activities per user');
            $charts[] = $chart;
        
            // 各ファイルのコードセルの実行回数とその内訳
            $filenames = array_keys($this->ipynbdata);
            sort($filenames);
            $file_ok_s  = [];
            $file_error_s = [];
            $charts_per_file = [];
            foreach($filenames as $filename) {
                $ipynb = $this->ipynbdata[$filename];
        
                $file_ok_s[]  = $ipynb['ok'];
                $file_error_s[] = $ipynb['error'];
        
                ksort($ipynb['codenums']);
                $labels  = array_keys($ipynb['codenums']);
                $ok_s    = [];
                $error_s = [];
                foreach($labels as $label) {
                    $ok_s[]    = $ipynb['codenums'][$label]['ok'];
                    $error_s[] = $ipynb['codenums'][$label]['error'];
                }
        
                $ok_series    = new core\chart_series('ok',    $ok_s);
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
            $ok_series    = new core\chart_series('ok',    $file_ok_s);
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
            $pie_series = new core\chart_series('total', [$this->kwn_data->ok_cnt    + $this->unk_data->ok_cnt, 
                                                          $this->kwn_data->error_cnt + $this->unk_data->error_cnt]);
            $labels = ['ok', 'error'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Total activities');
            $charts[] = $chart;
        
            // Total progress view
            $this->ttl_coords->y0 = new \core\chart_series('activities', $this->ttl_coords->y0);
            $this->ttl_coords->y1 = new \core\chart_series('error',      $this->ttl_coords->y1);
            $chart   = new \core\chart_line();
            $chart->set_title('Total activities progress view');
            $chart->set_labels($this->ttl_coords->x);
            $chart->add_series($this->ttl_coords->y0);
            $chart->add_series($this->ttl_coords->y1);
            $axis_x  = new \core\chart_axis();
            $axis_x->set_stepsize(1);
            $axis_y0 = new \core\chart_axis();
            $axis_y0->set_stepsize(1);
            $axis_y0->set_label('Time (minutes) & Number of errors');
            $axis_y1 = new \core\chart_axis();
            $axis_y1->set_labels([$this->ttl_data->exec_datetime->first->format('Y-m-d H:i'), 
                                 $this->ttl_data->exec_datetime->last->format('Y-m-d H:i')]);
            $chart->set_xaxis($axis_x);
            $chart->set_yaxis($axis_y0);    // 0番目のy軸 (Time minutes)
            $chart->set_yaxis($axis_y1, 1); // 1番目のy軸 (exec_datetime_first ~ exec_datetime_last)
            $charts[] = $chart;
        
            // Known user activities
            $pie_series = new core\chart_series('known', [$this->kwn_data->ok_cnt, $this->kwn_data->error_cnt]);
            $labels = ['ok', 'error'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Known activities');
            $charts[] = $chart;
        
            // Known progress view
            $this->kwn_coords->y0 = new \core\chart_series('activities', $this->kwn_coords->y0);
            $this->kwn_coords->y1 = new \core\chart_series('error',      $this->kwn_coords->y1);
            $chart = new \core\chart_line();
            $chart->set_title('Known activities progress view');
            $chart->set_labels($this->kwn_coords->x);
            $chart->add_series($this->kwn_coords->y0);
            $chart->add_series($this->kwn_coords->y1);
            $axis_x = new \core\chart_axis();
            $axis_x->set_stepsize(1);
            $axis_y0 = new \core\chart_axis();
            $axis_y0->set_stepsize(1);
            $axis_y0->set_label('Time (minutes) & Number of errors');
            $axis_y1 = new \core\chart_axis();
            $axis_y1->set_labels([$this->kwn_data->exec_datetime->first->format('Y-m-d H:i'), 
                                  $this->kwn_data->exec_datetime->last->format('Y-m-d H:i')]);
            $chart->set_xaxis($axis_x);
            $chart->set_yaxis($axis_y0);    // 0番目のy軸 (Time minutes)
            $chart->set_yaxis($axis_y1, 1); // 1番目のy軸 (exec_datetime_first ~ exec_datetime_last)
            $charts[] = $chart;
        
            // Unknown user activities
            $pie_series = new core\chart_series('unknown', [$this->unk_data->ok_cnt, $this->unk_data->error_cnt]);
            $labels = ['ok', 'error'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Unknown activities');
            $charts[] = $chart;
        
            // Unknown progress view
            $this->unk_coords->y0 = new \core\chart_series('activities', $this->unk_coords->y0);
            $this->unk_coords->y1 = new \core\chart_series('error',      $this->unk_coords->y1);
            $chart = new \core\chart_line();
            $chart->set_title('Unknown activities progress view');
            $chart->set_labels($this->unk_coords->x);
            $chart->add_series($this->unk_coords->y0);
            $chart->add_series($this->unk_coords->y1);
            $axis_x = new \core\chart_axis();
            $axis_x->set_stepsize(1);
            $axis_y0 = new \core\chart_axis();
            $axis_y0->set_stepsize(1);
            $axis_y0->set_label('Time (minutes) & Number of errors');
            $axis_y1 = new \core\chart_axis();
            $axis_y1->set_labels([$this->unk_data->exec_datetime->first->format('Y-m-d H:i'), 
                                  $this->unk_data->exec_datetime->last->format('Y-m-d H:i')]);
            $chart->set_xaxis($axis_x);
            $chart->set_yaxis($axis_y0);    // 0番目のy軸 (Time minutes)
            $chart->set_yaxis($axis_y1, 1); // 1番目のy軸 (exec_datetime_first ~ exec_datetime_last)
            $charts[] = $chart;
        
            // 各ファイルのコードセルの実行回数とその内訳
            $filenames = array_keys($this->ipynbdata);
            sort($filenames);
            $file_ok_s  = [];
            $file_error_s = [];
            $charts_per_file = [];

            foreach($filenames as $filename) {
                $ipynb = $this->ipynbdata[$filename];
        
                $file_ok_s[]    = $ipynb['ok'];
                $file_error_s[] = $ipynb['error'];
        
                ksort($ipynb['codenums']);
                $labels = array_keys($ipynb['codenums']);
                $ok_s  = [];
                $error_s = [];
                foreach($labels as $label) {
                    $ok_s[]    = $ipynb['codenums'][$label]['ok'];
                    $error_s[] = $ipynb['codenums'][$label]['error'];
                }
        
                $ok_series    = new core\chart_series('ok',    $ok_s);
                $error_series = new core\chart_series('error', $error_s);
        
                $chart = new core\chart_bar();
                $chart->set_stacked(true);
                $chart->set_horizontal(true);
                $chart->add_series($ok_series);
                $chart->add_series($error_series);
                $chart->set_labels($labels);
                $chart->set_title($filename);
                $charts_per_file[] = $chart;
        
                $this->ipynbdata[$filename]['coords_y0'] = new \core\chart_series('activities', $this->ipynbdata[$filename]['coords_y0']);
                $this->ipynbdata[$filename]['coords_y1'] = new \core\chart_series('error',      $this->ipynbdata[$filename]['coords_y1']);
        
                $chart = new \core\chart_line();
                $chart->set_title($filename);
                $chart->set_labels($this->ipynbdata[$filename]['coords_x']);
                $chart->add_series($this->ipynbdata[$filename]['coords_y0']);
                $chart->add_series($this->ipynbdata[$filename]['coords_y1']);
        
                $axis_x  = new \core\chart_axis();
                $axis_x->set_label('codenum');
                $axis_y0 = new \core\chart_axis();
                $axis_y0->set_label('Time (minutes) & Number of errors');
                $axis_y0->set_stepsize(1);
                $axis_y1 = new \core\chart_axis();
                $axis_y1->set_labels([$this->ipynbdata[$filename]['exec_datetime_first']->format('Y-m-d H:i'), 
                                      $this->ipynbdata[$filename]['exec_datetime_last' ]->format('Y-m-d H:i')]);
                $chart->set_xaxis($axis_x);
                $chart->set_yaxis($axis_y0);
                $chart->set_yaxis($axis_y1, 1);
        
                $charts_per_file[] = $chart;
            }
            $labels = $filenames;
            $ok_series    = new core\chart_series('ok',  $file_ok_s);
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

        return $charts;
    }

}
