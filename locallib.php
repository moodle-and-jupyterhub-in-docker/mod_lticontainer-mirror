<?php

defined('MOODLE_INTERNAL') || die;

define('LTICONTAINER_DOCKER_CMD',          '/usr/bin/docker');
define('LTICONTAINER_PODMAN_CMD',          '/usr/bin/podman');
define('LTICONTAINER_PODMAN_REMOTE_CMD',   '/usr/bin/podman-remote');

define('LTICONTAINER_LTI_PREFIX_CMD',      'lms_');
define('LTICONTAINER_LTI_USERS_CMD',       'lms_users');
define('LTICONTAINER_LTI_TEACHERS_CMD',    'lms_teachers');
define('LTICONTAINER_LTI_IMAGE_CMD',       'lms_image');
define('LTICONTAINER_LTI_CPUGRNT_CMD',     'lms_cpugrnt');
define('LTICONTAINER_LTI_MEMGRNT_CMD',     'lms_memgrnt');
define('LTICONTAINER_LTI_CPULIMIT_CMD',    'lms_cpulimit');
define('LTICONTAINER_LTI_MEMLIMIT_CMD',    'lms_memlimit');
define('LTICONTAINER_LTI_OPTIONS_CMD',     'lms_options');
define('LTICONTAINER_LTI_IFRAME_CMD',      'lms_iframe');
define('LTICONTAINER_LTI_DEFURL_CMD',      'lms_defurl');
define('LTICONTAINER_LTI_VOLUMES_CMD',     'lms_vol_');
define('LTICONTAINER_LTI_SUBMITS_CMD',     'lms_sub_');
define('LTICONTAINER_LTI_PRSNALS_CMD',     'lms_prs_');

define('LTICONTAINER_LTI_SESSIONINFO_CMD', 'lms_sessioninfo');
define('LTICONTAINER_LTI_RPCTOKEN_CMD',    'lms_rpctoken');
define('LTICONTAINER_LTI_SERVERURL_CMD' ,  'lms_serverurl');
define('LTICONTAINER_LTI_SERVERPATH_CMD' , 'lms_serverpath');


//////////////////////////////////////////////////////////////////////////////////////////////
/*
function  timezone_offset()
function  get_tz_date_str($date, $format=PHP_DATETIME_FMT)
function  passed_time($tm)

function  pack_space($str)
function  check_include_substr_and($name, $array_str)
function  check_include_substr_or($name, $array_str)

function  autoset_jupyterhub_url($courseid, $mi)
function  jupyterhub_api_get($url, $com, $token)
//function  jupyterhub_api_post($url, $com, $token)
//function  jupyterhub_api_put($url, $com, $token)
//function  jupyterhub_api_delete($url, $com, $token)

function  container_socket($mi, $socket_file)
function  container_exec($cmd, $mi)

function  lticontainer_get_event($cmid, $action, $params='', $info='')
function  lticontainer_explode_custom_params($custom_params)
function  lticontainer_join_custom_params($custom_data)
*/


//////////////////////////////////////////////////////////////////////////////////////////////

function  timezone_offset()
{
    global $TIME_OFFSET, $CFG;

    $TIME_OFFSET = 0;
    if (property_exists($CFG, 'timezone') and !empty($CFG->timezone)) {
        $tz = new DateTime('now', new DateTimeZone($CFG->timezone));
        $TIME_OFFSET = $tz->getOffset();
    }

    return $TIME_OFFSET;
}


function  get_tz_date_str($date, $format=PHP_DATETIME_FMT)
{
    global $TIME_OFFSET, $CFG;

    if ($TIME_OFFSET==0) timezone_offset();

    $date = preg_replace('/[TZ]/', ' ', $date);
    $ut   = (new DateTime($date))->format('U') + $TIME_OFFSET;
    $dt   = date($format, $ut);

    return $dt;
}


