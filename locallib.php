<?php

defined('MOODLE_INTERNAL') || die;

define('LTIDS_DOCKER_CMD',          '/usr/bin/docker');
define('LTIDS_CURL_CMD',            '/usr/bin/curl');

define('LTIDS_LTI_PREFIX_CMD',      'lms_');
define('LTIDS_LTI_USERS_CMD',       'lms_users');
define('LTIDS_LTI_TEACHERS_CMD',    'lms_teachers');
define('LTIDS_LTI_TGRPNAME_CMD',    'lms_grpname');
define('LTIDS_LTI_SESSIONINFO_CMD', 'lms_sessioninfo');
define('LTIDS_LTI_IMAGE_CMD',       'lms_image');
define('LTIDS_LTI_CPUGRNT_CMD',     'lms_cpugrnt');
define('LTIDS_LTI_MEMGRNT_CMD',     'lms_memgrnt');
define('LTIDS_LTI_CPULIMIT_CMD',    'lms_cpulimit');
define('LTIDS_LTI_MEMLIMIT_CMD',    'lms_memlimit');
define('LTIDS_LTI_OPTIONS_CMD',     'lms_options');
define('LTIDS_LTI_IFRAME_CMD',      'lms_iframe');
define('LTIDS_LTI_DEFURL_CMD',      'lms_defurl');
define('LTIDS_LTI_VOLUMES_CMD',     'lms_vol_');
define('LTIDS_LTI_SUBMITS_CMD',     'lms_sub_');
define('LTIDS_LTI_PRSNALS_CMD',     'lms_prs_');



//////////////////////////////////////////////////////////////////////////////////////////////

function  pack_space($str)
{
    $str = str_replace(array('　', '\t'), ' ', $str);
    $str = preg_replace("/\s+/", ' ', trim($str));

    return $str;
}


function  check_include_substr($name, $check_strs)
{
    $strs     = preg_replace("/\s+/", '', trim($check_strs));
    $arry_str = explode(",", $strs);
    if (empty($arry_str)) return true;

    foreach ($arry_str as $str) {
        if ($str=='' or $str=='*') return true;
        if (preg_match("/$str/", $name)) return true;
    }
    return false;
}



//////////////////////////////////////////////////////////////////////////////////////////////

function ltids_get_event($cmid, $action, $params='', $info='')
{
    global $CFG;

    $event = null;
    if (!is_array($params)) $params = array();

    $args = array(
        'context' => context_module::instance($cmid),
        'other'   => array('params' => $params, 'info'=> $info),
    );
    //
    if ($action=='over_view') {
        $event = \mod_ltids\event\over_view::create($args);
    }
    else if ($action=='lti_view') {
        $event = \mod_ltids\event\lti_view::create($args);
    }
    else if ($action=='lti_edit') {
        $event = \mod_ltids\event\lti_edit::create($args);
    }
    else if ($action=='lti_setting') {
        $event = \mod_ltids\event\lti_setting::create($args);
    }
    else if ($action=='volume_view') {
        $event = \mod_ltids\event\volume_view::create($args);
    }
    else if ($action=='volume_delete') {
        $event = \mod_ltids\event\volume_delete::create($args);
    }

    return $event;
}
 


function container_socket($mi, $socket_file)
{
    if ($mi->use_podman==1) {
        $socket_params = $mi->docker_host.' '.$mi->docker_user.' '.$mi->docker_pass.' '.$socket_file.' /var/run/podman/podman.sock';
    }
    else {
        $socket_params = $mi->docker_host.' '.$mi->docker_user.' '.$mi->docker_pass.' '.$socket_file;
    }
    $socket_cmd = __DIR__.'/sh/container_rsock.sh '.$socket_params;

    $rslts = array();
    $home_dir = posix_getpwuid(posix_geteuid())['dir'];
    if (!is_writable($home_dir)) {
        $rslts = array('error'=>'web_homedir_forbidden', 'home_dir'=>$home_dir);
        return $rslts;
    }
    exec($socket_cmd);

    return $rslts;
}



function container_exec($cmd, $mi)
{
    $rslts = array();
    $local_container = true;
    $socket_file     = '/tmp/ltids_'.$mi->docker_host.'.sock';

    if ($mi->docker_host=='') {
        return $rslts;
    }
    else if ($mi->docker_host=='localhost' or $mi->docker_host=='127.0.0.1') {
        if ($mi->use_podman==1) {
            $socket_file = '/var/run/podman/podman.sock';
        }
        else {
            $socket_file = '/var/run/docker.sock';
        }
    }
    else {
        $local_container = false;
        if (!file_exists($socket_file)) {
            $rslts = container_socket($mi, $socket_file);
            if (!empty($rslts)) return $rslts;          // error
        }
    }

    if ($mi->use_podman==1) {
        #$container_cmd = LTIDS_CURL_CMD.' --unix-socket '.$socket_file.' http://d/v3.0.0/libpod/'.$cmd;
        if ($cmd=='images') {
            $cmf = 'images';
        }
        $container_cmd = LTIDS_CURL_CMD.' --unix-socket '.$socket_file.' http://d/v3.0.0/libpod/info';
    }
    else {
        $container_cmd = LTIDS_DOCKER_CMD.' -H unix://'.$socket_file.' '.$cmd;
    }
    exec($container_cmd, $rslts);

    if (empty($rslts) and !$local_container) {
        $rslts = container_socket($mi, $socket_file);
        if (!empty($rslts)) return $rslts;              // error
        exec($container_cmd, $rslts);
    }

    return $rslts;
}



//////////////////////////////////////////////////////////////////////////////////////////////
//

