<?php

function show_lti_edit_table_cmd($cmds, $images)
{
    $table = new html_table();
    //
    $table->head [] = 'Command';
    $table->align[] = 'left';
    $table->size [] = '100px';
    $table->wrap [] = 'nowrap';

    $table->head [] = 'Users/Image Name';
    $table->align[] = 'left';
    $table->size [] = '300px';
    $table->wrap [] = 'nowrap';

    $table->head [] = '';
    $table->align[] = '';
    $table->size [] = '0';
    $table->wrap [] = '';

    $table->head [] = '';
    $table->align[] = '';
    $table->size [] = '0';
    $table->wrap [] = '';

    //
    $i = 0;
    $user_cmd = '';
    $teacher_cmd = '';
    $image_cmd = '';
    if (isset($cmds->custom_cmd[MDLDS_LTI_USER_CMD]))    $user_cmd    = $cmds->custom_cmd[MDLDS_LTI_USER_CMD];
    if (isset($cmds->custom_cmd[MDLDS_LTI_TEACHER_CMD])) $teacher_cmd = $cmds->custom_cmd[MDLDS_LTI_TEACHER_CMD];
    if (isset($cmds->custom_cmd[MDLDS_LTI_IMAGE_CMD]))   $image_cmd   = $cmds->custom_cmd[MDLDS_LTI_IMAGE_CMD];

    // MDLDS_LTI_USER_CMD
    $table->data[$i][] = '<strong>'.get_string('user_cmd_ttl', 'mdlds').'</strong>';
    $table->data[$i][] = '<input type="text" name="'.MDLDS_LTI_USER_CMD.'" size="50" maxlength="200" value="'.$user_cmd.'" />';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $i++;

    // MDLDS_LTI_TEACHER_CMD
    $table->data[$i][] = '<strong>'.get_string('teacher_cmd_ttl', 'mdlds').'</strong>';
    $table->data[$i][] = '<input type="text" name="'.MDLDS_LTI_TEACHER_CMD.'" size="50" maxlength="200" value="'.$teacher_cmd.'" />';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $i++;


    // MDLDS_LTI_IMAGE_CMD
    $select_opt = '';
    foreach($images as $image) {
        $selected = '';
        if ($image==$image_cmd) $selected = 'selected="selected"';
        $select_opt .= '<option value="'.$image.'" '.$selected.'>'.$image.'</option>';
    }
    $table->data[$i][] = '<strong>'.get_string('image_cmd_ttl', 'mdlds').'</strong>';
    $table->data[$i][] = '<select name="'.MDLDS_LTI_IMAGE_CMD.'" >'.$select_opt.'</select>';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $i++;

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}




function show_lti_edit_table_vol($cmds)
{
    $table = new html_table();
    //
    $table->head [] = 'Command';
    $table->align[] = 'left';
    $table->size [] = '100px';
    $table->wrap [] = 'nowrap';

    $table->head [] = 'Volume Name';
    $table->align[] = 'left';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    $table->head [] = 'Display Name';
    $table->align[] = 'left';
    $table->size [] = '150px';
    $table->wrap [] = 'nowrap';

    $table->head [] = 'Accessible Users';
    $table->align[] = 'left';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    //
    $i = 0;
    // Mount Volumes
    if (isset($cmds->mount_vol)) {
        $select_opt  = '<option value="'.MDLDS_LTI_VOLUME_CMD.'" selected="selected" />'.get_string('vol_cmd_ttl', 'mdlds').'</option>';
        $select_opt .= '<option value="'.MDLDS_LTI_SUBMIT_CMD.'" />'.get_string('sub_cmd_ttl', 'mdlds').'</option>';
        foreach($cmds->mount_vol as $key => $value) { 
            if (!isset($cmds->vol_user[$key])) $cmds->vol_user[$key] = '';
            $table->data[$i][] = '<select name="'.MDLDS_LTI_VOLUME_CMD.'[]" autocomplete="off">'.$select_opt.'</select>'; 
            $table->data[$i][] = '<input type="text" name="'.MDLDS_LTI_VOLUME_CMD.'name[]" size="15" maxlength="30"  value="'.$key.'" />';
            $table->data[$i][] = '<input type="text" name="'.MDLDS_LTI_VOLUME_CMD.'disp[]" size="30" maxlength="60"  value="'.$value.'" />';
            $table->data[$i][] = '<input type="text" name="'.MDLDS_LTI_VOLUME_CMD.'user[]" size="50" maxlength="200" value="'.$cmds->vol_user[$key].'" />';
            $i++;
        }
    }

    // Submit Volumes
    if (isset($cmds->mount_sub)) {
        $select_opt  = '<option value="'.MDLDS_LTI_VOLUME_CMD.'" />'.get_string('vol_cmd_ttl', 'mdlds').'</option>';
        $select_opt .= '<option value="'.MDLDS_LTI_SUBMIT_CMD.'" selected="selected" />'.get_string('sub_cmd_ttl', 'mdlds').'</option>';
        foreach($cmds->mount_sub as $key => $value) { 
            if (!isset($cmds->sub_user[$key])) $cmds->sub_user[$key] = '';
            $table->data[$i][] = '<select name="'.MDLDS_LTI_VOLUME_CMD.'[]" autocomplete="off">'.$select_opt.'</select>'; 
            $table->data[$i][] = '<input type="text" name="'.MDLDS_LTI_VOLUME_CMD.'name[]" size="15" maxlength="30"  value="'.$key.'" />';
            $table->data[$i][] = '<input type="text" name="'.MDLDS_LTI_VOLUME_CMD.'disp[]" size="30" maxlength="60"  value="'.$value.'" />';
            $table->data[$i][] = '<input type="text" name="'.MDLDS_LTI_VOLUME_CMD.'user[]" size="50" maxlength="200" value="'.$cmds->sub_user[$key].'" />';
            $i++;
        }
    }

    $table->data[$i][] = '<strong>New Volumes</strong>'; 
    $i++;

    // New Volumes
    $num = 3;
    $select_opt  = '<option value="'.MDLDS_LTI_VOLUME_CMD.'" />'.get_string('vol_cmd_ttl', 'mdlds').'</option>';
    $select_opt .= '<option value="'.MDLDS_LTI_SUBMIT_CMD.'" />'.get_string('sub_cmd_ttl', 'mdlds').'</option>';
    for ($cnt=0; $cnt<$num; $cnt++) {
        $table->data[$i][] = '<select name="'.MDLDS_LTI_VOLUME_CMD.'[]" autocomplete="off">'.$select_opt.'</select>'; 
        $table->data[$i][] = '<input type="text" name="'.MDLDS_LTI_VOLUME_CMD.'name[]" size="15" maxlength="30"  value="" />';
        $table->data[$i][] = '<input type="text" name="'.MDLDS_LTI_VOLUME_CMD.'disp[]" size="30" maxlength="60"  value="" />';
        $table->data[$i][] = '<input type="text" name="'.MDLDS_LTI_VOLUME_CMD.'user[]" size="50" maxlength="200" value="" />';
        $i++;
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}


//
function show_lti_edit_table($cmds, $images)
{
    show_lti_edit_table_cmd($cmds, $images);
    echo '<hr />';
    show_lti_edit_table_vol($cmds);
}
