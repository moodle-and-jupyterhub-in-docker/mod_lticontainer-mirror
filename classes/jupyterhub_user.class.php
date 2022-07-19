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

    var $page_size   = 10;
    var $status      = 'ALL';
    var $unsort      = 'asc';
    var $tmsort      = 'none';
    var $sort        = 'none';

    var $mode        = 'none';


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
        //$this->page_size  = $minstance->chart_bar_usernum;

        $this->userid = optional_param('userid', '',     PARAM_INT);
        $this->status = optional_param('status', 'ALL',  PARAM_ALPHA);
        $this->nmsort = optional_param('nmsort', 'asc',  PARAM_ALPHA);
        $this->tmsort = optional_param('tmsort', 'none', PARAM_ALPHA);
        $this->sort   = optional_param('sort',   'none', PARAM_ALPHA);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
        }
        //
        $this->mcontext = context_module::instance($cmid);
        if (has_capability('mod/lticontainer:jupyterhub_user', $this->mcontext)) {
            $this->mode = 'general';
        }
        else {
            if (empty($this->userid)) $this->userid = $USER->id;
            if ($USER->id == $this->userid) {
                $this->mode = 'personal';
            }
            else {
                print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
            }
        }
        //
        if (has_capability('mod/lticontainer:jupyterhub_user_edit', $this->mcontext) or $this->mode=='personal') {
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
            $this->tmsort = 'none';
        }
        $this->sort_params = array('nmsort'=>$this->nmsort, 'tmsort'=>$this->tmsort, 'sort'=>$this->sort);

        $submit_params = $this->sort_params;
        $submit_params['status'] = $this->status;
        $this->submit_url = new moodle_url($this->action_url, $submit_params);
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
        // get user(s)
        $md_users = array();
        if ($this->mode=='personal' or !empty($this->userid)) {
            $sql = 'SELECT u.* FROM {role_assignments} r, {user} u WHERE r.contextid = ? AND r.userid = u.id AND u.id = '.$this->userid;
            $md_users[0] = $DB->get_record_sql($sql, array($this->ccontext->id));
        }
        else {
            $sql = 'SELECT u.* FROM {role_assignments} r, {user} u WHERE r.contextid = ? AND r.userid = u.id ORDER BY u.username';
            $md_users = $DB->get_records_sql($sql, array($this->ccontext->id));
        }

        // JupyterHub users
        $jh_users = array();
        $json = jupyterhub_api_get($this->api_url, '/users', $this->api_token);

        // $this->users に JupyterHub のデータを追加
        $jh_users = json_decode($json, false);
        foreach ($md_users as $key => $md_user) {
            $md_users[$key]->status = 'NONE';
            if (is_array($jh_users) and !empty($jh_users)) {
                foreach ($jh_users as $jh_user) {
                    if ($md_user->username == $jh_user->name) {
                        $md_users[$key]->status = 'OK';
                        $md_users[$key]->admin  = $jh_user->admin;
                        $md_users[$key]->last_activity = $jh_user->last_activity;
                        break;
                    }
                }
            }
        }

        // 表示用データ(users)の作成
        $i = 0;
        foreach($md_users as $md_user) {
            if ($this->status=='ALL' or $this->status==$md_user->status) {
                $role = 'none';
                $lstact = '';
                $lsttm  = 0;
                $status = $md_user->status;
                if ($status=='OK') {
                    if ($md_user->admin=='1') $role = 'admin';
                    else                      $role = 'user';
                    $lstact = $md_user->last_activity;
                    $lsttm  = strtotime($lstact);
                }
                //
                $this->users[$i]         = $md_user;
                $this->users[$i]->status = $status;
                $this->users[$i]->role   = $role;
                $this->users[$i]->lstact = $lstact;
                $this->users[$i]->lsttm  = $lsttm;
                $i++;
            }
        }

        // Sorting
        if ($this->mode=='general' and empty($this->userid)) {
            if ($this->sort_params['sort']=='nmsort') {
                if ($this->sort_params['nmsort']=='desc') {
                    usort($this->users, function($a, $b) {return $a->username > $b->username ? -1 : 1;});
                }
            }
            else if ($this->sort_params['sort']=='tmsort') {
                if ($this->sort_params['tmsort']=='asc') {
                    usort($this->users, function($a, $b) {return $a->lsttm > $b->lsttm ? -1 : 1;});
                }
                else if ($this->sort_params['tmsort']=='desc') {
                    usort($this->users, function($a, $b) {return $a->lsttm < $b->lsttm ? -1 : 1;});
                }
            }
        }
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
