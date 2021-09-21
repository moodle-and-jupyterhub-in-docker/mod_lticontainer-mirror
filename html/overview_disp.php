<?php

$disp = 'No';
$make = 'No';
$podm = 'no';
if ($minstance->custom_params==1) $disp = 'Yes';
if ($minstance->make_volumes ==1) $make = 'Yes';
if ($minstance->use_podman ==1)   $podm = 'Yes';

$params = array('update' => $cmid);
$setup_url = new moodle_url('/course/modedit.php', $params);

echo '<br />'; 
echo '<h4>'; 
print('Docker Host : <strong>'.$minstance->docker_host.'</strong><br />');
print('Docker User : <strong>'.$minstance->docker_user.'</strong><br />');
print('Shows LTI parameters : <strong>'.$disp.'</strong><br />');
print('Image Name Filter : <strong>'.$minstance->imgname_fltr.'</strong><br />');
print('Creates volumes : <strong>'.$make.'</strong><br />');
print('Use Podman instead of Docker : <strong>'.$podm.'</strong><br />');

if (has_capability('mod/ltids:db_write', $mcontext)) {
    print('<br />');
    print('<a href='.$setup_url.' > '.get_string('Edit_settings', 'mod_ltids').' </a><br />');
}
echo '</h4>'; 

