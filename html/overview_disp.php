<?php

$disp = "No";
if ($minstance->custom_params==1) $disp = 'Yes';

$params = array('update' => $cmid);
$setup_url = new moodle_url('/course/modedit.php', $params);

echo '<br />'; 
echo '<h4>'; 
print('Docker Host : '.$minstance->docker_host.'<br />');
print('Docker User : '.$minstance->docker_user.'<br />');
print('Show LTI parameters : '.$disp.'<br />');
print('<a href='.$setup_url.' > '.get_string('Edit_settings', 'mod_mdlds').' </a><br />');
echo '</h4>'; 

