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
    var $courseid     = 0;
    var $course;
    var $minstance;
    var $mcontext;

    var $isGuest      = true;

    var $action_url   = '';
    var $error_url    = '';
    var $url_params   = array();

    var $start_date_r = '';
    var $start_date_a = '';
    var $start_date   = '';
    var $end_date     = '';
    var $lti_ids      = array();

    var $sql_r;         // SQL for Real Time
    var $sql_a;         // SQL for Any Period Time
    var $charts_data  = array();


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
        $startdiff_r = $this->minstance->during_chart;
        $startdiff_a = $this->minstance->during_dashboard;
        if ($startdiff_r <= 0) $startdiff_r = CHART_REALTIME_DURING;
        if ($startdiff_a <= 0) $startdiff_a = CHART_ANYTIME_DURING;

        $obj_datetime = new DateTime();
        $this->end_date = $obj_datetime->format('Y-m-d H:i');

        $startdiff  = $startdiff_r;
        $obj_datetime->sub(new DateInterval('PT'.$startdiff.'S'));
        $this->start_date_r = $obj_datetime->format('Y-m-d H:i');

        $startdiff += $startdiff_a - $startdiff_r;
        $obj_datetime->sub(new DateInterval('PT'.$startdiff.'S'));
        $this->start_date_a = $obj_datetime->format('Y-m-d H:i');

        $this->lti_info = db_get_valid_ltis($this->courseid, $this->minstance);
        foreach ($this->lti_info as $lti) {
            $this->lti_ids[] = $lti->id;
        }
    }


    function  set_condition()
    {
        $this->sql_r  = get_base_sql($this->courseid, $this->start_date_r, $this->end_date);
        $this->sql_r .= get_lti_sql_condition($this->lti_ids);
        //
        $this->sql_a  = get_base_sql($this->courseid, $this->start_date_a, $this->end_date);
        $this->sql_a .= get_lti_sql_condition($this->lti_ids);

        return true;
    }


    function  execute()
    {
        global $DB;

        $recs_r = $DB->get_records_sql($this->sql_r);
        $recs_a = $DB->get_records_sql($this->sql_a);
        $this->charts_data = chart_dashboard($recs_r, $recs_a, $this->minstance);

        return true;
    }


    function  print_page()
    {
        global $OUTPUT;

        include(__DIR__.'/../html/dashboard_view.html');
    }

}
