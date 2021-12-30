<?php
/**
 * Use Moodle's Charts API to visualize learning data.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function chart_view_selector($cmid, $args)
{
    //
    // ユーザ選択用セレクトボックス生成
    $user_select_box  = '<select name="user_select_box">';
    $user_select_box .= '<option value="*">*</option>'; // All user symbol charcter '*'

    $usernames = array_keys($args->usernames);
    foreach($usernames as $uname) {
        if($uname === $args->username)
            $user_select_box .= '<option value="'.$uname.'" selected>'.$uname.'</option>';
        else
            $user_select_box .= '<option value="'.$uname.'">'.$uname.'</option>';
    }
    $user_select_box .= '</select>';

    //
    // LTI選択用セレクトボックス生成
    $lti_select_box  = '<select name="lti_select_box">';
    $lti_select_box .= '<option value="*">*</option>'; // All LTI symbol charcter '*'

    foreach($args->lti_info as $lti) {
        if($lti->id === $args->lti_id)
            $lti_select_box .= '<option value="'.$lti->id.'" selected>'.$lti->name.'</option>';
        else
            $lti_select_box .= '<option value="'.$lti->id.'">'.$lti->name.'</option>';
    }
    $lti_select_box .= '</select>';

    //
    // File選択用セレクトボックス生成
    $file_select_box  = '<select name="file_select_box">';
    $file_select_box .= '<option value="*">*</option>'; 
    //
    $filenames = array_keys($args->filenames);
    foreach($filenames as $fn) {
        if($fn === $args->filename)
            $file_select_box .= '<option value="'.$fn.'" selected>'.$fn.'</option>';
        else
            $file_select_box .= '<option value="'.$fn.'">'.$fn.'</option>';
    }
    $file_select_box .= '</select>';

    //
    // フォーム描画
    include('chart_view_selector.html');

    //
    echo '<h4><strong>';
    echo $args->start_date.' - '.$args->end_date.'<br />';
    echo $args->username;
    echo '&emsp;';
    if($args->lti_id !== '*') echo $args->lti_info[$args->lti_id]->name;
    else                      echo $args->lti_id;
    echo '&emsp;';
    echo $args->filename;
    echo '</strong></h4>';
    echo '<hr />';

    return;
}


    
function show_chart_view($cmid, $args)
{
    global $OUTPUT;

    if ($args->chart_mode=='full') {
        chart_view_selector($cmid, $args);
    }

    // チャートを描画
    echo '<h3><strong>'.$args->chart_title.'</strong></h3>';
    echo '<table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">';
    echo   '<tr><td align="center">';
    //
    if (!empty($args->charts)) {
        foreach ($args->charts as $chart) {
            echo $OUTPUT->render_chart($chart, false);
        }
    }
    else {
        echo '<h3>No Data !</h3>';
    }
    //
    echo   '</td></tr>';
    echo '</table>';

    return;
}

