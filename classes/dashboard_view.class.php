<?php
/**
 * Use Moodle's Charts API to visualize learning data.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../locallib.php');


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
    var $end_date;
    var $selected_username    = '*';
    var $selected_lti_inst_id = '*';

    var $records;
    var $usernames;
    var $lti_inst_info;

    var $nml_data;
    var $unk_data;
    var $ttl_data;

    var $ipynbdata = [];
    var $charts = [];



    function  __construct($cmid, $courseid, $minstance)
    {
        global $DB;

        $this->cmid      = $cmid;
        $this->courseid  = $courseid;
        $this->course    = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance = $minstance;

        //$this->url_params = array('id'=>$cmid, 'course'=>$courseid);
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
        $this->selected_username    = optional_param('user_select_box',     '*', PARAM_CLEAN);
        $this->selected_lti_inst_id = optional_param('lti_inst_select_box', '*', PARAM_CLEAN);

        // 開始日(start_date)と終了日(end_date)があるならそれを受け取る
        $_start_date = optional_param('start_date_input', '*', PARAM_CLEAN);
        $_end_date   = optional_param('end_date_input',   '*', PARAM_CLEAN);

        if($_start_date !== '*') $this->start_date = (new DateTime($_start_date))->format('Y-m-d H:i');
        if($_end_date !== '*')   $this->end_date   = (new DateTime($_end_date))->format('Y-m-d H:i');


        $this->nml = new StdClass();
        $this->nml->ok_cnt   = 0;
        $this->nml->err_cnt  = 0;
        $this->nml->ok_s     = [];
        $this->nml->err_s    = [];
        $this->nml->userdata = [];

        $this->unk = new StdClass();
        $this->unk->ok_cnt   = 0;
        $this->unk->err_cnt  = 0;
        $this->unk->ok_s     = [];
        $this->unk->err_s    = [];
        $this->unk->userdata = [];

        $this->ttl = new StdClass();
        $this->ttl->ok_cnt   = 0;
        $this->ttl->err_cnt  = 0;
        $this->ttl->ok_s     = [];
        $this->ttl->err_s    = [];
        $this->ttl->userdata = [];
    }


    function  set_condition()
    {
        //$this->order = optional_param('order', '', PARAM_TEXT);

        return true;
    }


    function  execute()
    {
        global $DB;

        // DBからデータ取得
        $dp = DataProvider::instance_generation($start_date, $end_date, '*', $selected_lti_inst_id);
        // 本環境では多分以下のようにする
        //$dp = DataProvider::instance_generation($start_date, $end_date, $course->id, $selected_lti_inst_id);

        $this->records       = $dp->get_records();
        $this->nml->userdata = $dp->get_userdata();
        $this->usernames     = array_keys($this->nml->userdata);
        $this->lti_inst_info = $DB->get_records('lti', ['course'=>$course->id], '', 'id,name');

        $this->get_chart_info();
        $this->charts = $this->create_chart_inst();

        return true;
    }


    function  print_page()
    {
//var $usernames;
//$lti_inst_info
        global $OUTPUT;

        include(__DIR__.'/../html/dashboard_view.html');
    }



    function  get_chart_info()
    {
        //
        // チャートのインスタンスを生成するための情報を収集する
        //
        
        // All activities ('*')
        if($this->selected_username === '*'):
        

            //
            foreach($this->records as $record) {
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
                if(!array_key_exists($username, $this->ttl->userdata)) {
                    if($status === 'ok') {
                        $this->ttl->userdata[$username] = ['ok' => 1, 'err' => 0];
                    } else {
                        $this->ttl->userdata[$username] = ['ok' => 0, 'err' => 1];
                    }
                } else if($status === 'ok') {
                    $this->ttl->userdata[$username]['ok']  = $this->ttl->userdata[$username]['ok'] + 1;
                } else {
                    $this->ttl->userdata[$username]['err'] = $this->ttl->userdata[$username]['err'] + 1;
                }
        
                // Unknown activities
                if(empty($record->tags)) {
                    if($status === 'ok')
                        $this->unk->ok_cnt++;
                    else
                        $this->unk->err_cnt++;
        
                    if(!array_key_exists($username, $this->unk->userdata)) {
                        if($status === 'ok') {
                            $this->unk->userdata[$username] = ['ok' => 1, 'err' => 0];
                        } else {
                            $this->unk->userdata[$username] = ['ok' => 0, 'err' => 1];
                        }
                    } else if($status === 'ok') {
                        $this->unk->userdata[$username]['ok']  = $this->unk->userdata[$username]['ok'] + 1;
                    } else {
                        $this->unk->userdata[$username]['err'] = $this->unk->userdata[$username]['err'] + 1;
                    }
        
                    continue;
                }
        
                // Known activities
                if($status === 'ok')
                    $this->nml->ok_cnt++;
                else
                    $this->nml->err_cnt++;
        
                if(!array_key_exists($username, $this->nml->userdata)) {
                    if($status === 'ok') {
                        $this->nml->userdata[$username] = ['ok' => 1, 'err' => 0];
                    } else {
                        $this->nml->userdata[$username] = ['ok' => 0, 'err' => 1];
                    }
                } else if($status === 'ok') {
                    $this->nml->userdata[$username]['ok']  = $this->nml->userdata[$username]['ok'] + 1;
                } else {
                    $this->nml->userdata[$username]['err'] = $this->nml->userdata[$username]['err'] + 1;
                }
        
                // 各ファイルの各コードセル毎の実行結果(ok/err)
                if(!array_key_exists($filename, $this->ipynbdata)) {
                    if($status === 'ok') {
                        $this->ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>1,'err'=>0]];
                        $this->ipynbdata[$filename]['ok'] = 1;
                        $this->ipynbdata[$filename]['err'] = 0;
                    } else {
                        $this->ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>0,'err'=>1]];
                        $this->ipynbdata[$filename]['ok'] = 0;
                        $this->ipynbdata[$filename]['err'] = 1;
                    }
                    continue;
                }
        
                if(!array_key_exists($codenum, $this->ipynbdata[$filename]['codenums'])) {
                    if($status === 'ok') {
                        $this->ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>1,'err'=>0];
                        $this->ipynbdata[$filename]['ok'] = $this->ipynbdata[$filename]['ok'] + 1;
                    } else {
                        $this->ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>0,'err'=>1];
                        $this->ipynbdata[$filename]['err'] = $this->ipynbdata[$filename]['err'] + 1;
                    }
                    continue;
                }
        
                if($status === 'ok') {
                    $this->ipynbdata[$filename]['codenums'][$codenum]['ok'] = $this->ipynbdata[$filename]['codenums'][$codenum]['ok'] + 1;
                    $this->ipynbdata[$filename]['ok'] = $this->ipynbdata[$filename]['ok'] + 1;
                } else {
                    $this->ipynbdata[$filename]['codenums'][$codenum]['err'] = $this->ipynbdata[$filename]['codenums'][$codenum]['err'] + 1;
                    $this->ipynbdata[$filename]['err'] = $this->ipynbdata[$filename]['err'] + 1;
                }
            }
            //
        
            foreach($this->ttl->userdata as $userdatum) {
                $this->ttl->ok_s[]  = $userdatum['ok'];
                $this->ttl->err_s[] = $userdatum['err'];
            }
            foreach($this->nml->userdata as $userdatum) {
                $this->nml->ok_s[]  = $userdatum['ok'];
                $this->nml->err_s[] = $userdatum['err'];
            }
            foreach($this->unk->userdata as $userdatum) {
                $this->unk->ok_s[]  = $userdatum['ok'];
                $this->unk->err_s[] = $userdatum['err'];
            }
        
        
        // User activities
        else:
        
            $this->ipynbdata = [];
            $ttl_first_exec_datetime = new DateTime();
            $ttl_last_exec_datetime =  new DateTime();
            $ttl_xcoords = [];
            $ttl_ycoords = [];
            $ttl_ycoords1 = [];
            $first_exec_datetime = new DateTime();
            $last_exec_datetime  = new DateTime();
            $xcoords = [];
            $ycoords = [];
            $ycoords1 = [];
            $unk_first_exec_datetime = new DateTime();
            $unk_last_exec_datetime = new DateTime();
            $unk_xcoords = [];
            $unk_ycoords = [];
            $unk_ycoords1 = [];

            foreach($this->nml->userdata[$this->selected_username] as $record) {
                $filename   = $record->tags->filename;
                $codenum    = $record->tags->codenum;
                $status     = $record->status;
                $s_datetime = new DateTime($record->s_date);
        
                // Total progress view
                if(empty($ttl_xcoords) && empty($ttl_ycoords) && empty($ttl_ycoords1)) {
                    $ttl_first_exec_datetime = $s_datetime;
                    $ttl_last_exec_datetime  = $s_datetime;
                    $ttl_xcoords[] = 0;
                    $ttl_ycoords[] = 0;
                    $ttl_ycoords1[] = $status === 'err' ? 1 : 0;
                } else {
                    $ttl_last_exec_datetime = $s_datetime;
                    $size = count($ttl_xcoords);
                    $ttl_xcoords[] = $size;
                    $diff = $ttl_first_exec_datetime->diff($s_datetime);
                    $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                    $ttl_ycoords[] = $diff;
                    $ttl_ycoords1[] = $status === 'err' ? $ttl_ycoords1[$size - 1] + 1 : $ttl_ycoords1[$size - 1];
                }
        
                // Unknown activities
                if(empty($record->tags)) {
                    if($status === 'ok')
                        $this->unk->ok_cnt++;
                    else
                        $this->unk->err_cnt++;
        
                    // Unknown progress view
                    if(empty($unk_xcoords) && empty($unk_ycoords) && empty($unk_ycoords1)) {
                        $unk_first_exec_datetime = $s_datetime;
                        $unk_last_exec_datetime  = $s_datetime;
                        $unk_xcoords[] = 0;
                        $unk_ycoords[] = 0;
                        $unk_ycoords1[] = $status === 'err' ? 1 : 0;
                    } else {
                        $unk_last_exec_datetime = $s_datetime;
                        $size = count($unk_xcoords);
                        $unk_xcoords[] = $size;
                        $diff = $unk_first_exec_datetime->diff($s_datetime);
                        $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                        $unk_ycoords[] = $diff;
                        $unk_ycoords1[] = $status === 'err' ? $unk_ycoords1[$size - 1] + 1 : $unk_ycoords1[$size - 1];
                    }
        
                    continue;
                }
        
                // Known activities
                if($status === 'ok')
                    $this->nml->ok_cnt++;
                else
                    $this->nml->err_cnt++;
        
                // Known progress view
                if(empty($xcoords) && empty($ycoords) && empty($ycoords1)) {
                    $first_exec_datetime = $s_datetime;
                    $last_exec_datetime = $s_datetime;
                    $xcoords[] = 0;
                    $ycoords[] = 0;
                    $ycoords1[] = $status === 'err' ? 1 : 0;
                } else {
                    $last_exec_datetime = $s_datetime;
                    $size = count($xcoords);
                    $xcoords[] = $size;
                    $diff = $first_exec_datetime->diff($s_datetime);
                    $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                    $ycoords[] = $diff;
                    $ycoords1[] = $status === 'err' ? $ycoords1[$size - 1] + 1 : $ycoords1[$size - 1];
                }
        
                // 各ファイルの各コードセル毎の実行結果(ok/err)
                if(!array_key_exists($filename, $this->ipynbdata)) {
                    if($status === 'ok') {
                        $this->ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>1,'err'=>0]];
                        $this->ipynbdata[$filename]['ok'] = 1;
                        $this->ipynbdata[$filename]['err'] = 0;
                    } else {
                        $this->ipynbdata[$filename]['codenums'] = [$codenum=>['ok'=>0,'err'=>1]];
                        $this->ipynbdata[$filename]['ok'] = 0;
                        $this->ipynbdata[$filename]['err'] = 1;
                    }
        
                    $this->ipynbdata[$filename]['first_exec_datetime'] = $s_datetime;
                    $this->ipynbdata[$filename]['last_exec_datetime'] = $s_datetime;
                    $this->ipynbdata[$filename]['xcoords'] = [$codenum];
                    $this->ipynbdata[$filename]['ycoords'] = [0];
                    if($status === 'err')
                        $this->ipynbdata[$filename]['ycoords1'] = [1];
                    else
                        $this->ipynbdata[$filename]['ycoords1'] = [0];
        
                    continue;
                }
        
                $diff = $this->ipynbdata[$filename]['first_exec_datetime']->diff($s_datetime);
                $diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                $this->ipynbdata[$filename]['last_exec_datetime'] = $s_datetime;
                $this->ipynbdata[$filename]['xcoords'][] = $codenum;
                $this->ipynbdata[$filename]['ycoords'][] = $diff;
                $size = count($this->ipynbdata[$filename]['ycoords1']);
                $last_val = $this->ipynbdata[$filename]['ycoords1'][$size - 1];
                if($status === 'err')
                    $this->ipynbdata[$filename]['ycoords1'][] = $last_val + 1;
                else
                    $this->ipynbdata[$filename]['ycoords1'][] = $last_val;
        
        
                if(!array_key_exists($codenum, $this->ipynbdata[$filename]['codenums'])) {
                    if($status === 'ok') {
                        $this->ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>1,'err'=>0];
                        $this->ipynbdata[$filename]['ok'] = $this->ipynbdata[$filename]['ok'] + 1;
                    } else {
                        $this->ipynbdata[$filename]['codenums'][$codenum] = ['ok'=>0,'err'=>1];
                        $this->ipynbdata[$filename]['err'] = $this->ipynbdata[$filename]['err'] + 1;
                    }
                    continue;
                }
        
                if($status === 'ok') {
                    $this->ipynbdata[$filename]['codenums'][$codenum]['ok'] = $this->ipynbdata[$filename]['codenums'][$codenum]['ok'] + 1;
                    $this->ipynbdata[$filename]['ok'] = $this->ipynbdata[$filename]['ok'] + 1;
                } else {
                    $this->ipynbdata[$filename]['codenums'][$codenum]['err'] = $this->ipynbdata[$filename]['codenums'][$codenum]['err'] + 1;
                    $this->ipynbdata[$filename]['err'] = $this->ipynbdata[$filename]['err'] + 1;
                }
            }
        
        endif; // // //
    }
        

    
    function  create_chart_inst()
    {
        //
        // チャートのインスタンスを生成
        //
        
        $charts = [];
        
        // All activities ('*')
        if($this->selected_username === '*'):
        
            // Total(Known + Unknown) activities
            $pie_series = new core\chart_series('total', [$this->nml->ok_cnt + $this->unk->ok_cnt, $this->nml->err_cnt + $this->unk->err_cnt]);
            $labels = ['ok', 'err'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Total activities');
            $charts[] = $chart;
        
            // Known activities
            $pie_series = new core\chart_series('known', [$this->nml->ok_cnt, $this->nml->err_cnt]);
            $labels = ['ok', 'err'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Known activities');
            $charts[] = $chart;
        
            // Unknown activities
            $pie_series = new core\chart_series('unknown', [$this->unk->ok_cnt, $this->unk->err_cnt]);
            $labels = ['ok', 'err'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Unknown activities');
            $charts[] = $chart;
        
            // Total(Known + Unknown) activities per user
            $labels = array_keys($this->ttl->userdata);
            $ok_series  = new core\chart_series('ok',  $this->ttl->ok_s);
            $err_series = new core\chart_series('err', $this->ttl->err_s);
            $chart = new core\chart_bar();
            $chart->set_horizontal(true);
            $chart->set_stacked(true);
            $chart->add_series($ok_series);
            $chart->add_series($err_series);
            $chart->set_labels($labels);
            $chart->set_title('Total activities per user');
            $charts[] = $chart;
        
            // Known activities per user
            $labels = array_keys($this->nml->userdata);
            $ok_series  = new core\chart_series('ok',  $this->nml->ok_s);
            $err_series = new core\chart_series('err', $this->nml->err_s);
            $chart = new core\chart_bar();
            $chart->set_horizontal(true);
            $chart->set_stacked(true);
            $chart->add_series($ok_series);
            $chart->add_series($err_series);
            $chart->set_labels($labels);
            $chart->set_title('Known activities per user');
            $charts[] = $chart;
        
            // Unknown activities per user
            $labels = array_keys($this->unk->userdata);
            $ok_series  = new core\chart_series('ok',  $this->unk->ok_s);
            $err_series = new core\chart_series('err', $this->unk->err_s);
            $chart = new core\chart_bar();
            $chart->set_horizontal(true);
            $chart->set_stacked(true);
            $chart->add_series($ok_series);
            $chart->add_series($err_series);
            $chart->set_labels($labels);
            $chart->set_title('Unknown activities per user');
            $charts[] = $chart;
        
            // 各ファイルのコードセルの実行回数とその内訳
            $filenames = array_keys($this->ipynbdata);
            sort($filenames);
            $file_ok_s  = [];
            $file_err_s = [];
            $charts_per_file = [];
            foreach($filenames as $filename) {
                $ipynb = $this->ipynbdata[$filename];
        
                $file_ok_s[]  = $ipynb['ok'];
                $file_err_s[] = $ipynb['err'];
        
                ksort($ipynb['codenums']);
                $labels = array_keys($ipynb['codenums']);
                $ok_s  = [];
                $err_s = [];
                foreach($labels as $label) {
                    $ok_s[]  = $ipynb['codenums'][$label]['ok'];
                    $err_s[] = $ipynb['codenums'][$label]['err'];
                }
        
                $ok_series  = new core\chart_series('ok',  $ok_s);
                $err_series = new core\chart_series('err', $err_s);
        
                $chart = new core\chart_bar();
                $chart->set_stacked(true);
                $chart->set_horizontal(true);
                $chart->add_series($ok_series);
                $chart->add_series($err_series);
                $chart->set_labels($labels);
                $chart->set_title($filename);
                $charts_per_file[] = $chart;
            }
            $labels = $filenames;
            $ok_series  = new core\chart_series('ok',  $file_ok_s);
            $err_series = new core\chart_series('err', $file_err_s);
            $chart = new core\chart_bar();
            $chart->set_horizontal(true);
            $chart->set_stacked(true);
            $chart->add_series($ok_series);
            $chart->add_series($err_series);
            $chart->set_labels($labels);
            $chart->set_title('Known activities per file');
            $charts[] = $chart;
            $charts = array_merge($charts, $charts_per_file);
        
        
        // User activities
        else:
        
            // Total(Known + Unknown) user activities
            $pie_series = new core\chart_series('total', [$this->nml->ok_cnt + $this->unk->ok_cnt, $this->nml->err_cnt + $this->unk->err_cnt]);
            $labels = ['ok', 'err'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Total activities');
            $charts[] = $chart;
        
            // Total progress view
            $ttl_ycoords = new \core\chart_series('activities', $ttl_ycoords);
            $ttl_ycoords1 = new \core\chart_series('err', $ttl_ycoords1);
            $chart = new \core\chart_line();
            $chart->set_title('Total activities progress view');
            $chart->set_labels($ttl_xcoords);
            $chart->add_series($ttl_ycoords);
            $chart->add_series($ttl_ycoords1);
            $xaxis = new \core\chart_axis();
            $xaxis->set_stepsize(1);
            $yaxis = new \core\chart_axis();
            $yaxis->set_stepsize(1);
            $yaxis->set_label('Time (minutes) & Number of errs');
            $yaxis1 = new \core\chart_axis();
            $yaxis1->set_labels([$ttl_first_exec_datetime->format('Y-m-d H:i'), $ttl_last_exec_datetime->format('Y-m-d H:i')]);
            $chart->set_xaxis($xaxis);
            $chart->set_yaxis($yaxis);     // 0番目のy軸 (Time minutes)
            $chart->set_yaxis($yaxis1, 1); // 1番目のy軸 (first_exec_datetime ~ last_exec_datetime)
            $charts[] = $chart;
        
            // Known user activities
            $pie_series = new core\chart_series('known', [$this->nml->ok_cnt, $this->nml->err_cnt]);
            $labels = ['ok', 'err'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Known activities');
            $charts[] = $chart;
        
            // Known progress view
            $ycoords = new \core\chart_series('activities', $ycoords);
            $ycoords1 = new \core\chart_series('err', $ycoords1);
            $chart = new \core\chart_line();
            $chart->set_title('Known activities progress view');
            $chart->set_labels($xcoords);
            $chart->add_series($ycoords);
            $chart->add_series($ycoords1);
            $xaxis = new \core\chart_axis();
            $xaxis->set_stepsize(1);
            $yaxis = new \core\chart_axis();
            $yaxis->set_stepsize(1);
            $yaxis->set_label('Time (minutes) & Number of errs');
            $yaxis1 = new \core\chart_axis();
            $yaxis1->set_labels([$first_exec_datetime->format('Y-m-d H:i'), $last_exec_datetime->format('Y-m-d H:i')]);
            $chart->set_xaxis($xaxis);
            $chart->set_yaxis($yaxis);     // 0番目のy軸 (Time minutes)
            $chart->set_yaxis($yaxis1, 1); // 1番目のy軸 (first_exec_datetime ~ last_exec_datetime)
            $charts[] = $chart;
        
            // Unknown user activities
            $pie_series = new core\chart_series('unknown', [$this->unk->ok_cnt, $this->unk->err_cnt]);
            $labels = ['ok', 'err'];
            $chart = new \core\chart_pie();
            $chart->add_series($pie_series);
            $chart->set_labels($labels);
            $chart->set_title('Unknown activities');
            $charts[] = $chart;
        
            // Unknown progress view
            $unk_ycoords = new \core\chart_series('activities', $unk_ycoords);
            $unk_ycoords1 = new \core\chart_series('err', $unk_ycoords1);
            $chart = new \core\chart_line();
            $chart->set_title('Unknown activities progress view');
            $chart->set_labels($unk_xcoords);
            $chart->add_series($unk_ycoords);
            $chart->add_series($unk_ycoords1);
            $xaxis = new \core\chart_axis();
            $xaxis->set_stepsize(1);
            $yaxis = new \core\chart_axis();
            $yaxis->set_stepsize(1);
            $yaxis->set_label('Time (minutes) & Number of errs');
            $yaxis1 = new \core\chart_axis();
            $yaxis1->set_labels([$unk_first_exec_datetime->format('Y-m-d H:i'), $unk_last_exec_datetime->format('Y-m-d H:i')]);
            $chart->set_xaxis($xaxis);
            $chart->set_yaxis($yaxis);     // 0番目のy軸 (Time minutes)
            $chart->set_yaxis($yaxis1, 1); // 1番目のy軸 (first_exec_datetime ~ last_exec_datetime)
            $charts[] = $chart;
        
            // 各ファイルのコードセルの実行回数とその内訳
            $filenames = array_keys($this->ipynbdata);
            sort($filenames);
            $file_ok_s  = [];
            $file_err_s = [];
            $charts_per_file = [];
            foreach($filenames as $filename) {
                $ipynb = $this->ipynbdata[$filename];
        
                $file_ok_s[]  = $ipynb['ok'];
                $file_err_s[] = $ipynb['err'];
        
                ksort($ipynb['codenums']);
                $labels = array_keys($ipynb['codenums']);
                $ok_s  = [];
                $err_s = [];
                foreach($labels as $label) {
                    $ok_s[]  = $ipynb['codenums'][$label]['ok'];
                    $err_s[] = $ipynb['codenums'][$label]['err'];
                }
        
                $ok_series  = new core\chart_series('ok',  $ok_s);
                $err_series = new core\chart_series('err', $err_s);
        
                $chart = new core\chart_bar();
                $chart->set_stacked(true);
                $chart->set_horizontal(true);
                $chart->add_series($ok_series);
                $chart->add_series($err_series);
                $chart->set_labels($labels);
                $chart->set_title($filename);
                $charts_per_file[] = $chart;
        
        
                $this->ipynbdata[$filename]['ycoords'] = new \core\chart_series('activities', $this->ipynbdata[$filename]['ycoords']);
                $this->ipynbdata[$filename]['ycoords1'] = new \core\chart_series('err', $this->ipynbdata[$filename]['ycoords1']);
        
                $chart = new \core\chart_line();
                $chart->set_title($filename);
                $chart->set_labels($this->ipynbdata[$filename]['xcoords']);
                $chart->add_series($this->ipynbdata[$filename]['ycoords']);
                $chart->add_series($this->ipynbdata[$filename]['ycoords1']);
        
                $xaxis = new \core\chart_axis();
                $xaxis->set_label('codenum');
                $yaxis = new \core\chart_axis();
                $yaxis->set_label('Time (minutes) & Number of errs');
                $yaxis->set_stepsize(1);
                $yaxis1 = new \core\chart_axis();
                $yaxis1->set_labels([$this->ipynbdata[$filename]['first_exec_datetime']->format('Y-m-d H:i'), 
                                     $this->ipynbdata[$filename]['last_exec_datetime']->format('Y-m-d H:i')]);
                $chart->set_xaxis($xaxis);
                $chart->set_yaxis($yaxis);
                $chart->set_yaxis($yaxis1, 1);
        
                $charts_per_file[] = $chart;
            }
            $labels = $filenames;
            $ok_series  = new core\chart_series('ok',  $file_ok_s);
            $err_series = new core\chart_series('err', $file_err_s);
            $chart = new core\chart_bar();
            $chart->set_horizontal(true);
            $chart->set_stacked(true);
            $chart->add_series($ok_series);
            $chart->add_series($err_series);
            $chart->set_labels($labels);
            $chart->set_title('Known activities per file');
            $charts[] = $chart;
            $charts = array_merge($charts, $charts_per_file);
        
        
        endif; // // //

        return $charts;
    }
        

}
