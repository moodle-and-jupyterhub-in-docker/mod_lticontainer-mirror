<?php

$disp = "No";
if ($minstance->custom_params==1) $disp = 'Yes';

$params = array('update' => $cmid);
$setup_url = new moodle_url('/course/modedit.php', $params);

echo '<br />'; 
echo '<h4>'; 
print('Docker Host : <strong>'.$minstance->docker_host.'</strong><br />');
print('Docker User : <strong>'.$minstance->docker_user.'</strong><br />');
print('Show LTI parameters : <strong>'.$disp.'</strong><br />');

if (has_capability('mod/mdlds:db_write', $mcontext)) {
    print('<br />');
    print('<a href='.$setup_url.' > '.get_string('Edit_settings', 'mod_mdlds').' </a><br />');
}
echo '</h4>'; 

