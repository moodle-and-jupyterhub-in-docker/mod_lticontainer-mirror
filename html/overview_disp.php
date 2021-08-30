<?php

$disp = 'No';
$make = 'No';
if ($minstance->custom_params==1) $disp = 'Yes';
if ($minstance->make_volumes ==1) $make = 'Yes';

$params = array('update' => $cmid);
$setup_url = new moodle_url('/course/modedit.php', $params);

echo '<br />'; 
echo '<h4>'; 
print('Docker Host : <strong>'.$minstance->docker_host.'</strong><br />');
print('Docker User : <strong>'.$minstance->docker_user.'</strong><br />');
print('Shows LTI parameters : <strong>'.$disp.'</strong><br />');
print('Creates volumes : <strong>'.$make.'</strong><br />');

if (has_capability('mod/mdlds:db_write', $mcontext)) {
    print('<br />');
    print('<a href='.$setup_url.' > '.get_string('Edit_settings', 'mod_mdlds').' </a><br />');
}
echo '</h4>'; 

