<?php

defined('MOODLE_INTERNAL') || die();


require_once(__DIR__.'/../locallib.php');
require_once(__DIR__.'/../locallib_db.php');



class  LTIConnect
{
    var $cmid;
    var $courseid   = 0;
    var $course;
    var $minstance;
    var $mcontext;

    var $isGuest    = true;

    var $action_url = '';
    var $setup_url  = '';
    var $edit_url   = '';
    var $error_url  = '';
    var $url_params = array();

    var $ltis;


    function  __construct($cmid, $courseid, $minstance)
    {
        global $DB;
        
        $this->cmid      = $cmid;
        $this->courseid  = $courseid;
        $this->course    = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance = $minstance;

        //$this->url_params = array('id'=>$cmid, 'course'=>$courseid);
        $this->url_params = array('id'=>$cmid);
        $this->action_url = new moodle_url('/mod/lticontainer/actions/lti_view.php',    $this->url_params);
        $this->setup_url  = new moodle_url('/mod/lticontainer/actions/lti_setting.php', $this->url_params);
        $this->edit_url   = new moodle_url('/mod/lticontainer/actions/lti_edit.php',    $this->url_params);
        $this->error_url  = new moodle_url('/mod/lticontainer/actions/view.php',        $this->url_params);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
        }
        //
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/lticontainer:lti_view', $this->mcontext)) {
            print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
        }
    }


    function  set_condition() 
    {
        $this->order = optional_param('order', '', PARAM_TEXT);

        return true;
    }


    function  execute()
    {
        global $DB;

        // POST
        if ($formdata = data_submitted()) {
            if (!has_capability('mod/lticontainer:lti_setting', $this->mcontext)) {
                print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
            }
            if (!confirm_sesskey()) {
                print_error('invalid_sesskey', 'mod_lticontainer', $this->error_url);
            }
            $this->submitted  = true;

            $disp_list = '';
            if (property_exists($formdata, 'disp')) {
                $disp  = array();
                //$ltis = $DB->get_records('lti', array('course' => $this->courseid));
                $ltis = db_get_valid_ltis($this->courseid);
                foreach ($ltis as $lti) {
                    if (array_key_exists($lti->id, $formdata->disp)) $disp[] = $lti->id;
                }
                $disp_list = implode(',', $disp);
            }
            //
            $this->minstance->display_lti = $disp_list;
            $DB->update_record('lticontainer', $this->minstance);
            //
            $event = lticontainer_get_event($this->cmid, 'lti_setting', $this->url_params, 'display: '.$disp_list);
            $event->add_record_snapshot('course', $this->course);
            $event->add_record_snapshot('lticontainer',  $this->minstance);
            $event->trigger();
        }

        autoset_jupyterhub_url($this->courseid, $this->minstance);
        $this->ltis = db_get_disp_ltis($this->courseid, $this->minstance);

        return true;
    }


    function  print_page() 
    {
        global $OUTPUT;

        include(__DIR__.'/../html/lti_view.html');
    }
}

