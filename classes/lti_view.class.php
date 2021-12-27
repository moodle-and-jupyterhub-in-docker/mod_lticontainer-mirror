<?php

require_once(__DIR__.'/../locallib.php');
require_once(__DIR__.'/../localdblib.php');

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
        $this->action_url = new moodle_url('/mod/ltids/actions/lti_view.php',    $this->url_params);
        $this->setup_url  = new moodle_url('/mod/ltids/actions/lti_setting.php', $this->url_params);
        $this->edit_url   = new moodle_url('/mod/ltids/actions/lti_edit.php',    $this->url_params);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_ltids', $this->action_url);
        }
        //
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/ltids:lti_view', $this->mcontext)) {
            print_error('access_forbidden', 'mod_ltids', $this->action_url);
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
            if (!has_capability('mod/ltids:lti_setting', $this->mcontext)) {
                print_error('access_forbidden', 'mod_ltids', $this->action_url);
            }
            if (!confirm_sesskey()) {
                print_error('invalid_sesskey', 'mod_ltids', $this->action_url);
            }
            $this->submitted  = true;

            $this->minstance->no_disp_lti = '';
            if (property_exists($formdata, 'nodisp')) {
                $no_disp = array();
                $nodisps = $formdata->nodisp;
                foreach ($nodisps as $key => $val) {
                    $no_disp[] = $key;
                }
                $no_disp_list = implode(',', $no_disp);
                $event = ltids_get_event($this->cmid, 'lti_setting', $this->url_params, 'no disp: '.$no_disp_list);
                $event->add_record_snapshot('course', $this->course);
                $event->add_record_snapshot('ltids',  $this->minstance);
                $event->trigger();
                $this->minstance->no_disp_lti = $no_disp_list;
            }
            $DB->update_record('ltids', $this->minstance);
        }

        $this->ltis = db_get_valid_ltis($this->courseid, $this->minstance);

        return true;
    }


    function  print_page() 
    {
        global $OUTPUT;

        include(__DIR__.'/../html/lti_view.html');
    }
}

