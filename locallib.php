<?php

defined('MOODLE_INTERNAL') || die;

define('MDLDS_LTI_PREFIX_COM',  'mdl_');
define('MDLDS_LTI_USER_COM',    'mdl_user');
define('MDLDS_LTI_TEACHER_COM', 'mdl_teacher');
define('MDLDS_LTI_GRPNAME_COM', 'mdl_grpname');
define('MDLDS_LTI_VOLUME_COM',  'mdl_vol_');
define('MDLDS_LTI_SUBMIT_COM',  'mdl_sub_');




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


function mdlds_explode_custom_params($custom_params)
{
    $comms = new stdClass();
    $comms->custom_com = array();
    $comms->other_com  = array();
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

            if (!strncmp(MDLDS_LTI_PREFIX_COM, $com[0], strlen(MDLDS_LTI_PREFIX_COM))) {
                if (!strncmp(MDLDS_LTI_VOLUME_COM, $com[0], strlen(MDLDS_LTI_VOLUME_COM))) {
                    if ($com[1]=='') $com[1] = '.';
                    $vol = explode('_', $com[0]);
                    if (isset($vol[2])) {
                        $actl = explode(':', $com[1]);
                        $comms->mount_vol[$vol[2]] = $actl[0];
                        if (isset($actl[1])) $comms->vol_user[$vol[2]] = $actl[1];
                    }
                }
                else if (!strncmp(MDLDS_LTI_SUBMIT_COM, $com[0], strlen(MDLDS_LTI_SUBMIT_COM))) {
                    if ($com[1]=='') $com[1] = '.';
                    $sub = explode('_', $com[0]);
                    if (isset($sub[2])) {
                        $actl = explode(':', $com[1]);
                        $comms->mount_sub[$sub[2]] = $actl[0];
                        if (isset($actl[1])) $comms->sub_user[$sub[2]] = $actl[1];
                    }
                }
                else {
                    $comms->custom_com[$com[0]] = $com[1];
                }
            }
            else {
                $comms->other_com[$com[0]] = $com[1];
            }
        }
    }

    return $comms;
}




