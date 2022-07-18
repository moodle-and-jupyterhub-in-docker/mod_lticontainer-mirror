<?php


function  make_jupyterhub_tablehead($edit_cap, $name_pattern, $action_url, $sort_params, $show_status)
{
    $name_url_params = $sort_params;
    if ($name_url_params['nmsort']=='none' or $name_url_params['nmsort']=='asc') $name_url_params['nmsort'] = 'desc';
    else                                                                         $name_url_params['nmsort'] = 'asc';
    $name_url_params['sort']   = 'nmsort';
    $name_url_params['tmsort'] = 'none';
    $name_url_params['status'] = $show_status;
    $name_url = new moodle_url($action_url, $name_url_params);

    $last_url_params = $sort_params;
    if ($last_url_params['tmsort']=='asc') $last_url_params['tmsort'] = 'desc';
    else                                   $last_url_params['tmsort'] = 'asc';
    $last_url_params['sort']   = 'tmsort';
    $last_url_params['nmsort'] = 'desc';
    $last_url_params['status'] = $show_status;
    $last_url = new moodle_url($action_url, $last_url_params);

    //
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '20px';

    $table->head [] = '';
    $table->align[] = '';
    $table->size [] = '20px';

    $table->head [] = '<a href="'.$name_url.'">'.get_string('user_name','mod_lticontainer').'</a>';
    $table->align[] = 'left';
    $table->size [] = '140px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_namehead($name_pattern, get_string('firstname'), get_string('lastname'), '/');  //
    $table->align[] = 'left';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('user_status','mod_lticontainer');
    $table->align[] = 'center';
    $table->size [] = '80px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('user_role','mod_lticontainer');
    $table->align[] = 'center';
    $table->size [] = '100px';
    $table->wrap [] = 'nowrap';

    $table->head [] = '<a href="'.$last_url.'">'.get_string('user_last','mod_lticontainer').'</a>';
    $table->align[] = 'left';
    $table->size [] = '150px';
    $table->wrap [] = 'nowrap';

    if ($edit_cap) $table->head [] = get_string('user_del','mod_lticontainer');
    else           $table->head [] = '&nbsp;';
    $table->align[] = 'center';
    $table->size [] = '120px';
    $table->wrap [] = 'nowrap';

    return $table;
}


function  show_jupyterhub_table($users, $courseid, $edit_cap, $name_pattern, $action_url, $sort_params, $show_status, $page_size)
{
    global $OUTPUT;

    define('PAGE_ROW_SIZE', $page_size);

    $table = make_jupyterhub_tablehead($edit_cap, $name_pattern, $action_url, $sort_params, $show_status);

    $prfl_base_url = new moodle_url('/user/view.php', array('course'=>$courseid));
    $pic_options = array('size'=>20, 'link'=>true, 'alttext'=>true, 'courseid'=>$courseid, 'popup'=>true);
    $i = 0;
    foreach($users as $user) { 
        //$href_url = $user_url.'&userid='. strval($user->id);
        $prfl_url = $prfl_base_url.'&id='.strval($user->id);
        //
        $table->data[$i][] = $i + 1;
        $table->data[$i][] = $OUTPUT->user_picture($user, $pic_options);
        //$table->data[$i][] = '<a href="'.$href_url.'" >'.$user->username.'</a>';
        $table->data[$i][] = $user->username;
        $table->data[$i][] = '<a href="'.$prfl_url.'" target=_blank>'.get_namehead($name_pattern, $user->firstname, $user->lastname, '').'</a>';
        $table->data[$i][] = $user->status;
        $table->data[$i][] = $user->role;
        $table->data[$i][] = passed_time($user->lstact);
        if ($edit_cap and $user->status=='OK') $table->data[$i][] = '<input type="checkbox" name="delete['.$user->username.']" value="1" />';
        else                                   $table->data[$i][] = '-';
        $i++;

        if ($i%PAGE_ROW_SIZE==0) {
            echo '<div align="center" style="overflow-x: auto;">';  // スクロールしません
            echo html_writer::table($table);
            if ($edit_cap) {
                echo '<div align="center">';
                show_button_jupyterhub();
                echo '</div>';
            }
            echo '</div><br />';
            unset($table->data);
        }
    }

    if ($i%PAGE_ROW_SIZE!=0 or $i==0) {
        echo '<div align="center" style="overflow-x: auto;">';      // スクロールしません
        echo html_writer::table($table);
        if ($edit_cap) {
            echo '<div align="center">';
            show_button_jupyterhub();
            echo '</div>';
        }
        echo '</div><br />';
        unset($table->data);
    }
}



function  show_button_jupyterhub()
{
    //echo '<input type="submit" name="esv" value="'.get_string('submit').'" />';
    echo '<input type="submit" name="submit_jhuser_del" value="'.get_string('delete').'" />';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<input type="reset"  name="cancel_jhuser_del" value="'.get_string('reset').'" />';
}