// コマンドの分解
function ltids_explode_custom_params($custom_params)
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

            if (!strncmp(LTIDS_LTI_PREFIX_CMD, $cmd[0], strlen(LTIDS_LTI_PREFIX_CMD))) {
                if (!strncmp(LTIDS_LTI_VOLUMES_CMD, $cmd[0], strlen(LTIDS_LTI_VOLUMES_CMD))) {
                    $vol = explode('_', $cmd[0]);
                    if (isset($vol[2])) {
                        $actl = explode(':', $cmd[1]);
                        $cmds->mount_vol[$vol[2]] = $actl[0];
                        if (isset($actl[1])) $cmds->vol_users[$vol[2]] = $actl[1];
                    }
                }
                else if (!strncmp(LTIDS_LTI_SUBMITS_CMD, $cmd[0], strlen(LTIDS_LTI_SUBMITS_CMD))) {
                    $sub = explode('_', $cmd[0]);
                    if (isset($sub[2])) {
                        $actl = explode(':', $cmd[1]);
                        $cmds->mount_sub[$sub[2]] = $actl[0];
                        if (isset($actl[1])) $cmds->sub_users[$sub[2]] = $actl[1];
                    }
                }
                else if (!strncmp(LTIDS_LTI_PRSNALS_CMD, $cmd[0], strlen(LTIDS_LTI_PRSNALS_CMD))) {
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
function ltids_join_custom_params($custom_data)
{
    $custom_params = '';
    if (!isset($custom_data->lms_users))     $custom_data->lms_users    = '';
    if (!isset($custom_data->lms_teachers))  $custom_data->lms_teachers = '';
    if (!isset($custom_data->lms_image))     $custom_data->lms_image    = '';
    if (!isset($custom_data->lms_cpugrnt))   $custom_data->lms_cpugrnt  = '';
    if (!isset($custom_data->lms_memgrnt))   $custom_data->lms_memgrnt  = '';
    if (!isset($custom_data->lms_cpulimit))  $custom_data->lms_cpulimit = '';
    if (!isset($custom_data->lms_memlimit))  $custom_data->lms_memlimit = '';
    if (!isset($custom_data->lms_options))   $custom_data->lms_options  = '';
    if (!isset($custom_data->lms_iframe))    $custom_data->lms_iframe   = '';
    if (!isset($custom_data->lms_defurl))    $custom_data->lms_defurl   = '';
    if ($custom_data->lms_image =='default') $custom_data->lms_image    = '';

    $lowstr = mb_strtolower($custom_data->lms_users);
    $value  = preg_replace("/[^a-z0-9\*, ]/", '', $lowstr);
    $param  = LTIDS_LTI_USERS_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_teachers);
    $value  = preg_replace("/[^a-z0-9\*, ]/", '', $lowstr);
    $param  = LTIDS_LTI_TEACHERS_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_image);
    $value  = preg_replace("/[;$\!\"\'&|\\<>?^%\(\)\{\}\n\r~]/", '', $lowstr);
    $param  = LTIDS_LTI_IMAGE_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_cpulimit);
    $climit = preg_replace("/[^0-9\.]/", '', $lowstr);
    $param  = LTIDS_LTI_CPULIMIT_CMD.'='.$climit;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_memlimit);
    $mlimit = preg_replace("/[^0-9,]/", '', $lowstr);
    $param  = LTIDS_LTI_MEMLIMIT_CMD.'='.$mlimit;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_cpugrnt);
    $value  = preg_replace("/[^0-9\.]/", '', $lowstr);
    if ($climit!='' and $climit!='0.0') {
        if ((float)$climit < (float)$value) $value = $climit;
    }
    $param  = LTIDS_LTI_CPUGRNT_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_memgrnt);
    $value  = preg_replace("/[^0-9,]/", '', $lowstr);
    if ($mlimit!='' and $mlimit!='0') {
        $int_mlimit = (int)preg_replace("/[^0-9]/", '', $mlimit);
        $int_value  = (int)preg_replace("/[^0-9]/", '', $value);
        if ($int_mlimit < $int_value) $value = $mlimit;
    }
    $param  = LTIDS_LTI_MEMGRNT_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    //$lowstr = mb_strtolower($custom_data->lms_options);
    //$value  = preg_replace("/[;$\!\"\'&|\\<>?^%\(\)\{\}\n\r~\/ ]/", '', $lowstr);
    //$param  = LTIDS_LTI_OPTIONS_CMD.'='.$avlue;
    //$custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_defurl);
    $value  = preg_replace("/[^a-z\/]/", '', $lowstr);
    $param  = LTIDS_LTI_DEFURL_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_iframe);
    $value = preg_replace("/[^0-9]/", '', $lowstr);
    $param = LTIDS_LTI_IFRAME_CMD.'='.$value;                               // iframeサポート．ユーザによる操作はなし．
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->instanceid);
    $instid = preg_replace("/[^0-9]/", '', $lowstr);
    $lowstr = mb_strtolower($custom_data->ltiid);
    $ltiid  = preg_replace("/[^0-9]/", '', $lowstr);
    $param  = LTIDS_LTI_SESSIONINFO_CMD.'='.$instid.','.$ltiid;             // Session情報用．ユーザによる操作はなし．
    $custom_params .= $param."\r\n";

    // Volume
    $vol_array = array();
    $i = 0;
    foreach ($custom_data->lms_vol_ as $vol) {
        if ($custom_data->lms_vol_name[$i]!='' and $custom_data->lms_vol_link[$i]!='') {
            $users = '';
            if ($custom_data->lms_vol_users[$i]!='') $users = ':'.$custom_data->lms_vol_users[$i];
            $lowstr  = mb_strtolower($custom_data->lms_vol_name[$i]);
            $dirname = preg_replace("/[^a-z0-9]/", '', $lowstr);
            $vol_array[$vol.$dirname] = $custom_data->lms_vol_link[$i].$users;
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