function  passed_time($tm)
{
    $ret = get_string('never_ago', 'mod_lticontainer');
    //
    if (!empty($tm)) {
        $pass = time() - strtotime($tm);
        if ($pass<60) $ret = strval($pass).' '.get_string('seconds_ago', 'mod_lticontainer');
        else {
            $pass = intdiv($pass, 60);
            if ($pass<60) $ret = strval($pass).' '.get_string('minutes_ago', 'mod_lticontainer');
            else {
                $pass = intdiv($pass, 60);
                if ($pass<24) $ret = strval($pass).' '.get_string('hours_ago', 'mod_lticontainer');
                else {
                    $pass = intdiv($pass, 24);
                    $ret = strval($pass).' '.get_string('days_ago', 'mod_lticontainer');
                }
            }
        }
    }

    return $ret;
}



//////////////////////////////////////////////////////////////////////////////////////////////
//
function  pack_space($str)
{
    $str = str_replace(array('　', '\t'), ' ', $str);
    $str = preg_replace("/\s+/", ' ', trim($str));

    return $str;
}


function  check_include_substr_and($name, $array_str)
{
    foreach ($array_str as $str) {
        if ($str=='' or $str=='*') return true;
        if (!preg_match("/$str/", $name)) return false;
    }
    return true;
}


function  check_include_substr_or($name, $array_str)
{
    foreach ($array_str as $str) {
        if ($str=='' or $str=='*') return true;
        if (preg_match("/$str/", $name)) return true;
    }
    return false;
}


function  get_namehead($name_pattern, $firstname, $lastname, $deli='')
{
    global $CFG;

    if ($name_pattern=='fullname') {
        if ($CFG->fullnamedisplay=='lastname firstname') { // for better view (dlnsk)
            if ($deli=='') $namehead = "$lastname $firstname";
            else           $namehead = "$lastname ".$deli." $firstname";
        }
        else {
            if ($deli=='') $namehead = "$firstname $lastname";
            else           $namehead = "$firstname ".$deli." $lastname";
        }
    }
    else if ($name_pattern=='lastname') {
        $namehead = "$lastname";
    }
    else {
        $namehead = "$firstname";
    }

    return $namehead;
}



//////////////////////////////////////////////////////////////////////////////////////////////
//

function  select_jupyterhub_status($url, $url_options, $selected)
{
    global $OUTPUT;

    if (is_array($url_options)) $popupurl = new moodle_url($url, $url_options);
    else                        $popupurl = $url.$url_options;
    //
    $options = array();
    $options['ALL']  = 'ALL';
    $options['OK']   = 'OK';
    $options['NONE'] = 'NONE';
    //
    echo $OUTPUT->single_select($popupurl, 'status', $options, $selected);

    return;
}



// update jupyterhub_url using lti_types
function  autoset_jupyterhub_url($courseid, $mi)
{
    global $DB;

    if (empty($mi->jupyterhub_url)) {
        $ltis = db_get_disp_ltis($courseid, $mi);
        if (is_array($ltis) && !empty($ltis)) {
            $typeid = current($ltis)->typeid;
            $lti_type = $DB->get_record('lti_types', array('id' => $typeid), 'id,baseurl', MUST_EXIST);
            if (is_object($lti_type)) {
                $scheme = parse_url($lti_type->baseurl, PHP_URL_SCHEME);
                $host   = parse_url($lti_type->baseurl, PHP_URL_HOST);
                $port   = parse_url($lti_type->baseurl, PHP_URL_PORT);
                $url = $scheme.'://'.$host;
                if (!empty($port)) $url .= ':'.$port;
                $mi->jupyterhub_url = $url;
                $DB->update_record('lticontainer', $mi);
            }
        }
    }

    return;
}


// used GET cURL
function  jupyterhub_api_get($url, $com, $token)
{
    $headers = array('Authorization: token '.$token,);

    $curl = curl_init($url.$com);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $html = curl_exec($curl);
    curl_close($curl);

    return $html;
}



//////////////////////////////////////////////////////////////////////////////////////////////
//

