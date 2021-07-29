<?php

class  ShowDemo
{
    var $courseid     = 0;

    var $isGuest      = true;
    var $db_data      = array();

    var $action_url   = '';
    var $url_params   = array();

    // SQL
    var $sql_order    = '';
    var $sql_limit    = '';


    function  __construct($courseid)
    {
        global $CFG, $USER;

        $this->courseid   = $courseid;
        $this->url_params = array('course' => $courseid);
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
        global $CFG, $USER;

        return true;
    }


    function  print_page() 
    {
        global $CFG, $USER;

        include(__DIR__.'/../html/show_demo.php');
    }
}
