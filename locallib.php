<?php

defined('MOODLE_INTERNAL') || die;

define('MDLDS_LTI_PREFIX_CMD',  'mdl_');
define('MDLDS_LTI_USER_CMD',    'mdl_user');
define('MDLDS_LTI_TEACHER_CMD', 'mdl_teacher');
define('MDLDS_LTI_TGRPNAME_CMD','mdl_grpname');
define('MDLDS_LTI_IMAGE_CMD',   'mdl_image');
define('MDLDS_LTI_VOLUME_CMD',  'mdl_vol_');
define('MDLDS_LTI_SUBMIT_CMD',  'mdl_sub_');




//////////////////////////////////////////////////////////////////////////////////////////////


function  pack_space($str)
{
    $str = str_replace(array('　', '\t'), ' ', $str);
    $str = preg_replace("/\s+/", ' ', trim($str));

    return $str;
}



//////////////////////////////////////////////////////////////////////////////////////////////

function mdlds_get_event($cmid, $instanceid, $action, $params='', $info='')
{
    global $CFG;
    //require_once($CFG->dirroot.'/mod/mdlds/jbxl/jbxl_tools.php');
    //require_once($CFG->dirroot.'/mod/mdlds/jbxl/jbxl_moodle_tools.php');

    $event = null;
    if (!is_array($params)) $params = array();

    $args = array(
        'objectid' => $instanceid,
        'context'  => context_module::instance($cmid),
        'other'    => array('params' => $params, 'info'=> $info),
    );
    //
    if ($action=='over_view') {
        $event = \mod_mdlds\event\over_view::create($args);
    }
    else if ($action=='lti_edit') {
        $event = \mod_mdlds\event\lti_edit::create($args);
    }
    //else if ($action=='user_submit') {
    //    $event = \mod_mdlds\event\user_submit::create($args);
    //}
    //else if ($action=='delete') {
    //    $event = \mod_mdlds\event\delete_submit::create($args);
    //}

    return $event;
}
    




//////////////////////////////////////////////////////////////////////////////////////////////
//

function mdlds_explode_custom_params($custom_params)
{
    $comms = new stdClass();
    $comms->custom_cmd = array();
    $comms->other_cmd  = array();
    $comms->mount_vol  = array();
    $comms->mount_sub  = array();
    $comms->vol_user   = array();
    $comms->sub_user   = array();

    $str = str_replace(array("\r\n", "\r", "\n"), "\n", $custom_params);
    $customs = explode("\n", $str);

    foreach ($customs as $custom) {
        if ($custom) {
            $com = explode('=', $custom);
            if (!isset($com[1])) $com[1] = '';

            if (!strncmp(MDLDS_LTI_PREFIX_CMD, $com[0], strlen(MDLDS_LTI_PREFIX_CMD))) {
                if (!strncmp(MDLDS_LTI_VOLUME_CMD, $com[0], strlen(MDLDS_LTI_VOLUME_CMD))) {
                    if ($com[1]=='') $com[1] = '.';
                    $vol = explode('_', $com[0]);
                    if (isset($vol[2])) {
                        $actl = explode(':', $com[1]);
                        $comms->mount_vol[$vol[2]] = $actl[0];
                        if (isset($actl[1])) $comms->vol_user[$vol[2]] = $actl[1];
                    }
                }
                else if (!strncmp(MDLDS_LTI_SUBMIT_CMD, $com[0], strlen(MDLDS_LTI_SUBMIT_CMD))) {
                    if ($com[1]=='') $com[1] = '.';
                    $sub = explode('_', $com[0]);
                    if (isset($sub[2])) {
                        $actl = explode(':', $com[1]);
                        $comms->mount_sub[$sub[2]] = $actl[0];
                        if (isset($actl[1])) $comms->sub_user[$sub[2]] = $actl[1];
                    }
                }
                else {
                    $comms->custom_cmd[$com[0]] = $com[1];
                }
            }
            else {
                $comms->other_cmd[$com[0]] = $com[1];
            }
        }
    }

    return $comms;
}



function mdlds_join_custom_params($formdata)
{
    $custom_params = '';
    if (!isset($formdata->mdl_user))    $formdata->mdl_user    = '';
    if (!isset($formdata->mdl_teacher)) $formdata->mdl_teacher = '';
    if (!isset($formdata->mdl_image))   $formdata->mdl_image   = '';

    $param = MDLDS_LTI_USER_CMD.'='.$formdata->mdl_user;
    $custom_params .= $param."\r\n";
    $param = MDLDS_LTI_TEACHER_CMD.'='.$formdata->mdl_teacher;
    $custom_params .= $param."\r\n";
    $param = MDLDS_LTI_IMAGE_CMD.'='.$formdata->mdl_image;
    $custom_params .= $param."\r\n";

    $i = 0;
    foreach ($formdata->mdl_vol_ as $vol) {
        if ($formdata->mdl_vol_name[$i]!='') {
            if ($vol==MDLDS_LTI_VOLUME_CMD) {
                $user = '';
                if ($formdata->mdl_vol_user[$i]!='') $user = ':'.$formdata->mdl_vol_user[$i];
                $param = MDLDS_LTI_VOLUME_CMD.$formdata->mdl_vol_name[$i].'='.$formdata->mdl_vol_disp[$i].$user;
                $custom_params .= $param."\r\n";
            }
            else if ($vol==MDLDS_LTI_SUBMIT_CMD) {
                $user = '';
                if ($formdata->mdl_vol_user[$i]!='') $user = ':'.$formdata->mdl_vol_user[$i];
                $param = MDLDS_LTI_SUBMIT_CMD.$formdata->mdl_vol_name[$i].'='.$formdata->mdl_vol_disp[$i].$user;
                $custom_params .= $param."\r\n";
            }
        }
        $i++;
    }

    if (isset($formdata->others)) {
        $other_cmds = unserialize($formdata->others);
        foreach ($other_cmds as $cmd=>$value) {
            $param = $cmd.'='.$value;
            $custom_params .= $param."\r\n";
        }
    }
    
    $custom_params = trim($custom_params);
    return $custom_params;
}
