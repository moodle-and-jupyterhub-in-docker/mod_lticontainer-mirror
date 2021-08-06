<?php

require_once(__DIR__.'/../locallib.php');


class  LTIEdit
{
    var $cmid;
    var $courseid   = 0;
    var $course;

    var $ltiid      = 0;
    var $ltirec;

    var $submitted  = false;

    var $isGuest    = true;

    var $action_url = '';
    var $url_params = array();

    var $items;

    var $custom_cmds;

    // SQL
    var $sql_order  = '';
    var $sql_limit  = '';


    function  __construct($cmid, $courseid)
    {
        global $CFG, $DB, $USER;

        $this->cmid     = $cmid;
        $this->courseid = $courseid;
        $this->course   = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

        $this->url_params = array('id'=>$cmid, 'course'=>$courseid);
        $this->action_url = new moodle_url('/mod/mdlds/actions/lti_edit.php', $this->url_params);

        $this->ltiid = required_param('ltiid', PARAM_INT);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mdlds', $this->action_url);
        }
    }


    function  set_condition() 
    {
        global $CFG, $USER, $DB;

        $this->order = optional_param('order', '', PARAM_TEXT);

        return true;
    }


    function  execute()
    {
        global $CFG, $DB, $USER;

        if ($formdata = data_submitted()) {
            if (!confirm_sesskey()) {
                print_error('invalid_sesskey', 'mdlds', $this->action_url);
            }
            $this->submitted  = true;
        }
        //
        else {
            $this->ltirec = $DB->get_record('lti', array('id' => $this->ltiid), '*');
            if (!$this->ltirec) {
                print_error('no_dataf_ound', 'mdlds', $this->action_url);
            }

            $this->custom_cmds = mdlds_explode_custom_params($this->ltirec->instructorcustomparameters);
        }

        return true;
    }


    function  print_page() 
    {
        global $CFG, $DB, $OUTPUT;
        
        include(__DIR__.'/../html/lti_edit.html');
    }
}
