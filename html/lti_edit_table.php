<?php

function show_lti_edit_table_cmd($cmds, $params)
{
    $table = new html_table();
    //
    $table->head [] = get_string('custom_command', 'mod_ltids');
    $table->align[] = 'left';
    $table->size [] = '165px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('users/image', 'mod_ltids'); 
    $table->align[] = 'left';
    $table->size [] = '300px';
    $table->wrap [] = 'nowrap';

    $table->head [] = ''; 
    $table->align[] = 'left';
    $table->size [] = '30px';
    $table->wrap [] = 'nowrap';

    $table->head [] = ''; 
    $table->align[] = 'left';
    $table->size [] = '50px';
    $table->wrap [] = 'nowrap';

    $table->head [] = ''; 
    $table->align[] = 'left';
    $table->size [] = '50px';
    $table->wrap [] = 'nowrap';

    $table->head [] = ''; 
    $table->align[] = 'left';
    $table->size [] = '30px';
    $table->wrap [] = 'nowrap';

    $table->head [] = ''; 
    $table->align[] = 'left';
    $table->size [] = '50px';
    $table->wrap [] = 'nowrap';

    $table->head [] = ''; 
    $table->align[] = 'left';
    $table->size [] = '50px';
    $table->wrap [] = 'nowrap';

    //
    $i = 0;
    $users_cmd    = '';
    $teachers_cmd = '';
    $image_cmd    = '';
    $cpugrnt_cmd  = '';
    $memgrnt_cmd  = '';
    $cpulimit_cmd = '';
    $memlimit_cmd = '';
    $options_cmd  = '';
    $url_cmd      = '';
    if (isset($cmds->custom_cmd[LTIDS_LTI_USERS_CMD]))    $users_cmd    = $cmds->custom_cmd[LTIDS_LTI_USERS_CMD];
    if (isset($cmds->custom_cmd[LTIDS_LTI_TEACHERS_CMD])) $teachers_cmd = $cmds->custom_cmd[LTIDS_LTI_TEACHERS_CMD];
    if (isset($cmds->custom_cmd[LTIDS_LTI_IMAGE_CMD]))    $image_cmd    = $cmds->custom_cmd[LTIDS_LTI_IMAGE_CMD];
    if (isset($cmds->custom_cmd[LTIDS_LTI_CPUGRNT_CMD]))  $cpugrnt_cmd  = $cmds->custom_cmd[LTIDS_LTI_CPUGRNT_CMD];
    if (isset($cmds->custom_cmd[LTIDS_LTI_MEMGRNT_CMD]))  $memgrnt_cmd  = $cmds->custom_cmd[LTIDS_LTI_MEMGRNT_CMD];
    if (isset($cmds->custom_cmd[LTIDS_LTI_CPULIMIT_CMD])) $cpulimit_cmd = $cmds->custom_cmd[LTIDS_LTI_CPULIMIT_CMD];
    if (isset($cmds->custom_cmd[LTIDS_LTI_MEMLIMIT_CMD])) $memlimit_cmd = $cmds->custom_cmd[LTIDS_LTI_MEMLIMIT_CMD];
    if (isset($cmds->custom_cmd[LTIDS_LTI_OPTIONS_CMD]))  $options_cmd  = $cmds->custom_cmd[LTIDS_LTI_OPTIONS_CMD];
    if (isset($cmds->custom_cmd[LTIDS_LTI_DEFURL_CMD]))   $url_cmd      = $cmds->custom_cmd[LTIDS_LTI_DEFURL_CMD];

    // LTIDS_LTI_USERS_CMD
    $table->data[$i][] = '<strong>'.get_string('users_cmd_ttl', 'mod_ltids').'</strong>';
    $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_USERS_CMD.'" size="50" maxlength="200" value="'.$users_cmd.'" />';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $i++;

    // LTIDS_LTI_TEACHERS_CMD
    $table->data[$i][] = '<strong>'.get_string('teachers_cmd_ttl', 'mod_ltids').'</strong>';
    $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_TEACHERS_CMD.'" size="50" maxlength="200" value="'.$teachers_cmd.'" />';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $i++;

    // LTIDS_LTI_IMAGE_CMD
    $select_opt = '';
    foreach($params->images as $image) {
        $selected = '';
        if ($image==$image_cmd) $selected = 'selected="selected"';
        $select_opt .= '<option value="'.$image.'" '.$selected.'>'.$image.'</option>';
    }
    $table->data[$i][] = '<strong>'.get_string('image_cmd_ttl', 'mod_ltids').'</strong>';
    $table->data[$i][] = '<select name="'.LTIDS_LTI_IMAGE_CMD.'" >'.$select_opt.'</select>';
    $table->data[$i][] = '';

    // LTIDS_LTI_OPTIONS_CMD
    /*/
    $select_opt = '';
    foreach($params->options as $key=>$option) {
        $selected = '';
        if ($option==$options_cmd) $selected = 'selected="selected"';
        $select_opt .= '<option value="'.$option.'" '.$selected.'>'.$key.'</option>';
    }
    $table->data[$i][] = '<select name="'.LTIDS_LTI_OPTIONS_CMD.'" >'.$select_opt.'</select>';
    /*/

    // LTIDS_CPULIMIT_CMD
    $select_opt = '';
    foreach($params->cpu_grnt as $key=>$cpu) {
        $selected = '';
        if ($cpu==$cpugrnt_cmd) $selected = 'selected="selected"';
        $select_opt .= '<option value="'.$cpu.'" '.$selected.'>'.$key.'</option>';
    }
    $table->data[$i][] = '<strong>'.get_string('cpugrnt_cmd_ttl', 'mod_ltids').'</strong>';
    $table->data[$i][] = '<select name="'.LTIDS_LTI_CPUGRNT_CMD.'" >'.$select_opt.'</select>';
    $table->data[$i][] = '';

    // LTIDS_CPULIMIT_CMD
    $select_opt = '';
    foreach($params->cpu_limit as $key=>$cpu) {
        $selected = '';
        if ($cpu==$cpulimit_cmd) $selected = 'selected="selected"';
        $select_opt .= '<option value="'.$cpu.'" '.$selected.'>'.$key.'</option>';
    }
    $table->data[$i][] = '<strong>'.get_string('cpulimit_cmd_ttl', 'mod_ltids').'</strong>';
    $table->data[$i][] = '<select name="'.LTIDS_LTI_CPULIMIT_CMD.'" >'.$select_opt.'</select>';
    $i++;

    // LTIDS_LTI_DEFURL_CMD
    $select_opt = '';
    foreach($params->lab_urls as $key=>$url) {
        $selected = '';
        if ($url==$url_cmd) $selected = 'selected="selected"';
        $select_opt .= '<option value="'.$url.'" '.$selected.'>'.$key.'</option>';
    }
    $table->data[$i][] = '<strong>'.get_string('lab_url_cmd_ttl', 'mod_ltids').'</strong>';
    $table->data[$i][] = '<select name="'.LTIDS_LTI_DEFURL_CMD.'" >'.$select_opt.'</select>';
    $table->data[$i][] = '';

    // LTIDS_MEMLGRNT_CMD
    $select_opt = '';
    foreach($params->mem_grnt as $key=>$mem) {
        $selected = '';
        if ($mem==$memgrnt_cmd) $selected = 'selected="selected"';
        $select_opt .= '<option value="'.$mem.'" '.$selected.'>'.$key.'</option>';
    }
    $table->data[$i][] = '<strong>'.get_string('memgrnt_cmd_ttl', 'mod_ltids').'</strong>';
    $table->data[$i][] = '<select name="'.LTIDS_LTI_MEMGRNT_CMD.'" >'.$select_opt.'</select>';
    $table->data[$i][] = '';

    // LTIDS_MEMLIMIT_CMD
    $select_opt = '';
    foreach($params->mem_limit as $key=>$mem) {
        $selected = '';
        if ($mem==$memlimit_cmd) $selected = 'selected="selected"';
        $select_opt .= '<option value="'.$mem.'" '.$selected.'>'.$key.'</option>';
    }
    $table->data[$i][] = '<strong>'.get_string('memlimit_cmd_ttl', 'mod_ltids').'</strong>';
    $table->data[$i][] = '<select name="'.LTIDS_LTI_MEMLIMIT_CMD.'" >'.$select_opt.'</select>';
    $i++;

    // dummy
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';
    $table->data[$i][] = '';

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}


