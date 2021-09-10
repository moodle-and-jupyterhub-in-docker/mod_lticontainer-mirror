<?php

defined('MOODLE_INTERNAL') || die;

define('MDLDS_DOCKER_CMD',          '/usr/bin/docker');

define('MDLDS_LTI_PREFIX_CMD',      'mdl_');
define('MDLDS_LTI_USERS_CMD',       'mdl_users');
define('MDLDS_LTI_TEACHERS_CMD',    'mdl_teachers');
define('MDLDS_LTI_TGRPNAME_CMD',    'mdl_grpname');
define('MDLDS_LTI_SESSIONINFO_CMD', 'mdl_sessioninfo');
define('MDLDS_LTI_IMAGE_CMD',       'mdl_image');
define('MDLDS_LTI_CPULIMIT_CMD',    'mdl_cpulimit');
define('MDLDS_LTI_MEMLIMIT_CMD',    'mdl_memlimit');
define('MDLDS_LTI_OPTION_CMD',      'mdl_option');
define('MDLDS_LTI_IFRAME_CMD',      'mdl_iframe');
define('MDLDS_LTI_DEFURL_CMD',      'mdl_defurl');
define('MDLDS_LTI_VOLUMES_CMD',     'mdl_vol_');
define('MDLDS_LTI_SUBMITS_CMD',     'mdl_sub_');
define('MDLDS_LTI_PRSNALS_CMD',     'mdl_prs_');



//////////////////////////////////////////////////////////////////////////////////////////////

function  pack_space($str)
{
    $str = str_replace(array('　', '\t'), ' ', $str);
    $str = preg_replace("/\s+/", ' ', trim($str));

    return $str;
}



//////////////////////////////////////////////////////////////////////////////////////////////

function mdlds_get_event($cmid, $action, $params='', $info='')
{
    global $CFG;

    $event = null;
    if (!is_array($params)) $params = array();

    $args = array(
        'context'  => context_module::instance($cmid),
        'other'    => array('params' => $params, 'info'=> $info),
    );
    //
    if ($action=='over_view') {
        $event = \mod_mdlds\event\over_view::create($args);
    }
    else if ($action=='lti_view') {
        $event = \mod_mdlds\event\lti_view::create($args);
    }
    else if ($action=='lti_edit') {
        $event = \mod_mdlds\event\lti_edit::create($args);
    }
    else if ($action=='lti_setting') {
        $event = \mod_mdlds\event\lti_setting::create($args);
    }
    else if ($action=='volume_view') {
        $event = \mod_mdlds\event\volume_view::create($args);
    }
    else if ($action=='volume_del') {
        $event = \mod_mdlds\event\volume_del::create($args);
    }

    return $event;
}
 


function docker_socket($mi, $socket_file)
{
    $socket_cmd = __DIR__.'/sh/docker_rsock.sh '.$mi->docker_host.' '.$mi->docker_user.' '.$mi->docker_pass.' '.$socket_file;

    $rslts = array();
    $home_dir = posix_getpwuid(posix_geteuid())['dir'];
    if (!is_writable($home_dir)) {
        $rslts = array('error'=>'web_homedir_forbidden', 'home_dir'=>$home_dir);
        return $rslts;
    }
    exec($socket_cmd);

    return $rslts;
}



function docker_exec($cmd, $mi)
{
    $rslts = array();
    $local_docker = true;
    $socket_file  = '/tmp/mdlds_'.$mi->docker_host.'.sock';

    if ($mi->docker_host=='') {
        return $rslts;
    }
    else if ($mi->docker_host=='localhost' or $mi->docker_host=='127.0.0.1') {
        $socket_file = '/var/run/docker.sock';
    }
    else {
        $local_docker = false;
        if (!file_exists($socket_file)) {
            $rslts = docker_socket($mi, $socket_file);
            if (!empty($rslts)) return $rslts;          // error
        }
    }

    $docker_cmd = MDLDS_DOCKER_CMD.' -H unix://'.$socket_file.' '.$cmd;
    exec($docker_cmd, $rslts);

    if (empty($rslts) and !$local_docker) {
        $rslts = docker_socket($mi, $socket_file);
        if (!empty($rslts)) return $rslts;              // error
        exec($docker_cmd, $rslts);
    }

    return $rslts;
}



//////////////////////////////////////////////////////////////////////////////////////////////
//

