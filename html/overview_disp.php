<?php

$disp = 'No';
$make = 'No';
$container = 'Docker';
if ($minstance->custom_params==1) $disp = 'Yes';
if ($minstance->make_volumes ==1) $make = 'Yes';
if ($minstance->use_podman ==1)   $container = 'Podman';

$params = array('update' => $cmid);
$setup_url = new moodle_url('/course/modedit.php', $params);

echo '<br />'; 
echo '<h4>'; 
print('Container Host : <strong>'.$minstance->docker_host.'</strong><br />');
print('Conrainer User : <strong>'.$minstance->docker_user.'</strong><br />');
print('Shows LTI parameters : <strong>'.$disp.'</strong><br />');
print('Image Name Filter : <strong>'.$minstance->imgname_fltr.'</strong><br />');
print('Creates volumes  : <strong>'.$make.'</strong><br />');
print('Container System : <strong>'.$container.'</strong><br />');

if (has_capability('mod/lticontainer:db_write', $mcontext)) {
    print('<br />');
    print('<strong>');
    print('<a href='.$setup_url.' > '.get_string('Edit_settings', 'mod_lticontainer').' </a><br />');
    print('</strong>');
}
echo '</h4>'; 

