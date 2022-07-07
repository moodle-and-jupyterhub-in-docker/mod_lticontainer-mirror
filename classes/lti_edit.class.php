<?php

defined('MOODLE_INTERNAL') || die();


require_once(__DIR__.'/../local_lib.php');
require_once(__DIR__.'/../local_dblib.php');


class  LTIEdit
{
    var $cmid;
    var $courseid   = 0;
    var $course;
    var $minstance;
    var $mcontext;
    var $host_name  = 'localhost';

    var $lti_id     = 0;
    var $lti_rec;
    var $images     = array();
    var $options    = array();
    var $lab_urls   = array();
    var $cpu_grnt   = array();
    var $mem_grnt   = array();
    var $cpu_limit  = array();
    var $mem_limut  = array();

    var $imgname_ok = array();
    var $imgname_ng = array();

    var $edit_cap   = false;
    var $submitted  = false;
    var $isGuest    = true;

    var $action_url = '';
    var $error_url  = '';
    var $url_params = array();

    var $custom_ary = array();
    var $custom_txt = '';
    var $costom_prm;


    function  __construct($cmid, $courseid, $minstance)
    {
        global $CFG, $DB;

        $this->cmid      = $cmid;
        $this->courseid  = $courseid;
        $this->course    = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance = $minstance;
        $this->host_name = parse_url($CFG->wwwroot, PHP_URL_HOST);
        #
        $this->lti_id = required_param('lti_id', PARAM_INT);

        $this->url_params = array('id'=>$cmid, 'course'=>$courseid, 'lti_id'=>$this->lti_id);
        $this->action_url = new moodle_url('/mod/lticontainer/actions/lti_edit.php', $this->url_params);
        $this->error_url  = new moodle_url('/mod/lticontainer/actions/lti_view.php', $this->url_params);

        //
        $this->lab_urls   = array('default'=>'', 'Lab'=>'/lab', 'Notebook'=>'/tree');
        //
        $this->cpu_limit  = array('default'=>'', 'no limit'=>'0.0', '0.1'=>'0.1', '0.2'=>'0.2', '0.3'=>'0.3', '0.5'=>'0.5',  
                                                    '1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '7'=>'7', '8'=>'8', '9'=>'9', '10'=>'10');
        $this->mem_limit  = array('default'=>'', 'no limit'=>'0', '200MiB'=>'209,715,200', '300MiB'=> '314,572,800', '500MiB'=> '524,288,000', 
                                                    '1GiB' => '1,073,741,824', '2GiB'=> '2,147,483,648',   '3GiB'=>'3,221,225,472',   '4GiB'=> '4,294,967,296', 
                                                    '5GiB' => '5,368,709,120', '6GiB'=> '6,442,450,944',   '7GiB'=>'7,516,192,768',   '8GiB'=> '8,589,934,592', 
                                                    '9GiB' => '9,663,676,416','10GiB'=>'10,737,418,240',  '12GiB'=>'12,884,901,888', '14GiB'=>'15,032,385,536', 
                                                    '16GiB'=>'17,179,869,184','18GiB'=>'19,327,352,832',  '20GiB'=>'21,474,836,480');
        $this->cpu_grnt   = $this->cpu_limit;
        $this->mem_grnt   = $this->mem_limit;

        // filter
        $this->minstance->imgname_fltr;
        $imgname_fltr = preg_replace("/,/", ' ', trim($this->minstance->imgname_fltr));
        $imgname_fltr = preg_replace("/\s+/", ' ', $imgname_fltr);
        $images_subs  = explode(' ', $imgname_fltr);
        foreach ($images_subs as $img) {
            if (substr($img, 0, 1)=='-') {
                $this->imgname_ng[] = substr($img, 1);
            }
            else {
                $this->imgname_ok[] = $img;
            }
        }

        // option の設定
        $this->options    = array('none'=>'', 'double args'=>'doubleargs');

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
        }
        //
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/lticontainer:lti_view', $this->mcontext)) {
            print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
        }
        if (has_capability('mod/lticontainer:lti_edit', $this->mcontext)) {
            $this->edit_cap = true;
        }

