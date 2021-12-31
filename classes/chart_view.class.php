<?php
/**
 * chart_view.class.php
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../locallib.php');
require_once(__DIR__.'/../localdblib.php');
require_once(__DIR__.'/../localchartlib.php');



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
    var $lti_ids    = array();

    var $usernames  = array();
    var $filenames  = array();
    var $lti_info;

    var $username   = '*';
    var $filename   = '*';
    var $lti_id     = '*';

    var $sql;
    var $charts     = array();
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
        $start_date = optional_param('start_date_input', '*', PARAM_TEXT);
        $end_date   = optional_param('end_date_input',   '*', PARAM_TEXT);

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
        // Parameters を受け取る
        $this->chart_kind = optional_param('chart_kind', 'total_pie', PARAM_TEXT);
        $this->username   = optional_param('user_select_box', '*',    PARAM_TEXT);
        $this->filename   = optional_param('file_select_box', '*',    PARAM_TEXT);
        $this->lti_id     = optional_param('lti_select_box',  '*',    PARAM_TEXT);

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

        //
        // call chart function
        if ($this->chart_kind === 'users_bar') {
            $this->chart_title = 'Activities per User';
            $this->charts = chart_users_bar($recs, $this->username, $this->filename);
        }
        else if ($this->chart_kind === 'codecell_bar') {
            $this->chart_title = 'Activities per Code Cell';
            $this->charts = chart_codecell_bar($recs, $this->username, $this->filename);
        }
        else if ($this->chart_kind === 'codecell_line') {
            $this->chart_title = 'User Activities per Code Cell';
            $this->charts = chart_codecell_line($recs, $this->username, $this->filename);
        }
        else {
            $this->chart_title = 'Total Activities';
            $this->charts = chart_total_pie($recs, $this->username, $this->filename);
        }

        return true;
    }


    function  print_page()
    {
        global $OUTPUT;

        $this->args = new StdClass();
        $this->args->usernames   = $this->usernames;
        $this->args->username    = $this->username;
        $this->args->filenames   = $this->filenames;
        $this->args->filename    = $this->filename;
        $this->args->lti_info    = $this->lti_info;
        $this->args->lti_id      = $this->lti_id;
        $this->args->start_date  = $this->start_date;
        $this->args->end_date    = $this->end_date;
        $this->args->chart_title = $this->chart_title;
        $this->args->chart_kind  = $this->chart_kind;
        $this->args->charts      = $this->charts;

        include(__DIR__.'/../html/chart_view.html');
    }

}
