<?php

require_once(__DIR__.'/../locallib.php');

class  LTIConnect
{
    var $cmid;
    var $courseid   = 0;
    var $course;
    var $minstance;
    var $mcontext;

    var $isGuest    = true;

    var $action_url = '';
    var $setup_url  = '';
    var $edit_url   = '';
    var $url_params = array();

    var $items;

    function  __construct($cmid, $courseid, $minstance)
    {
        global $DB;
        
        $this->cmid      = $cmid;
        $this->courseid  = $courseid;
        $this->course    = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $this->minstance = $minstance;

        //$this->url_params = array('id'=>$cmid, 'course'=>$courseid);
        $this->url_params = array('id'=>$cmid);
        $this->action_url = new moodle_url('/mod/mdlds/actions/lti_connect.php', $this->url_params);
        $this->setup_url  = new moodle_url('/mod/mdlds/actions/lti_setting.php', $this->url_params);
        $this->edit_url   = new moodle_url('/mod/mdlds/actions/lti_edit.php', $this->url_params);

        // for Guest
        $this->isGuest = isguestuser();
        if ($this->isGuest) {
            print_error('access_forbidden', 'mod_mdlds', $this->action_url);
        }
        //
        $this->mcontext = context_module::instance($cmid);
        if (!has_capability('mod/mdlds:lti_connect', $this->mcontext)) {
            print_error('access_forbidden', 'mod_mdlds', $this->action_url);
        }
    }


    function  set_condition() 
    {
        $this->order = optional_param('order', '', PARAM_TEXT);

        return true;
    }


    function  execute()
    {
        global $DB;

        // POST
        if ($formdata = data_submitted()) {
            if (!has_capability('mod/mdlds:lti_setting', $this->mcontext)) {
                print_error('access_forbidden', 'mod_mdlds', $this->action_url);
            }
            if (!confirm_sesskey()) {
                print_error('invalid_sesskey', 'mod_mdlds', $this->action_url);
            }
            $this->submitted  = true;

            $this->minstance->no_disp_lti = '';
            if (property_exists($formdata, 'nodisp')) {
                $no_disp = array();
                $nodisps = $formdata->nodisp;
                foreach ($nodisps as $key => $val) {
                    $no_disp[] = $key;
                }
                $no_disp_list = implode(',', $no_disp);
                $event = mdlds_get_event($this->cmid, 'lti_setting', $this->url_params, 'no disp: '.$no_disp_list);
                $event->trigger();
                $this->minstance->no_disp_lti = $no_disp_list;
            }
            $DB->update_record('mdlds', $this->minstance);
        }

        $nodisp = explode(',', $this->minstance->no_disp_lti);
        $sort   = '';
        $fields = 'id,name,instructorcustomparameters';
        $this->items = $DB->get_records('lti', array('course' => $this->courseid), $sort, $fields);

        foreach ($this->items as &$item) {
            $item->disp = 1;
            if (in_array($item->id, $nodisp)) {
                $item->disp = 0;
            }
        }

        return true;
    }


    function  print_page() 
    {
        global $OUTPUT;

        include(__DIR__.'/../html/lti_connect.html');
    }
}
