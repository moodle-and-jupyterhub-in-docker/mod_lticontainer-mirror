<?php

defined('MOODLE_INTERNAL') || die();


require_once(__DIR__.'/../locallib.php');


class  JupyterHubAPI
{
    var $cmid;
    var $courseid   = 0;
    var $course;
    var $minstance;
    var $mcontext;
    var $ccontext;
    var $host_name  = 'localhost';

    var $submitted  = false;
    var $isGuest    = true;

    var $url_params = array();
    var $action_url = '';
    var $error_url  = '';

    var $users      = array();
    var $api_token  = '';
    var $api_url    = '';


    function  __construct($cmid, $courseid, $minstance, $ccontext)
    {
        global $CFG, $DB;

        $this->cmid      = $cmid;
        $this->courseid  = $courseid;
        $this->course    = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance = $minstance;
        $this->host_name = parse_url($CFG->wwwroot, PHP_URL_HOST);
        #
        $this->url_params = array('id'=>$cmid, 'course'=>$courseid);
        $this->action_url = new moodle_url('/mod/lticontainer/actions/jupyterhub_api.php', $this->url_params);
        $this->error_url  = new moodle_url('/mod/lticontainer/actions/view.php',           $this->url_params);

        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/lticontainer:jupyterhub_api',  $this->mcontext)) {
            print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
        }
        $this->ccontext = $ccontext;

        //
        $api_scheme = parse_url($this->minstance->jupyterhub_url, PHP_URL_SCHEME);
        $api_host   = parse_url($this->minstance->jupyterhub_url, PHP_URL_HOST);
        $api_port   = parse_url($this->minstance->jupyterhub_url, PHP_URL_PORT);
        $api_url    = $api_scheme.'://'.$api_host;
        if (!empty($api_port)) $api_url .= ':'.$api_port;
        $this->api_url   = $api_url.'/hub/api';
        $this->api_token = $this->minstance->api_token;
    }


    function  set_condition() 
    {
        return true;
    }


    function  execute()
    {
        global $DB, $USER;

        // course users
        $sql = 'SELECT u.* FROM {role_assignments} r, {user} u WHERE r.contextid = ? AND r.userid = u.id ORDER BY u.username';
        $this->users = $DB->get_records_sql($sql, array($this->ccontext->id));

        // JupyterHub users
        $jh_users = array();
        $json = jupyterhub_api_get($this->api_url, '/users', $this->api_token);
        if (!empty($json)) {
            $jh_users = json_decode($json, false);
        }

        // $this->users に JupyterHub のデータを追加
        foreach ($this->users as $key => $user) {
            $this->users[$key]->jh = new StdClass();
            foreach ($jh_users as $jh_user) {
                if ($user->username == $jh_user->name) {
                    $this->users[$key]->jh = $jh_user;
                    break;
                }
            }
        }

        /*
        $recs = $DB->get_records('lticontainer_data');
        foreach ($recs as $rec) {
            print_r($rec);
            echo '<br />';
            //
            $rec->id = null;
            if ($rec->host=='server') {
                $ret = $DB->insert_record('lticontainer_server_data', $rec);
            }
            else if ($rec->host=='client') {
                $ret = $DB->insert_record('lticontainer_client_data', $rec);
            }
        }
        */

        /*
        $properties = 'filename|codenum';
        $patterns   = "/\"(${properties})\s*:\s*([^\s\"]+)\"/u";

        $recs = $DB->get_records('lticontainer_tags');
        foreach ($recs as $rec) {
            print_r($rec);
            echo '<br />';

            if ($rec->filename==null) {
                preg_match_all($patterns, $rec->tags, $matches, PREG_SET_ORDER);

                foreach($matches as $match) {
                    $rec->{$match[1]} = $match[2];
                }
                $DB->update_record('lticontainer_tags', $rec);
            }
        }
        */

        //
        // POST
        if ($submit_data = data_submitted()) {
            $html = jupyterhub_api_get($this->api_url, '/users', $this->api_token);
            echo $html;
        }

        //
        return true;
    }


    function  print_page() 
    {
        global $CFG, $DB, $OUTPUT;
        
        include(__DIR__.'/../html/jupyterhub_api.html');
    }
}
