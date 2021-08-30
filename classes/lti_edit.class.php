<?php

require_once(__DIR__.'/../locallib.php');


class  LTIEdit
{
    var $cmid;
    var $courseid   = 0;
    var $course;
    var $minstance;
    var $mcontext;

    var $ltiid      = 0;
    var $ltirec;
    var $images     = array();
    var $options    = array();
    var $lab_urls   = array();

    var $submitted  = false;

    var $isGuest    = true;

    var $action_url = '';
    var $url_params = array();

    var $custom_ary = array();
    var $custom_txt = '';


    function  __construct($cmid, $courseid, $minstance)
    {
        global $CFG, $DB;

        $this->cmid      = $cmid;
        $this->courseid  = $courseid;
        $this->course    = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance = $minstance;
        #
        $this->ltiid = required_param('ltiid', PARAM_INT);

        $this->url_params = array('id'=>$cmid, 'course'=>$courseid, 'ltiid'=>$this->ltiid);
        $this->action_url = new moodle_url('/mod/mdlds/actions/lti_edit.php', $this->url_params);
        $this->lab_urls   = array('default'=>'', 'Lab'=>'/lab', 'Notebook'=>'/tree');
        // option の設定
        $this->options    = array('none'=>'', 'double args'=>'doubleargs');

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mdlds', $this->action_url);
        }
        //
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/mdlds:lti_edit', $this->mcontext)) {
            print_error('access_forbidden', 'mdlds', $this->action_url);
        }
    }


    function  set_condition() 
    {
        return true;
    }


    function  execute()
    {
        global $CFG, $DB;

        $fields = 'id, course, name, instructorcustomparameters, timemodified';
        $this->ltirec = $DB->get_record('lti', array('id' => $this->ltiid), $fields);
        if (!$this->ltirec) {
            print_error('no_data_found', 'mdlds', $this->action_url);
        }

        // POST
        if ($formdata = data_submitted()) {
            if (!has_capability('mod/mdlds:db_write', $this->mcontext)) {
                print_error('access_forbidden', 'mdlds', $this->action_url);
            }
            if (!confirm_sesskey()) {
                print_error('invalid_sesskey', 'mdlds', $this->action_url);
            }
            //
            $this->submitted  = true;
            $this->custom_txt = mdlds_join_custom_params($formdata, $this->minstance->id, $this->ltiid);
            $this->ltirec->instructorcustomparameters = $this->custom_txt;
            $this->ltirec->timemodified = time();
            $DB->update_record('lti', $this->ltirec);

            // create volume
            $i = 0;
            foreach ($formdata->mdl_vol_ as $vol) {
                if ($formdata->mdl_vol_name[$i]!='') {
                    $lowstr  = mb_strtolower($formdata->mdl_vol_name[$i]);
                    $dirname = preg_replace("/[^a-z0-9]/", '', $lowstr);
                    $cmd = 'volume create '.$vol.$dirname.'_'.$this->courseid;
                    docker_exec($cmd, $this->minstance);
                }
                $i++;
            }
        }

        //
        $rslts = docker_exec('images', $this->minstance);
        if (!empty($rslts) and isset($rslts['error'])) {
            print_error($rslts['error'], 'mdlds', $this->action_url, $rslts['home_dir']);
        }

        $i = 0;
        foreach ($rslts as $rslt) {
            if ($i==0) $this->images[$i] = 'default';
            else {
                //$rslt  = preg_replace("/[<>]/", '', $rslt);
                $rslt  = htmlspecialchars ($rslt);
                $rslt  = preg_replace("/\s+/", ' ', trim($rslt));
                $image = explode(' ', $rslt);
                $idisp = $image[0].' : '.$image[1];
                if ($image[0]=='&lt;none&gt;' and isset($image[2])) $idisp = $image[2];
                $this->images[$i] = $idisp;
            }
            $i++;
        }
        $this->custom_txt = $this->ltirec->instructorcustomparameters;
        $this->custom_ary = mdlds_explode_custom_params($this->custom_txt);

        return true;
    }


    function  print_page() 
    {
        global $CFG, $DB, $OUTPUT;
        
        include(__DIR__.'/../html/lti_edit.html');
    }
}
