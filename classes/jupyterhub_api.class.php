<?php

defined('MOODLE_INTERNAL') || die();


require_once(__DIR__.'/../locallib.php');


class  JupyterHubAPI
{
    var $cmid;
    var $courseid    = 0;
    var $course;
    var $minstance;
    var $mcontext;
    var $ccontext;
    var $host_name   = 'localhost';

    var $submitted   = false;
    var $isGuest     = true;
    var $edit_cap    = false;

    var $sort_params = array();
    var $url_params  = array();
    var $action_url  = '';
    var $error_url   = '';

    var $users       = array();
    var $api_token   = '';
    var $api_url     = '';

    var $status      = 'ALL';
    var $unsort      = 'asc';
    var $tmsort      = 'none';
    var $sort        = 'none';


    function  __construct($cmid, $courseid, $minstance, $ccontext)
    {
        global $CFG, $DB;

        $this->cmid       = $cmid;
        $this->courseid   = $courseid;
        $this->course     = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance  = $minstance;
        $this->host_name  = parse_url($CFG->wwwroot, PHP_URL_HOST);
        #
        $this->url_params = array('id'=>$cmid, 'course'=>$courseid);
        $this->action_url = new moodle_url('/mod/lticontainer/actions/jupyterhub_api.php', $this->url_params);
        $this->error_url  = new moodle_url('/mod/lticontainer/actions/view.php',           $this->url_params);
        $this->ccontext   = $ccontext;

        $this->status = optional_param('status', 'ALL',  PARAM_ALPHA);
        $this->nmsort = optional_param('nmsort', 'asc',  PARAM_ALPHA);
        $this->tmsort = optional_param('tmsort', 'none', PARAM_ALPHA);
        $this->sort   = optional_param('sort',   'none', PARAM_ALPHA);

        $this->sort_params = array('nmsort'=>$this->nmsort, 'tmsort'=>$this->tmsort, 'sort'=>$this->sort);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
        }
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/lticontainer:jupyterhub_api',  $this->mcontext)) {
            print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
        }
        if (has_capability('mod/lticontainer:jupyterhub_api_edit', $this->mcontext)) {
            $this->edit_cap = true;
        }

        //
        $api_url = 'http://localhost:8000';
        if (!empty($this->minstance->jupyterhub_url)) {
            $api_scheme = parse_url($this->minstance->jupyterhub_url, PHP_URL_SCHEME);
            $api_host   = parse_url($this->minstance->jupyterhub_url, PHP_URL_HOST);
            $api_port   = parse_url($this->minstance->jupyterhub_url, PHP_URL_PORT);
            $api_url    = $api_scheme.'://'.$api_host;
            if (!empty($api_port)) $api_url .= ':'.$api_port;
        }

        $this->api_url   = $api_url.'/hub/api';
        $this->api_token = $this->minstance->api_token;
    }


    function  set_condition() 
    {
        if ($this->sort=='nmsort') {
            if ($this->nmsort=='none' or $this->nmsort=='asc')  $this->nmsort = 'desc';
            else                                                $this->nmsort = 'none';
            $this->tmsort = 'none';
        }
        else if ($this->sort=='tmsort') {
            if ($this->tmsort=='none' or $this->tmsort=='desc') $this->tmsort = 'asc';
            else                                                $this->tmsort = 'desc';
        }

        return true;
    }


    function  execute()
    {
        global $DB, $USER;

        // POST
        if ($submit_data = data_submitted()) {
            //$html = jupyterhub_api_get($this->api_url, '/users', $this->api_token);
            //echo $html;
        }

        // course users
        $sql = 'SELECT u.* FROM {role_assignments} r, {user} u WHERE r.contextid = ? AND r.userid = u.id ORDER BY u.username';
        $this->users = $DB->get_records_sql($sql, array($this->ccontext->id));

        // JupyterHub users
        $jh_users = array();
        $json = jupyterhub_api_get($this->api_url, '/users', $this->api_token);

        $jh_users = json_decode($json, false);
        // $this->users に JupyterHub のデータを追加
        foreach ($this->users as $key => $user) {
            $this->users[$key]->jh = new StdClass();
            $this->users[$key]->jh->status = 'NONE';

            if (is_array($jh_users) and !empty($jh_users)) {
                foreach ($jh_users as $jh_user) {
                    if ($user->username == $jh_user->name) {
                        $this->users[$key]->jh = $jh_user;
                        $this->users[$key]->jh->status = 'OK';
                        break;
                    }
                }
            }
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