function show_lti_edit_table_vol($cmds)
{
    $table = new html_table();
    //
    $table->head [] = get_string('volume_role', 'mod_ltids');
    $table->align[] = 'left';
    $table->size [] = '100px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('volume_name', 'mod_ltids');
    $table->align[] = 'left';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('access_name', 'mod_ltids');
    $table->align[] = 'left';
    $table->size [] = '300px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('access_users', 'mod_ltids');
    $table->align[] = 'left';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    $table->head [] = ''; 
    $table->align[] = 'left';
    $table->size [] = '100px';
    $table->wrap [] = 'nowrap';

    //
    $i = 0;
    // Presen Volumes
    if (isset($cmds->mount_vol)) {
        foreach($cmds->mount_vol as $key => $value) { 
            if (!isset($cmds->vol_users[$key])) $cmds->vol_users[$key] = '';
            $table->data[$i][] = '<input type="hidden" name="'.LTIDS_LTI_VOLUMES_CMD.'[]" value="'.LTIDS_LTI_VOLUMES_CMD.'" />'. 
                                 '<strong>'.get_string('vol_cmd_ttl', 'mod_ltids').'</strong>';
            $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'name[]"  size="20" value="'.$key.'" readonly style="background-color:#eee;"/>';
            $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'link[]"  size="50" maxlength="100" value="'.$value.'" />';
            $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'users[]" size="50" maxlength="200" value="'.$cmds->vol_users[$key].'" />';
            $table->data[$i][] = '';
            $i++;
        }
    }

    // Submit Volumes
    if (isset($cmds->mount_sub)) {
        foreach($cmds->mount_sub as $key => $value) { 
            if (!isset($cmds->sub_users[$key])) $cmds->sub_users[$key] = '';
            $table->data[$i][] = '<input type="hidden" name="'.LTIDS_LTI_VOLUMES_CMD.'[]" value="'.LTIDS_LTI_SUBMITS_CMD.'" />'. 
                                 '<strong>'.get_string('sub_cmd_ttl', 'mod_ltids').'</strong>';
            $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'name[]"  size="20" value="'.$key.'" readonly style="background-color:#eee;"/>';
            $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'link[]"  size="50" maxlength="100" value="'.$value.'" />';
            $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'users[]" size="50" maxlength="200" value="'.$cmds->sub_users[$key].'" />';
            $table->data[$i][] = '';
            $i++;
        }
    }

    // Personal Volumes
    if (isset($cmds->mount_prs)) {
        foreach($cmds->mount_prs as $key => $value) { 
            if (!isset($cmds->prs_users[$key])) $cmds->prs_users[$key] = '';
            $table->data[$i][] = '<input type="hidden" name="'.LTIDS_LTI_VOLUMES_CMD.'[]" value="'.LTIDS_LTI_PRSNALS_CMD.'" />'. 
                                 '<strong>'.get_string('prs_cmd_ttl', 'mod_ltids').'</strong>';
            $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'name[]"  size="20" value="'.$key.'" readonly style="background-color:#eee;"/>';
            $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'link[]"  size="50" maxlength="100" value="'.$value.'" />';
            $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'users[]" size="50" maxlength="200" value="'.$cmds->prs_users[$key].'" />';
            $table->data[$i][] = '';
            $i++;
        }
    }

    // New Volumes
    $num = 3;
    $select_opt  = '<option value="'.LTIDS_LTI_VOLUMES_CMD.'" />'.get_string('vol_cmd_ttl', 'mod_ltids').'</option>';
    $select_opt .= '<option value="'.LTIDS_LTI_SUBMITS_CMD.'" />'.get_string('sub_cmd_ttl', 'mod_ltids').'</option>';
    $select_opt .= '<option value="'.LTIDS_LTI_PRSNALS_CMD.'" />'.get_string('prs_cmd_ttl', 'mod_ltids').'</option>';
    for ($cnt=0; $cnt<$num; $cnt++) {
        $table->data[$i][] = '<select name="'.LTIDS_LTI_VOLUMES_CMD.'[]" autocomplete="off">'.$select_opt.'</select>'; 
        $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'name[]"  size="20" maxlength="30"  value="" />';
        $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'link[]"  size="50" maxlength="100" value="" />';
        $table->data[$i][] = '<input type="text" name="'.LTIDS_LTI_VOLUMES_CMD.'users[]" size="50" maxlength="200" value="" />';
        $table->data[$i][] = '';
        $i++;
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}


//
function show_lti_edit_table($cmds, $params)
{
    show_lti_edit_table_cmd($cmds, $params);
    show_lti_edit_table_vol($cmds);
}

