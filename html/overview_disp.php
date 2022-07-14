<?php

$disp = 'No';
$make = 'No';
$container = 'Docker';
if ($minstance->custom_params==1) $disp = 'Yes';
if ($minstance->make_volumes ==1) $make = 'Yes';
if ($minstance->use_podman ==1)   $container = 'Podman';

$api_scheme = parse_url($minstance->jupyterhub_url, PHP_URL_SCHEME);
$api_host   = parse_url($minstance->jupyterhub_url, PHP_URL_HOST);
$api_port   = parse_url($minstance->jupyterhub_url, PHP_URL_PORT);
$api_url    = $api_scheme.'://'.$api_host;
if (!empty($api_port)) $api_url .= ':'.$api_port;
$container_host = $api_host;

$params = array('update' => $cmid);
$setup_url = new moodle_url('/course/modedit.php', $params);

echo '<br />'; 
echo '<h4>'; 
print('JupyterHub URL : <strong>'.$api_url.'</strong><br />');
print('Container System : <strong>'.$container.'</strong><br />');
print('Container Host : <strong>'.$container_host.'</strong><br />');
print('Container User : <strong>'.$minstance->docker_user.'</strong><br />');
print('Shows LTI parameters : <strong>'.$disp.'</strong><br />');
print('Image Name Filter : <strong>'.$minstance->imgname_fltr.'</strong><br />');
print('Creates Volumes  : <strong>'.$make.'</strong><br />');

if (has_capability('mod/lticontainer:db_write', $mcontext)) {
    print('<br />');
    print('<strong>');
    print('<a href='.$setup_url.' > '.get_string('Edit_settings', 'mod_lticontainer').' </a><br />');
    print('</strong>');
}
echo '</h4>'; 

