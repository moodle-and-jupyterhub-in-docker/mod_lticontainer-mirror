<?php

require_once(__DIR__.'/../locallib.php');


class  LTIEdit
{
    var $cmid;
    var $courseid   = 0;
    var $course;
    var $minstance;

    var $ltiid      = 0;
    var $ltirec;
    var $images     = array();

    var $submitted  = false;

    var $isGuest    = true;

    var $action_url = '';
    var $url_params = array();

    var $custom_ary = array();
    var $custom_txt = '';


    function  __construct($cmid, $courseid, $minstance)
    {
        global $CFG, $DB;

        $this->cmid      = $cmid;
        $this->courseid  = $courseid;
        $this->course    = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance = $minstance;
        #
        $this->ltiid = required_param('ltiid', PARAM_INT);

        $this->url_params = array('id'=>$cmid, 'course'=>$courseid, 'ltiid'=>$this->ltiid);
        $this->action_url = new moodle_url('/mod/mdlds/actions/lti_edit.php', $this->url_params);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mdlds', $this->action_url);
        }
    }


    function  set_condition() 
    {
        return true;
    }


    function  execute()
    {
        global $CFG, $DB;

        $fields = 'id, course, name, instructorcustomparameters, timemodified';
        $this->ltirec = $DB->get_record('lti', array('id' => $this->ltiid), $fields);
        if (!$this->ltirec) {
            print_error('no_dataf_ound', 'mdlds', $this->action_url);
        }

        // POST
        if ($formdata = data_submitted()) {
            if (!confirm_sesskey()) {
                print_error('invalid_sesskey', 'mdlds', $this->action_url);
            }
            $this->submitted  = true;
            $this->custom_txt = mdlds_join_custom_params($formdata);
            $this->ltirec->instructorcustomparameters = $this->custom_txt;
            $this->ltirec->timemodified = time();
            $DB->update_record('lti', $this->ltirec);
        }

        //
        $rslts = docker_exec('images', $this->minstance);

        $i = 0;
        foreach ($rslts as $rslt) {
            if ($i==0) $this->images[$i] = '';
            else {
                $rslt  = preg_replace("/\s+/", ' ', trim($rslt));
                $image = explode(' ', $rslt);
                $this->images[$i] = $image[0];
            }
            $i++;
        }
        $this->custom_txt = $this->ltirec->instructorcustomparameters;
        $this->custom_ary = mdlds_explode_custom_params($this->custom_txt);

        return true;
    }


    function  print_page() 
    {
        global $CFG, $DB, $OUTPUT;
        
        include(__DIR__.'/../html/lti_edit.html');
    }
}
