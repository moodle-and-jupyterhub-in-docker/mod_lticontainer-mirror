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
    var $confirm     = false;

    var $sort_params = array();
    var $url_params  = array();
    var $action_url  = '';
    var $submit_url  = '';
    var $error_url   = '';

    var $api_url     = '';
    var $api_token   = '';
    var $users       = array();


    function  __construct($cmid, $courseid, $minstance, $ccontext)
    {
        global $CFG, $DB, $USER;

        $this->cmid       = $cmid;
        $this->courseid   = $courseid;
        $this->course     = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance  = $minstance;
        $this->host_name  = parse_url($CFG->wwwroot, PHP_URL_HOST);
        #
        $this->url_params = array('id'=>$cmid, 'course'=>$courseid);
        $this->action_url = new moodle_url('/mod/lticontainer/actions/jupyterhub_user.php', $this->url_params);
        $this->error_url  = new moodle_url('/mod/lticontainer/actions/view.php',            $this->url_params);
        $this->ccontext   = $ccontext;

        $this->userid     = optional_param('userid', '', PARAM_INT);
        if (empty($this->userid)) $this->userid = $USER->id;

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
        }
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/lticontainer:jupyterhub_user', $this->mcontext) and $USER->id != $this->userid) {
            print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
        }
        if (has_capability('mod/lticontainer:jupyterhub_user_edit', $this->mcontext) or $USER->id == $this->userid) {
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
        $this->sort_params = array('nmsort'=>'none', 'tmsort'=>'none', 'sort'=>'none');
        $this->submit_url  = $this->action_url;
        //
        return true;
    }


    function  execute()
    {
        global $DB;

        // POST
        if ($submit_data = data_submitted()) {
            //
            if (!$this->edit_cap) {
                print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
            }
            if (!confirm_sesskey()) {
                print_error('invalid_sesskey', 'mod_lticontainer',  $this->error_url);
            }
            $this->submitted  = true;

            //
            if (property_exists($submit_data, 'delete')) {
                $this->deletes = $submit_data->delete;
                if (!empty($this->deletes)) {
                    //
                    // confirm to delete user
                    if (property_exists($submit_data, 'submit_jhuser_del')) {
                        $this->confirm = true;
                    }
                    // delete user
                    else if (property_exists($submit_data, 'submit_jhuser_delete')) {
                        foreach ($this->deletes as $del_user=>$value) {
                            if ($value=='1') {
                                jupyterhub_api_delete($this->api_url, '/users/'.$del_user.'/server', $this->api_token);
                                jupyterhub_api_delete($this->api_url, '/users/'.$del_user, $this->api_token);
                                //
                                $cmd = 'delete user '.$del_user;
                                $event = lticontainer_get_event($this->cmid, 'jupyterhub_user_delete', $this->url_params, $cmd);
                                $event->add_record_snapshot('course', $this->course);
                                $event->add_record_snapshot('lticontainer',  $this->minstance);
                                $event->trigger();
                            }
                        }
                    }
                }
            }
        }

        //
        // get user
        $sql = 'SELECT * FROM {user} u WHERE u.id = '.$this->userid;
        $md_user = $DB->get_record_sql($sql, array($this->ccontext->id));

        // JupyterHub user
        $json = jupyterhub_api_get($this->api_url, '/users/'.$md_user->username, $this->api_token);
        $jh_user = json_decode($json, false);
        if (property_exists($jh_user, 'status')) $jh_user->status = 'NONE';     // ERORR
        else                                     $jh_user->status = 'OK';

        $role   = 'none';
        $lstact = '';
        $status = $jh_user->status;
        if ($status=='OK') {
            if ($jh_user->admin=='1') $role = 'admin';
            else                      $role = 'user';
            $lstact = $jh_user->last_activity;
        }
        //
        $this->users[0]         = $md_user;
        $this->users[0]->status = $status;
        $this->users[0]->role   = $role;
        $this->users[0]->lstact = $lstact;

        //
        return true;
    }


    function  print_page() 
    {
        global $CFG, $DB, $OUTPUT;
        
      if ($this->confirm) {
            include(__DIR__.'/../html/jupyterhub_delete.html');
        }
        else {
            include(__DIR__.'/../html/jupyterhub_user.html');
        }
    }
}