function  container_socket($mi, $socket_file)
{
    $container_host = parse_url($mi->jupyterhub_url, PHP_URL_HOST);

    if ($mi->use_podman==1) {
        //$socket_params = $mi->docker_host.' '.$mi->docker_user.' '.$mi->docker_pass.' '.$socket_file.' /var/run/podman/podman.sock';
        $socket_params = $container_host.' '.$mi->docker_user.' '.$mi->docker_pass.' '.$socket_file.' /var/run/podman/podman.sock';
    }
    else {
        //$socket_params = $mi->docker_host.' '.$mi->docker_user.' '.$mi->docker_pass.' '.$socket_file;
        $socket_params = $container_host.' '.$mi->docker_user.' '.$mi->docker_pass.' '.$socket_file;
    }
    $socket_cmd = __DIR__.'/sh/container_rsock.sh '.$socket_params;

    $rslts = array();
    $home_dir = posix_getpwuid(posix_geteuid())['dir'];
    if (!is_writable($home_dir)) {
        $rslts = array('error'=>'web_homedir_forbidden', 'home_dir'=>$home_dir);
        return $rslts;
    }
    exec($socket_cmd, $rslts);

    return $rslts;
}



function  container_exec($cmd, $mi)
{
    $rslts = array();
    $container_host = parse_url($mi->jupyterhub_url, PHP_URL_HOST);
    //
    //$socket_file = '/tmp/lticontainer_'.$mi->docker_host.'.sock';
    $socket_file = '/tmp/lticontainer_'.$container_host.'.sock';

    //if ($mi->docker_host=='') {
    if ($container_host=='') {
        return $rslts;
    }
    else {
        if (!file_exists($socket_file)) {
            $rslts = container_socket($mi, $socket_file);
            if (!empty($rslts)) return $rslts;          // error
        }
    }

    $container_cmd = null;
    if ($mi->use_podman==1) {
        if (file_exists(LTICONTAINER_PODMAN_REMOTE_CMD)) {
            $container_cmd = LTICONTAINER_PODMAN_REMOTE_CMD.' --url unix://'.$socket_file.' '.$cmd;
        }
        else {
            if (file_exists(LTICONTAINER_PODMAN_CMD)) {
                $container_cmd = LTICONTAINER_PODMAN_CMD.' --remote --url unix://'.$socket_file.' '.$cmd;
            }
        }
    }
    else {
        if (file_exists(LTICONTAINER_DOCKER_CMD)) {
            $container_cmd = LTICONTAINER_DOCKER_CMD.' -H unix://'.$socket_file.' '.$cmd;
        }
    }

    if (!empty($container_cmd)) {
        //echo $container_cmd;
        exec($container_cmd, $rslts);

        // retry
        if (empty($rslts)) {
            $rslts = container_socket($mi, $socket_file);
            if (!empty($rslts)) return $rslts;              // error
            exec($container_cmd, $rslts);
        }
    }

    return $rslts;
}



//////////////////////////////////////////////////////////////////////////////////////////////
//

function  lticontainer_get_event($cmid, $action, $params='', $info='')
{
    global $CFG;

    $event = null;
    if (!is_array($params)) $params = array();

    $args = array(
        'context' => context_module::instance($cmid),
        'other'   => array('params' => $params, 'info'=> $info),
    );
    //
    if      ($action=='over_view') {
        $event = \mod_lticontainer\event\over_view::create($args);
    }
    else if ($action=='lti_view') {
        $event = \mod_lticontainer\event\lti_view::create($args);
    }
    else if ($action=='lti_setting') {
        $event = \mod_lticontainer\event\lti_setting::create($args);
    }
    else if ($action=='lti_edit') {
        $event = \mod_lticontainer\event\lti_edit::create($args);
    }
    else if ($action=='volume_view') {
        $event = \mod_lticontainer\event\volume_view::create($args);
    }
    else if ($action=='volume_delete') {
        $event = \mod_lticontainer\event\volume_delete::create($args);
    }
    else if ($action=='dashboard_view') {
        $event = \mod_lticontainer\event\dashboard_view::create($args);
    }
    else if ($action=='chart_view') {
        $event = \mod_lticontainer\event\chart_view::create($args);
    }
    else if ($action=='jupyterhub_api') {
        $event = \mod_lticontainer\event\jupyterhub_api::create($args);
    }
    else if ($action=='admin_tools') {
        $event = \mod_lticontainer\event\admin_tools::create($args);
    }

    return $event;
}
 

