<?php
/**
 * Use Moodle's Charts API to visualize learning data.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function dashboard_view_selector($cmid, $usernames, $username, $lti_info, $lti_id, $start_date, $end_date)
{
    $usernames = array_keys($usernames);

    // ユーザ選択用セレクトボックス生成
    $user_select_box  = '<select name="user_select_box">';
    $user_select_box .= '<option value="*">*</option>'; // All user symbol charcter '*'
    foreach($usernames as $uname) {
        if($uname === $username)
            $user_select_box .= '<option value="'.$uname.'" selected>'.$uname.'</option>';
        else
            $user_select_box .= '<option value="'.$uname.'">'.$uname.'</option>';
    }
    $user_select_box .= '</select>';

    // LTI選択用セレクトボックス生成
    $lti_select_box = '<select name="lti_select_box">';
    $lti_select_box .= '<option value="*">*</option>'; // All LTI symbol charcter '*'

    foreach($lti_info as $lti) {
        if($lti->id === $lti_id)
            $lti_select_box .= '<option value="'.$lti->id.'" selected>'.$lti->name.'</option>';
        else
            $lti_select_box .= '<option value="'.$lti->id.'">'.$lti->name.'</option>';
    }
    $lti_select_box .= '</select>';

    // フォーム描画
    include('dashboard_view_selector.html');

    return;
}


    
    
//
// チャートを描画
//
function dashboard_view_chart($username, $lti_info, $lti_id, $start_date, $end_date, $charts, $titles)
{
    global $OUTPUT;

    echo '<h4><strong>';
      echo $start_date.' - '.$end_date.'<br />';
      echo $username;
      echo '&emsp;';
      if($lti_id !== '*') echo $lti_info[$lti_id]->name;
      else                echo $lti_id;
    echo '</strong></h4>';

    $i = 0;
    foreach($charts as $chart) {
        echo '<hr />';
        echo '<h3><strong>'.$titles[$i++].'</strong></h3>';
        echo '<table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">';
          echo '<tr><td align="center">';
            echo  $OUTPUT->render_chart($chart, false);
          echo '</td></tr>';
        echo '</table>';
    }
    echo '<hr />';
}


function show_dashboard_view_chart($cmid, $usernames, $username, $lti_info, $lti_id, $start_date, $end_date, $charts, $titles)
{
    dashboard_view_selector($cmid, $usernames, $username, $lti_info, $lti_id, $start_date, $end_date);
    dashboard_view_chart($username, $lti_info, $lti_id, $start_date, $end_date, $charts, $titles);

    return;
}

