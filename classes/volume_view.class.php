<?php

require_once(__DIR__.'/../locallib.php');

class  VolumeView
{
    var $cmid;
    var $courseid   = 0;
    var $course;

    var $isGuest    = true;

    var $action_url = '';
    var $edit_url   = '';
    var $url_params = array();

    var $items      = array();

    function  __construct($cmid, $courseid)
    {
        global $DB;
        
        $this->cmid     = $cmid;
        $this->courseid = $courseid;
        $this->course   = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

        $this->url_params = array('id'=>$cmid, 'course'=>$courseid);
        $this->action_url = new moodle_url('/mod/mdlds/actions/volume_view.php', $this->url_params);
        //$this->edit_url   = new moodle_url('/mod/mdlds/actions/volume_edit.php', $this->url_params);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mdlds', $this->action_url);
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

        $rslts = array();
        exec('/usr/bin/docker -H unix:///var/run/mdlds_172.22.1.75.sock volume ls', $rslts);

        $check_course = '_'.$this->courseid;
        $len_check = strlen($check_course);

        $i = 0;
        foreach ($rslts as $rslt) {
            $rslt = preg_replace("/\s+/", ' ', trim($rslt));
            $vol  = explode(' ', $rslt);
            if (isset($vol[1])) {
                $cmd = '';
                if      (!strncmp(MDLDS_LTI_VOLUME_CMD, $vol[1], strlen(MDLDS_LTI_VOLUME_CMD))) {
                    $cmd = 'VOL';
                    $len_cmd = strlen(MDLDS_LTI_VOLUME_CMD);
                }
                else if (!strncmp(MDLDS_LTI_SUBMIT_CMD, $vol[1], strlen(MDLDS_LTI_SUBMIT_CMD))) {
                    $cmd = 'SUB';
                    $len_cmd = strlen(MDLDS_LTI_SUBMIT_CMD);
                }

                if ($cmd!='' and substr($vol[1], -$len_check)==$check_course) { 
                    $this->items[$i] = new stdClass();
                    $this->items[$i]->driver   = $vol[0];
                    $this->items[$i]->fullname = $vol[1]; 
                    $this->items[$i]->volname  = substr($vol[1], 0, strlen($vol[1])-$len_check); 
                    $this->items[$i]->shtname  = substr($vol[1], $len_cmd, strlen($vol[1])-$len_check-$len_cmd); 
                    $this->items[$i]->command  = $cmd; 
                    $i++;
                }
            }
        }
        
        return true;
    }


    function  print_page() 
    {
        global $OUTPUT;

        include(__DIR__.'/../html/volume_view.html');
    }
}
