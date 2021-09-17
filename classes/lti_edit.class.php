<?php

require_once(__DIR__.'/../locallib.php');


class  LTIEdit
{
    var $cmid;
    var $courseid   = 0;
    var $course;
    var $minstance;
    var $mcontext;
    var $host_name  = 'localhost';

    var $ltiid      = 0;
    var $ltirec;
    var $images     = array();
    var $options    = array();
    var $lab_urls   = array();
    var $cpu_limit  = array();
    var $mem_limut  = array();

    var $submitted  = false;

    var $isGuest    = true;

    var $action_url = '';
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
        $this->ltiid = required_param('ltiid', PARAM_INT);

        $this->url_params = array('id'=>$cmid, 'course'=>$courseid, 'ltiid'=>$this->ltiid);
        $this->action_url = new moodle_url('/mod/ltids/actions/lti_edit.php', $this->url_params);
        $this->lab_urls   = array('default'=>'', 'Lab'=>'/lab', 'Notebook'=>'/tree');
        $this->cpu_limit  = array('default'=>'', 'no limit'=>'0', '1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '7'=>'7', 
                                                    '8'=>'8', '9'=>'9', '10'=>'10', '12'=>'12', '14'=>'14', '16'=>'16', '18'=>'18', '20'=>'20');
        $this->mem_limit  = array('default'=>'', 'no limit'=>'0',            '300MiB'=>   '314,572,800', '500MiB'=>  '524,288,000', '800MiB'=>   '838,860,800',
                                                    '1GiB' => '1,073,741,824', '2GiB'=> '2,147,483,648',   '3GiB'=>'3,221,225,472',   '4GiB'=> '4,294,967,296', 
                                                    '5GiB' => '5,368,709,120', '6GiB'=> '6,442,450,944',   '7GiB'=>'7,516,192,768',   '8GiB'=> '8,589,934,592', 
                                                    '9GiB' => '9,663,676,416','10GiB'=>'10,737,418,240',  '12GiB'=>'12,884,901,888', '14GiB'=>'15,032,385,536', 
                                                    '16GiB'=>'17,179,869,184','18GiB'=>'19,327,352,832',  '20GiB'=>'21,474,836,480');
        $this->lab_urls   = array('default'=>'', 'Lab'=>'/lab', 'Notebook'=>'/tree');
        // option の設定
        $this->options    = array('none'=>'', 'double args'=>'doubleargs');

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_ltids', $this->action_url);
        }
        //
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/ltids:lti_edit', $this->mcontext)) {
            print_error('access_forbidden', 'mod_ltids', $this->action_url);
        }

        $this->custom_prm = new stdClass();
        $this->custom_prm->lab_urls  = $this->lab_urls;
        $this->custom_prm->cpu_limit = $this->cpu_limit;
        $this->custom_prm->mem_limit = $this->mem_limit;
        $this->custom_prm->options   = $this->options;
    }


    function  set_condition() 
    {
        return true;
    }


    function  execute()
    {
        global $DB;

        $fields = 'id, course, name, typeid, instructorcustomparameters, launchcontainer, timemodified';
        $this->ltirec = $DB->get_record('lti', array('id' => $this->ltiid), $fields);
        if (!$this->ltirec) {
            print_error('no_data_found', 'mod_ltids', $this->action_url);
        }
        if (!file_exists(LTIDS_DOCKER_CMD)) {
            print_error('no_docker_command', 'mod_ltids', $this->action_url, LTIDS_DOCKER_CMD);
        }
        
        // Launcher Container
        $launch = $this->ltirec->launchcontainer;
        if ($launch=='1') {     //default
            $ret = $DB->get_record('lti_types_config', array('name'=>'launchcontainer', 'typeid'=>$this->ltirec->typeid), 'value');
            if ($ret) $launch = $ret->value;
        }

        // POST
        if ($custom_data = data_submitted()) {
            if (!has_capability('mod/ltids:db_write', $this->mcontext)) {
                print_error('access_forbidden', 'mod_ltids', $this->action_url);
            }
            if (!confirm_sesskey()) {
                print_error('invalid_sesskey', 'mod_ltids', $this->action_url);
            }
            //
            $custom_data->lms_iframe = '0';
            if ($launch=='2' or $launch=='3') $custom_data->lms_iframe = '1';   // 埋め込み
            $custom_data->instanceid = $this->minstance->id;
            $custom_data->ltiid = $this->ltiid;
            //
            $this->submitted  = true;
            $this->custom_txt = ltids_join_custom_params($custom_data);
            $this->ltirec->instructorcustomparameters = $this->custom_txt;
            $this->ltirec->timemodified = time();
            $DB->update_record('lti', $this->ltirec);

            // create volume
            if ($this->minstance->make_volumes==1) {
                $i = 0;
                foreach ($custom_data->lms_vol_ as $vol) {
                    if ($custom_data->lms_vol_name[$i]!='' and $vol!=LTIDS_LTI_PRSNAL_CMD) {
                        $lowstr  = mb_strtolower($custom_data->lms_vol_name[$i]);
                        $dirname = preg_replace("/[^a-z0-9]/", '', $lowstr);
                        $cmd = 'volume create '.$vol.$dirname.'_'.$this->courseid.'_'.$this->host_name;
                        docker_exec($cmd, $this->minstance);
                    }
                    $i++;
                }
            }
        }

        //
        $rslts = docker_exec('images', $this->minstance);
        if (!empty($rslts) and isset($rslts['error'])) {
            print_error($rslts['error'], 'mod_ltids', $this->action_url);
        }

        $i = 0;
        foreach ($rslts as $rslt) {
            if ($i==0) $this->images[$i++] = 'default';
            else {
                $rslt  = htmlspecialchars($rslt);
                $rslt  = preg_replace("/\s+/", ' ', trim($rslt));
                $image = explode(' ', $rslt);
                $idisp = $image[0].' : '.$image[1];
                if ($image[0]=='&lt;none&gt;' and isset($image[2])) $idisp = $image[2];
                if (check_include_substr($idisp, $this->minstance->imgname_fltr)) {
                    $this->images[$i++] = $idisp;
                }
            }
        }
        $this->custom_txt = $this->ltirec->instructorcustomparameters;
        $this->custom_ary = ltids_explode_custom_params($this->custom_txt);
        $this->custom_prm->images = $this->images;

        return true;
    }


    function  print_page() 
    {
        global $CFG, $DB, $OUTPUT;
        
        include(__DIR__.'/../html/lti_edit.html');
    }
}
