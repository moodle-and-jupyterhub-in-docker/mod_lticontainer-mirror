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


define('CHART_BAR_LINENUM', 10);


class  ChartView
{
    var $cmid;
    var $courseid   = 0;
    var $course;
    var $minstance;
    var $mcontext;

    var $isGuest    = true;

    var $action_url = '';
    var $error_url  = '';
    var $url_params = array();

    var $start_date = '';
    var $end_date   = '';
    var $usernames  = array();
    var $filenames  = array();
    var $lti_ids    = array();
    var $lti_info;

    var $username   = '*';
    var $lti_id     = '*';
    var $filename   = '*';

    var $sql;
    var $charts     = array();
    var $chart_mode;
    var $chart_kind;
    var $chart_title;

    var $args;


    function  __construct($cmid, $courseid, $minstance)
    {
        global $DB;

        $this->cmid      = $cmid;
        $this->courseid  = $courseid;
        $this->course    = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance = $minstance;

        $this->url_params = array('id'=>$cmid);
        $this->action_url = new moodle_url('/mod/ltids/actions/chart_view.php', $this->url_params);
        $this->error_url  = new moodle_url('/mod/ltids/actions/view.php', $this->url_params);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_ltids', $this->error_url);
        }
        //
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/ltids:chart_view', $this->mcontext)) {
            print_error('access_forbidden', 'mod_ltids', $this->error_url);
        }

        ////////////////////////////////////////////////////////////////////////////
        // 取得するレコードの範囲 (デフォルト)

        // 開始日(start_date)と終了日(end_date)があるならそれを受け取る
        $start_date = optional_param('start_date_input', '*', PARAM_CLEAN);
        $end_date   = optional_param('end_date_input',   '*', PARAM_CLEAN);