// コマンドの分解
function mdlds_explode_custom_params($custom_params)
{
    $cmds = new stdClass();
    $cmds->custom_cmd = array();
    $cmds->other_cmd  = array();
    $cmds->mount_vol  = array();
    $cmds->mount_sub  = array();
    $cmds->mount_prs  = array();
    $cmds->vol_users  = array();
    $cmds->sub_users  = array();
    $cmds->prs_users  = array();

    $str = str_replace(array("\r\n", "\r", "\n"), "\n", $custom_params);
    $customs = explode("\n", $str);

    foreach ($customs as $custom) {
        if ($custom) {
            $cmd = explode('=', $custom);
            if (!isset($cmd[1])) $cmd[1] = '';

            if (!strncmp(MDLDS_LTI_PREFIX_CMD, $cmd[0], strlen(MDLDS_LTI_PREFIX_CMD))) {
                if (!strncmp(MDLDS_LTI_VOLUMES_CMD, $cmd[0], strlen(MDLDS_LTI_VOLUMES_CMD))) {
                    $vol = explode('_', $cmd[0]);
                    if (isset($vol[2])) {
                        $actl = explode(':', $cmd[1]);
                        $cmds->mount_vol[$vol[2]] = $actl[0];
                        if (isset($actl[1])) $cmds->vol_users[$vol[2]] = $actl[1];
                    }
                }
                else if (!strncmp(MDLDS_LTI_SUBMITS_CMD, $cmd[0], strlen(MDLDS_LTI_SUBMITS_CMD))) {
                    $sub = explode('_', $cmd[0]);
                    if (isset($sub[2])) {
                        $actl = explode(':', $cmd[1]);
                        $cmds->mount_sub[$sub[2]] = $actl[0];
                        if (isset($actl[1])) $cmds->sub_users[$sub[2]] = $actl[1];
                    }
                }
                else if (!strncmp(MDLDS_LTI_PRSNALS_CMD, $cmd[0], strlen(MDLDS_LTI_PRSNALS_CMD))) {
                    $prs = explode('_', $cmd[0]);
                    if (isset($prs[2])) {
                        $actl = explode(':', $cmd[1]);
                        $cmds->mount_prs[$prs[2]] = $actl[0];
                        if (isset($actl[1])) $cmds->prs_users[$prs[2]] = $actl[1];
                    }
                }
                else {
                    $cmds->custom_cmd[$cmd[0]] = $cmd[1];
                }
            }
            else {
                $cmds->other_cmd[$cmd[0]] = $cmd[1];
            }
        }
    }

    return $cmds;
}



// コマンドを結合してテキストへ
function mdlds_join_custom_params($custom_data)
{
    $custom_params = '';
    if (!isset($custom_data->mdl_users))     $custom_data->mdl_users    = '';
    if (!isset($custom_data->mdl_teachers))  $custom_data->mdl_teachers = '';
    if (!isset($custom_data->mdl_image))     $custom_data->mdl_image    = '';
    if (!isset($custom_data->mdl_cpulimit))  $custom_data->mdl_cpulimit = '';
    if (!isset($custom_data->mdl_memlimit))  $custom_data->mdl_memlimit = '';
    if (!isset($custom_data->mdl_option))    $custom_data->mdl_option   = '';
    if (!isset($custom_data->mdl_iframe))    $custom_data->mdl_iframe   = '';
    if (!isset($custom_data->mdl_defurl))    $custom_data->mdl_defurl   = '';
    if ($custom_data->mdl_image =='default') $custom_data->mdl_image    = '';

    $lowstr = mb_strtolower($custom_data->mdl_users);
    $value  = preg_replace("/[^a-z0-9\*, ]/", '', $lowstr);
    $param  = MDLDS_LTI_USERS_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->mdl_teachers);
    $value  = preg_replace("/[^a-z0-9\*, ]/", '', $lowstr);
    $param  = MDLDS_LTI_TEACHERS_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->mdl_image);
    $value  = preg_replace("/[;$\!\"\'&|\\<>?^%\(\)\{\}\n\r~]/", '', $lowstr);
    $param  = MDLDS_LTI_IMAGE_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->mdl_cpulimit);
    $value  = preg_replace("/[^0-9\.]/", '', $lowstr);
    $param  = MDLDS_LTI_CPULIMIT_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->mdl_memlimit);
    $value  = preg_replace("/[^0-9KMGTP]/", '', $lowstr);
    $param  = MDLDS_LTI_MEMLIMIT_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    //$lowstr = mb_strtolower($custom_data->mdl_option);
    //$value  = preg_replace("/[;$\!\"\'&|\\<>?^%\(\)\{\}\n\r~\/ ]/", '', $lowstr);
    //$param  = MDLDS_LTI_OPTION_CMD.'='.$avlue;
    //$custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->mdl_defurl);
    $value  = preg_replace("/[^a-z\/]/", '', $lowstr);
    $param  = MDLDS_LTI_DEFURL_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->mdl_iframe);
    $value = preg_replace("/[^0-9]/", '', $lowstr);
    $param = MDLDS_LTI_IFRAME_CMD.'='.$value;                               // iframeサポート．ユーザによる操作はなし．
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->instanceid);
    $instid = preg_replace("/[^0-9]/", '', $lowstr);
    $lowstr = mb_strtolower($custom_data->ltiid);
    $ltiid  = preg_replace("/[^0-9]/", '', $lowstr);
    $param  = MDLDS_LTI_SESSIONINFO_CMD.'='.$instid.','.$ltiid;             // Session情報用．ユーザによる操作はなし．
    $custom_params .= $param."\r\n";

    // Volume
    $vol_array = array();
    $i = 0;
    foreach ($custom_data->mdl_vol_ as $vol) {
        if ($custom_data->mdl_vol_name[$i]!='' and $custom_data->mdl_vol_link[$i]!='') {
            $users = '';
            if ($custom_data->mdl_vol_users[$i]!='') $users = ':'.$custom_data->mdl_vol_users[$i];
            $lowstr  = mb_strtolower($custom_data->mdl_vol_name[$i]);
            $dirname = preg_replace("/[^a-z0-9]/", '', $lowstr);
            $vol_array[$vol.$dirname] = $custom_data->mdl_vol_link[$i].$users;
        }
        $i++;
    }

    foreach ($vol_array as $key=>$value) {
        $custom_params .= $key.'='.$value."\r\n";
    }    

    //
    if (isset($custom_data->others)) {
        $other_cmds = unserialize($custom_data->others);
        foreach ($other_cmds as $cmd=>$value) {
            $param = $cmd.'='.$value;
            $custom_params .= $param."\r\n";
        }
    }
    
    $custom_params = trim($custom_params);
    return $custom_params;
}

