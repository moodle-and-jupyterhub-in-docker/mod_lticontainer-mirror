<?php
/**
 * dashboard_view.class.php
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../locallib.php');
require_once(__DIR__.'/../localdblib.php');
require_once(__DIR__.'/../localchartlib.php');


class  DashboardView
{
    var $cmid;
    var $courseid    = 0;
    var $course;
    var $minstance;
    var $mcontext;

    var $isGuest     = true;

    var $action_url  = '';
    var $error_url   = '';
    var $url_params  = array();

    var $start_date  = '';
    var $end_date    = '';
    var $lti_ids     = array();

    var $sql;
    var $charts_data = array();


    function  __construct($cmid, $courseid, $minstance)
    {
        global $DB;

        $this->cmid      = $cmid;
        $this->courseid  = $courseid;
        $this->course    = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance = $minstance;

        $this->url_params = array('id'=>$cmid);
        $this->action_url = new moodle_url('/mod/ltids/actions/dashboard_view.php', $this->url_params);
        $this->error_url  = new moodle_url('/mod/ltids/actions/view.php', $this->url_params);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_ltids', $this->error_url);
        }
        //
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/ltids:dashboard_view', $this->mcontext)) {
            print_error('access_forbidden', 'mod_ltids', $this->error_url);
        }

        ////////////////////////////////////////////////////////////////////////////
        $obj_datetime = new DateTime();
        $this->end_date   = $obj_datetime->format('Y-m-d H:i');
        $obj_datetime->sub(new DateInterval('PT5400S'));        // 1:30 前
        $this->start_date = $obj_datetime->format('Y-m-d H:i');
$this->start_date = '2021-10-1 00:00';

        $this->lti_info = db_get_valid_ltis($this->courseid, $this->minstance);
        foreach ($this->lti_info as $lti) {
            $this->lti_ids[] = $lti->id;
        }
    }


    function  set_condition()
    {
        $this->sql  = get_base_sql($this->courseid, $this->start_date, $this->end_date);
        $this->sql .= get_lti_sql_condition($this->lti_ids);

        return true;
    }


    function  execute()
    {
        global $DB;

        $recs = $DB->get_records_sql($this->sql);
        $this->charts_data = chart_dashboard($recs);

        return true;
    }


    function  print_page()
    {
        global $OUTPUT;

        include(__DIR__.'/../html/dashboard_view.html');
    }

}
