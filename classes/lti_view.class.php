<?php

require_once(__DIR__.'/../local_lib.php');
require_once(__DIR__.'/../local_dblib.php');

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
        $this->action_url = new moodle_url('/mod/ltids/actions/lti_view.php',    $this->url_params);
        $this->setup_url  = new moodle_url('/mod/ltids/actions/lti_setting.php', $this->url_params);
        $this->edit_url   = new moodle_url('/mod/ltids/actions/lti_edit.php',    $this->url_params);
        $this->error_url  = new moodle_url('/mod/ltids/actions/view.php',        $this->url_params);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_ltids', $this->error_url);
        }
        //
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/ltids:lti_view', $this->mcontext)) {
            print_error('access_forbidden', 'mod_ltids', $this->error_url);
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
                print_error('access_forbidden', 'mod_ltids', $this->error_url);
            }
            if (!confirm_sesskey()) {
                print_error('invalid_sesskey', 'mod_ltids', $this->error_url);
            }
            $this->submitted  = true;

            if (property_exists($formdata, 'disp')) {
                $no_disp  = array();
                $ltis = $DB->get_records('lti', array('course' => $this->courseid));
                foreach ($ltis as $lti) {
                    if (!array_key_exists($lti->id, $formdata->disp)) {
                        $no_disp[] = $lti->id;
                    }
                }

                $no_disp_list = implode(',', $no_disp);
                $this->minstance->no_disp_lti = $no_disp_list;
                $DB->update_record('ltids', $this->minstance);
                //
                $event = ltids_get_event($this->cmid, 'lti_setting', $this->url_params, 'no disp: '.$no_disp_list);
                $event->add_record_snapshot('course', $this->course);
                $event->add_record_snapshot('ltids',  $this->minstance);
                $event->trigger();
            }
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