// コマンドの分解
function  lticontainer_explode_custom_params($custom_params)
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

            if (!strncmp(LTICONTAINER_LTI_PREFIX_CMD, $cmd[0], strlen(LTICONTAINER_LTI_PREFIX_CMD))) {
                if (!strncmp(LTICONTAINER_LTI_VOLUMES_CMD, $cmd[0], strlen(LTICONTAINER_LTI_VOLUMES_CMD))) {
                    $vol = explode('_', $cmd[0]);
                    if (isset($vol[2])) {
                        $actl = explode(':', $cmd[1]);
                        $cmds->mount_vol[$vol[2]] = $actl[0];
                        if (isset($actl[1])) $cmds->vol_users[$vol[2]] = $actl[1];
                    }
                }
                else if (!strncmp(LTICONTAINER_LTI_SUBMITS_CMD, $cmd[0], strlen(LTICONTAINER_LTI_SUBMITS_CMD))) {
                    $sub = explode('_', $cmd[0]);
                    if (isset($sub[2])) {
                        $actl = explode(':', $cmd[1]);
                        $cmds->mount_sub[$sub[2]] = $actl[0];
                        if (isset($actl[1])) $cmds->sub_users[$sub[2]] = $actl[1];
                    }
                }
                else if (!strncmp(LTICONTAINER_LTI_PRSNALS_CMD, $cmd[0], strlen(LTICONTAINER_LTI_PRSNALS_CMD))) {
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
function  lticontainer_join_custom_params($custom_data)
{
    $custom_params = '';
    if (!isset($custom_data->lms_users))       $custom_data->lms_users       = '';
    if (!isset($custom_data->lms_teachers))    $custom_data->lms_teachers    = '';
    if (!isset($custom_data->lms_image))       $custom_data->lms_image       = '';
    if (!isset($custom_data->lms_cpulimit))    $custom_data->lms_cpulimit    = '';
    if (!isset($custom_data->lms_memlimit))    $custom_data->lms_memlimit    = '';
    if (!isset($custom_data->lms_cpugrnt))     $custom_data->lms_cpugrnt     = '';
    if (!isset($custom_data->lms_memgrnt))     $custom_data->lms_memgrnt     = '';
    if (!isset($custom_data->lms_defurl))      $custom_data->lms_defurl      = '';
    if (!isset($custom_data->lms_iframe))      $custom_data->lms_iframe      = '';
    if (!isset($custom_data->lms_sessioninfo)) $custom_data->lms_sessioninfo = '';
    if (!isset($custom_data->lms_rpctoken))    $custom_data->lms_rpctoken    = '';
    if (!isset($custom_data->lms_options))     $custom_data->lms_options     = '';
    if ($custom_data->lms_image =='default')   $custom_data->lms_image       = '';

    $lowstr = mb_strtolower($custom_data->lms_users);
    $value  = preg_replace("/[^a-z0-9\-\_\*, ]/", '', $lowstr);
    $param  = LTICONTAINER_LTI_USERS_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_teachers);
    $value  = preg_replace("/[^a-z0-9\*, ]/", '', $lowstr);
    $param  = LTICONTAINER_LTI_TEACHERS_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_image);
    $value  = preg_replace("/[;$\!\"\'&|\\<>?^%\(\)\{\}\n\r~]/", '', $lowstr);
    $param  = LTICONTAINER_LTI_IMAGE_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_cpulimit);
    $climit = preg_replace("/[^0-9\.]/", '', $lowstr);
    $param  = LTICONTAINER_LTI_CPULIMIT_CMD.'='.$climit;
    $custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_memlimit);
    $mlimit = preg_replace("/[^0-9,]/", '', $lowstr);
    $param  = LTICONTAINER_LTI_MEMLIMIT_CMD.'='.$mlimit;
    $custom_params .= $param."\r\n";

    //$lowstr = mb_strtolower($custom_data->lms_cpugrnt);
    //$value  = preg_replace("/[^0-9\.]/", '', $lowstr);
    //if ($climit!='' and $climit!='0.0') {
    //    if ((float)$climit < (float)$value) $value = $climit;
    //}
    //$param  = LTICONTAINER_LTI_CPUGRNT_CMD.'='.$value;
    //$custom_params .= $param."\r\n";

    //$lowstr = mb_strtolower($custom_data->lms_memgrnt);
    //$value  = preg_replace("/[^0-9,]/", '', $lowstr);
    //if ($mlimit!='' and $mlimit!='0') {
    //    $int_mlimit = (int)preg_replace("/[^0-9]/", '', $mlimit);
    //    $int_value  = (int)preg_replace("/[^0-9]/", '', $value);
    //    if ($int_mlimit < $int_value) $value = $mlimit;
    //}
    //$param  = LTICONTAINER_LTI_MEMGRNT_CMD.'='.$value;
    //$custom_params .= $param."\r\n";

    $lowstr = mb_strtolower($custom_data->lms_defurl);
    $value  = preg_replace("/[^a-z\/]/", '', $lowstr);
    $param  = LTICONTAINER_LTI_DEFURL_CMD.'='.$value;
    $custom_params .= $param."\r\n";

    //$lowstr = mb_strtolower($custom_data->lms_options);
    //$value  = preg_replace("/[;$\!\"\'&|\\<>?^%\(\)\{\}\n\r~\/ ]/", '', $lowstr);
    //$param  = LTICONTAINER_LTI_OPTIONS_CMD.'='.$avlue;
    //$custom_params .= $param."\r\n";

    // Volume
    $vol_array = array();
    $i = 0;
    foreach ($custom_data->lms_vol_ as $vol) {
        if ($custom_data->lms_vol_name[$i]!='' and $custom_data->lms_vol_link[$i]!='') {
            $users = '';
            if ($custom_data->lms_vol_users[$i]!='') $users = ':'.$custom_data->lms_vol_users[$i];
            $lowstr   = mb_strtolower($custom_data->lms_vol_name[$i]);
            $dirname  = preg_replace("/[^a-z0-9]/", '', $lowstr);
            $linkname = preg_replace("/[*;: $\!\"\'&+=|\\<>?^%~\`\(\)\{\}\[\]\n\r]/", '', $custom_data->lms_vol_link[$i]);
            $vol_array[$vol.$dirname] = $linkname.$users;
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
    
    // automatically set
    $lowstr = mb_strtolower($custom_data->lms_iframe);
    $value = preg_replace("/[^0-9]/", '', $lowstr);
    $param = LTICONTAINER_LTI_IFRAME_CMD.'='.$value;                                // iframeサポート．ユーザによる操作はなし．
    $custom_params .= $param."\r\n";

    $lowstr  = mb_strtolower($custom_data->instanceid);                                 // lticontainer のインスタンスの ID 
    $inst_id = preg_replace("/[^0-9]/", '', $lowstr);
    $lowstr  = mb_strtolower($custom_data->lti_id);                                     // lti のインスタンスの ID
    $lti_id  = preg_replace("/[^0-9]/", '', $lowstr);
    $param   = LTICONTAINER_LTI_SESSIONINFO_CMD.'='.$inst_id.','.$lti_id;           // Session情報用．ユーザによる操作はなし．
    $custom_params .= $param."\r\n";

    $lowstr  = mb_strtolower($custom_data->rpc_token);
    $value   = preg_replace("/[^0-9a-f]/", '', $lowstr);
    $param   = LTICONTAINER_LTI_RPCTOKEN_CMD.'='.$value;                            // Web services用 RPC Token. ユーザによる操作はなし．
    $custom_params .= $param."\r\n";

    $lowstr  = mb_strtolower($custom_data->server_url);
    $value   = preg_replace("/[_;$\!\"\'&|\\<>?^%\(\)\{\}\n\r~]/", '', $lowstr);
    $param   = LTICONTAINER_LTI_SERVERURL_CMD.'='.$value;                           // Server URL. ユーザによる操作はなし．
    $custom_params .= $param."\r\n";

    $value   = preg_replace("/[;$\!\"\'&|\\<>?^%\(\)\{\}\n\r~]/", '', $custom_data->server_path);
    $param   = LTICONTAINER_LTI_SERVERPATH_CMD.'='.$value;                          // Server URL. ユーザによる操作はなし．
    $custom_params .= $param."\r\n";

    $custom_params = trim($custom_params);

    return $custom_params;
}

