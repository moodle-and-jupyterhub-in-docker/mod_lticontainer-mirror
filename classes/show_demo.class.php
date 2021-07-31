<?php

class  ShowDemo
{
    var $courseid     = 0;
    var $course;

    var $isGuest      = true;
    var $db_data      = array();

    var $action_url   = '';
    var $url_params   = array();

    var $items;

    // SQL
    var $sql_order    = '';
    var $sql_limit    = '';


    function  __construct($cmid, $courseid)
    {
        global $CFG, $DB, $USER;

        $this->courseid   = $courseid;
        $this->course     = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->url_params = array('id'=>$cmid, 'course'=>$courseid);
        $this->action_url = new moodle_url('/mod/mdlds/actions/show_demo.php', $this->url_params);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('mdlds_access_forbidden', 'mdlds', $this->action_url);
        }
    }


    function  set_condition() 
    {
        global $CFG, $USER, $DB;

        $this->order = optional_param('order', '', PARAM_TEXT);

        // Post Check
        if (data_submitted()) {
            if (!confirm_sesskey()) {
                print_error('sesskey_error', 'mdlds', $this->action_url);
            }
        }
        return true;
    }


    function  execute()
    {
        global $CFG, $DB, $USER;

        // Check
/*
        if (data_submitted()) {
            if (!confirm_sesskey()) {
            }
        }
*/

        $sort   = '';
        $fields = 'id, name, instructorcustomparameters';
        $this->items = $DB->get_records('lti', array('course' => $this->courseid), $sort, $fields);

        return true;
    }


    function  print_page() 
    {
        global $CFG, $DB, $OUTPUT;

        include(__DIR__.'/../html/show_demo.html');
        //include(__DIR__.'/../html/show_demo2.html');
    }
}