        $this->custom_prm = new stdClass();
        $this->custom_prm->lab_urls  = $this->lab_urls;
        $this->custom_prm->cpu_grnt  = $this->cpu_grnt;
        $this->custom_prm->mem_grnt  = $this->mem_grnt;
        $this->custom_prm->cpu_limit = $this->cpu_limit;
        $this->custom_prm->mem_limit = $this->mem_limit;
        $this->custom_prm->options   = $this->options;
    }


    function  set_condition() 
    {
        $ltis = db_get_disp_ltis($this->courseid, $this->minstance);

        if (!array_key_exists($this->lti_id, $ltis)) {
            print_error('no_ltiid_found', 'mod_lticontainer', $this->error_url);
        }

        return true;
    }


    function  execute()
    {
        global $DB, $USER, $CFG;

        $fields = 'id, course, name, typeid, instructorcustomparameters, launchcontainer, timemodified';
        $this->lti_rec = $DB->get_record('lti', array('id' => $this->lti_id), $fields);
        if (!$this->lti_rec) {
            print_error('no_data_found', 'mod_lticontainer', $this->error_url);
        }
        #
        if ($this->minstance->use_podman==1) {
            if (!file_exists(LTICONTAINER_PODMAN_CMD) and  !file_exists(LTICONTAINER_PODMAN_REMOTE_CMD)) {
                print_error('no_podman_command', 'mod_lticontainer', $this->error_url);
            }
        }
        else {
            if (!file_exists(LTICONTAINER_DOCKER_CMD)) print_error('no_docker_command', 'mod_lticontainer', $this->error_url);
        }
        
        // Launcher Container
        $launch = $this->lti_rec->launchcontainer;
        if ($launch=='1') {     //default
            $ret = $DB->get_record('lti_types_config', array('name'=>'launchcontainer', 'typeid'=>$this->lti_rec->typeid), 'value');
            if ($ret) $launch = $ret->value;
        }

        // POST
        if ($custom_data = data_submitted()) {
            if (!$this->edit_cap) {
                print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
            }
            if (!has_capability('mod/lticontainer:db_write', $this->mcontext)) {
                print_error('access_forbidden', 'mod_lticontainer', $this->error_url);
            }
            if (!confirm_sesskey()) {
                print_error('invalid_sesskey', 'mod_lticontainer', $this->error_url);
            }

            //
            $custom_data->lms_iframe = '0';
            if ($launch=='2' or $launch=='3') $custom_data->lms_iframe = '1';   // 埋め込み
            $custom_data->instanceid = $this->minstance->id;
            $custom_data->lti_id     = $this->lti_id;
            $custom_data->rpc_token  = $this->minstance->rpc_token;
            //
            $server_port = parse_url($CFG->wwwroot, PHP_URL_PORT);
            $scheme      = parse_url($CFG->wwwroot, PHP_URL_SCHEME);
            if ($server_port=='') {
                if ($scheme=="https") $server_port = 443;
                else                  $server_port = 80;
            }
            $custom_data->server_url  = $scheme.':'.$this->host_name.':'.strval($server_port);
            $custom_data->server_path = parse_url($CFG->wwwroot, PHP_URL_PATH);
            //
            $this->submitted  = true;
            $this->custom_txt = lticontainer_join_custom_params($custom_data);
            $this->lti_rec->instructorcustomparameters = $this->custom_txt;
            $this->lti_rec->timemodified = time();
            $DB->update_record('lti', $this->lti_rec);

            // create volume
            if ($this->minstance->make_volumes==1) {
                $i = 0;
                foreach ($custom_data->lms_vol_ as $vol) {
                    if ($custom_data->lms_vol_name[$i]!='' and $vol!=LTICONTAINER_LTI_PRSNALS_CMD) {
                        $lowstr  = mb_strtolower($custom_data->lms_vol_name[$i]);
                        $dirname = preg_replace("/[^a-z0-9]/", '', $lowstr);
                        $cmd = 'volume create '.$vol.$dirname.'_'.$this->courseid.'_'.$this->host_name;
                        container_exec($cmd, $this->minstance);
                    }
                    $i++;
                }
            }
        }

        // サーバ上のイメージの一覧取得
        $rslts = container_exec('images', $this->minstance);
        if (!empty($rslts) and isset($rslts['error'])) {
            print_error($rslts['error'], 'mod_lticontainer', $this->error_url);
        }

        $i = 0;
        foreach ($rslts as $rslt) {
            if ($i==0) $this->images[$i++] = 'default';
            else {
                $rslt  = htmlspecialchars($rslt);
                $rslt  = preg_replace("/\s+/", ' ', trim($rslt));
                $image = explode(' ', $rslt);
                $idisp = $image[0].' : '.$image[1]; // image namne
                if ($image[0]=='&lt;none&gt;' and isset($image[2])) $idisp = $image[2];
                //
                if (check_include_substr_and($idisp, $this->imgname_ok)) {
                    if (!check_include_substr_or($idisp, $this->imgname_ng)) {
                        $this->images[$i++] = $idisp;
                    }
                }
            }
        }

        $this->custom_txt = $this->lti_rec->instructorcustomparameters;
        $this->custom_ary = lticontainer_explode_custom_params($this->custom_txt);
        $this->custom_prm->images = $this->images;

        return true;
    }


    function  print_page() 
    {
        global $CFG, $DB, $OUTPUT;
        
        include(__DIR__.'/../html/lti_edit.html');
    }
}
