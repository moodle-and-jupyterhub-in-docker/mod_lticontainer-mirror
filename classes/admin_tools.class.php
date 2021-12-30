<?php

require_once(__DIR__.'/../locallib.php');


class  AdminTools
{
    var $cmid;
    var $courseid   = 0;
    var $course;
    var $minstance;
    var $mcontext;
    var $host_name  = 'localhost';

    var $submitted  = false;
    var $isGuest    = true;

    var $url_params = array();
    var $action_url = '';
    var $error_url  = '';


    function  __construct($cmid, $courseid, $minstance)
    {
        global $CFG, $DB;

        $this->cmid      = $cmid;
        $this->courseid  = $courseid;
        $this->course    = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance = $minstance;
        $this->host_name = parse_url($CFG->wwwroot, PHP_URL_HOST);
        #
        $this->url_params = array('id'=>$cmid, 'course'=>$courseid);
        $this->action_url = new moodle_url('/mod/ltids/actions/admin_tools.php', $this->url_params);
        $this->error_url  = new moodle_url('/mod/ltids/actions/view.php',        $this->url_params);
    }


    function  set_condition() 
    {
        return true;
    }


    function  execute()
    {
        global $DB, $USER;

        /*
        $recs = $DB->get_records('ltids_websock_data');
        foreach ($recs as $rec) {
            print_r($rec);
            echo '<br />';
            //
            $rec->id = null;
            if ($rec->host=='server') {
                $ret = $DB->insert_record('ltids_websock_server_data', $rec);
            }
            else if ($rec->host=='client') {
                $ret = $DB->insert_record('ltids_websock_client_data', $rec);
            }
        }
        */

        /*
        $properties = 'filename|codenum';
        $patterns   = "/\"(${properties})\s*:\s*([^\s\"]+)\"/u";

        $recs = $DB->get_records('ltids_websock_tags');
        foreach ($recs as $rec) {
            print_r($rec);
            echo '<br />';

            if ($rec->filename==null) {
                preg_match_all($patterns, $rec->tags, $matches, PREG_SET_ORDER);

                foreach($matches as $match) {
                    $rec->{$match[1]} = $match[2];
                }
                $DB->update_record('ltids_websock_tags', $rec);
            }
        }
        */

        //
        // POST
        if ($submit_data = data_submitted()) {
        }

        //
        return true;
    }


    function  print_page() 
    {
        global $CFG, $DB, $OUTPUT;
        
        include(__DIR__.'/../html/admin_tools.html');
    }
}
