<?php

require_once(__DIR__.'/../locallib.php');


class  LTIEdit
{
    var $cmid;
    var $courseid   = 0;
    var $course;

    var $ltiid      = 0;
    var $ltirec;
    var $images     = array();

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
        //global $CFG, $USER, $DB;

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
            //print_r(unserialize($formdata->others));
print("I'm here!");
die();
        }
        //
        else {
            $this->ltirec = $DB->get_record('lti', array('id' => $this->ltiid), '*');
            if (!$this->ltirec) {
                print_error('no_dataf_ound', 'mdlds', $this->action_url);
            }

            $rslts = array();
            exec('/usr/bin/docker -H unix:///var/run/mdlds_172.22.1.75.sock images', $rslts);

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