$start_date = '2021-10-1 00:00';
        $obj_datetime = new DateTime();
        if ($end_date == '*') {
            $this->end_date   = $obj_datetime->format('Y-m-d H:i');
        }
        else {
            $this->end_date   = (new DateTime($end_date))->format('Y-m-d H:i');
        }
        if ($start_date == '*') {
            $obj_datetime->sub(new DateInterval('PT5400S'));        // 1:30 前
            $this->start_date = $obj_datetime->format('Y-m-d H:i');
        }
        else {
            $this->start_date = (new DateTime($start_date))->format('Y-m-d H:i');
        }

        //
        //  を受け取る
        $this->username   = optional_param('user_select_box', '*',    PARAM_CLEAN);
        $this->filename   = optional_param('file_select_box', '*',    PARAM_CLEAN);
        $this->chart_kind = optional_param('chart_kind', 'total_pie', PARAM_CLEAN);
        $this->chart_mode = optional_param('chart_kind', 'full',      PARAM_CLEAN);

        // LTI ID を受け取る
        $this->lti_id = optional_param('lti_select_box', '*', PARAM_CLEAN);

        $this->lti_info = db_get_valid_ltis($this->courseid, $this->minstance);
        foreach ($this->lti_info as $lti) {
            $this->lti_ids[] = $lti->id;
        }
    }


    function  set_condition()
    {
        if ($this->lti_id == '*') $lti_id = $this->lti_ids;
        else                      $lti_id = $this->lti_id;

        $this->sql  = get_base_sql($this->courseid, $this->start_date, $this->end_date);
        $this->sql .= get_lti_sql_condition($lti_id);

        return true;
    }


    function  execute()
    {
        global $DB;

        $recs = $DB->get_records_sql($this->sql);
        foreach ($recs as $rec) {
            if (!empty($rec->username)) $this->usernames[$rec->username] = $rec->username;
            if (!empty($rec->filename)) $this->filenames[$rec->filename] = $rec->filename;
        }
        ksort($this->usernames);
        ksort($this->filenames);

        $this->args = new StdClass();
        $this->args->usernames   = $this->usernames;
        $this->args->username    = $this->username;
        $this->args->filenames   = $this->filenames;
        $this->args->filename    = $this->filename;
        $this->args->lti_info    = $this->lti_info;
        $this->args->lti_id      = $this->lti_id;
        $this->args->start_date  = $this->start_date;
        $this->args->end_date    = $this->end_date;
        $this->args->chart_kind  = $this->chart_kind;
        $this->args->chart_mode  = $this->chart_mode;

        //
        if ($this->chart_kind === 'users_bar') {
            $this->args = $this->chart_users_bar($recs, $this->args);
        }
        else {
            //$this->args = $this->chart_total_pie($recs, $this->args);
            $this->args = $this->chart_users_bar($recs, $this->args);
        }
        
        return true;
    }


    function  print_page()
    {
        global $OUTPUT;

        include(__DIR__.'/../html/chart_view.html');
    }


    function  chart_total_pie($recs, $args)
    {
        $ok = 0;
        $er = 0;

        $exclsn = false;
        foreach ($recs as $rec) {
            //
            if ($args->username !== '*' and $rec->username !== $args->username) $exclsn = true;
            if (!$exclsn) {
                if ($args->filename !== '*' and $rec->filename !== $args->filename) $exclsn = true;
            }
            //
            if (!$exclsn) {
                if ($rec->status == 'ok') $ok++;
                else                      $er++;
            }
            $exclsn = false;
        }

        // Total(Known + Unknown) activities
        $series = new core\chart_series('Count', [$ok, $er]);
        $labels = ['OK', 'ERROR'];
        $chart  = new \core\chart_pie();
        $chart->add_series($series);
        $chart->set_labels($labels);
        //$chart->set_title('Total Activities');
        //
        $args->charts[] = $chart;
        $args->chart_title = 'Total Activities';
    
        return $args;
    }


    //
    function  chart_users_bar($recs, $args)
    {
        $user_data = array();
        //
        $exclsn = false;
        foreach ($recs as $rec) {
            //
            if ($args->username !== '*' and $rec->username !== $args->username) $exclsn = true;
            if (!$exclsn) {
                if ($args->filename !== '*' and $rec->filename !== $args->filename) $exclsn = true;
            }
            //
            if (!$exclsn) {
                $username = $rec->username;
                if(!array_key_exists($username, $user_data)) {
                    $user_data[$username] = ['ok'=>0,'er'=>0];
                }
                if ($rec->status == 'ok') {
                    $user_data[$username]['ok']++;
                }
                else {
                    $user_data[$username]['er']++;
                }
            }
            $exclsn = false;
        }

        // all data
        $maxval = 0;
        $us_srs = array();
        $ok_srs = array();
        $er_srs = array();

        foreach ($user_data as $name => $data) {
            $us_srs[] = $name;
            $ok_srs[] = $data['ok'];
            $er_srs[] = $data['er'];
            if ($maxval < $data['ok'] + $data['er']) $maxval = $data['ok'] + $data['er'];
        }
        $stepsz = ceil($maxval/4);
        $pw     = 10**(strlen($stepsz)-1);
        $stepsz = floor($stepsz/$pw)*$pw;
        if ($stepsz==0) $stepsz = 1; 
        $maxval = $stepsz*5;

        //
        $args->charts = array();
        $args->chart_title = 'Total Activities per user';

        $array_num = count($us_srs);
        $cnt = 0;
        $num = 0;
        while ($num < $array_num) {
            //            
            $us_wrk = array();
            $ok_wrk = array();
            $er_wrk = array();
            /*
            if ($args->username === '*' and $array_num > CHART_BAR_LINENUM) {
                $us_wrk[] = 'SCALE';
                $ok_wrk[] = $maxval;
                $er_wrk[] = 0;
            }
            */

            $stop_i = CHART_BAR_LINENUM;
            if (($cnt+1)*CHART_BAR_LINENUM > $array_num) {
                $stop_i = $array_num % CHART_BAR_LINENUM;
            }
            $cnt++;

            for ($i=0; $i<$stop_i; $i++) {
                $us_wrk[] = $us_srs[$num];
                $ok_wrk[] = $ok_srs[$num];
                $er_wrk[] = $er_srs[$num];
                $num++;
            }
            for ($i=$stop_i; $i<CHART_BAR_LINENUM; $i++) {
                $us_wrk[] = '';
                $ok_wrk[] = 0;
                $er_wrk[] = 0;
            }

            $chart = new core\chart_bar();
            $chart->set_horizontal(true);
            $chart->set_stacked(true);
            $chart->add_series(new core\chart_series('OK',    $ok_wrk));
            $chart->add_series(new core\chart_series('ERROR', $er_wrk));
            $chart->set_labels($us_wrk);
            //
            $xaxis = $chart->get_xaxis(0, true);
            $xaxis->set_max($maxval);
            $xaxis->set_stepsize($stepsz);
            //
            $args->charts[] = $chart;
        }

        return $args;
    }


}
