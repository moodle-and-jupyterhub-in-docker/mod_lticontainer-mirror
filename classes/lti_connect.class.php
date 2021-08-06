<?php

class  LTIConnect
{
    var $cmid;
    var $courseid   = 0;
    var $course;

    var $isGuest    = true;

    var $action_url = '';
    var $edit_url   = '';
    var $url_params = array();

    var $items;

    function  __construct($cmid, $courseid)
    {
        global $DB;
        
        $this->cmid     = $cmid;
        $this->courseid = $courseid;
        $this->course   = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

        $this->url_params = array('id'=>$cmid, 'course'=>$courseid);
        $this->action_url = new moodle_url('/mod/mdlds/actions/lti_connect.php', $this->url_params);
        $this->edit_url   = new moodle_url('/mod/mdlds/actions/lti_edit.php', $this->url_params);

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

        $sort   = '';
        $fields = 'id,name,instructorcustomparameters';
        $this->items = $DB->get_records('lti', array('course' => $this->courseid), $sort, $fields);

        return true;
    }


    function  print_page() 
    {
        global $OUTPUT;

        include(__DIR__.'/../html/lti_connect.html');
    }
}
