<?php
/**
 * Use Moodle's Charts API to visualize learning data.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function dashboard_view_selector($cmid, $userdata, $username, $lti_inst_info, $lti_id, $start_date, $end_date)
{
    $usernames = array_keys($userdata);

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
    $lti_inst_select_box = '<select name="lti_inst_select_box">';
    $lti_inst_select_box .= '<option value="*">*</option>'; // All LTI symbol charcter '*'

    foreach($lti_inst_info as $lti_inst) {
        if($lti_inst->id === $lti_id)
            $lti_inst_select_box .= '<option value="'.$lti_inst->id.'" selected>'.$lti_inst->name.'</option>';
        else
            $lti_inst_select_box .= '<option value="'.$lti_inst->id.'">'.$lti_inst->name.'</option>';
    }
    $lti_inst_select_box .= '</select>';

    // フォーム描画
    include('dashboard_view_selector.html');

    return;
}


    
    
//
// チャートを描画
//
function dashboard_view_chart($username, $lti_inst_info, $lti_id, $start_date, $end_date, $charts)
{
    global $OUTPUT;

    echo '<center>';
    echo '<font size="5">';
    echo $start_date.'～'.$end_date.'<br>';
    echo $username;
    echo '&emsp;';
    if($lti_id !== '*')
        echo $lti_inst_info[$lti_id]->name;
    else
        echo $lti_id;
    echo '</font>';
    echo '</center>';
    foreach($charts as $chart)
        echo $OUTPUT->render($chart);
}


function show_dashboard_view_chart($cmid, $userdata, $username, $lti_inst_info, $lti_id, $start_date, $end_date, $charts)
{
    dashboard_view_selector($cmid, $userdata, $username, $lti_inst_info, $lti_id, $start_date, $end_date);
    dashboard_view_chart($username, $lti_inst_info, $lti_id, $start_date, $end_date, $charts);

    return;
}

