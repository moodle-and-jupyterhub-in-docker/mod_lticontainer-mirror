<?php


function show_jupyterhub_api_table($users, $edit_cap, $name_pattern)
{
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '40px';
    //$table->wrap [] = 'nowrap';

    $table->head [] = get_namehead($name_pattern, get_string('firstname'), get_string('lastname'), '/');  //
    $table->align[] = 'left';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('user_name','mod_lticontainer');
    $table->align[] = 'left';
    $table->size [] = '140px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('user_status','mod_lticontainer');
    $table->align[] = 'center';
    $table->size [] = '80px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('user_role','mod_lticontainer');
    $table->align[] = 'center';
    $table->size [] = '100px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('user_last','mod_lticontainer');
    $table->align[] = 'left';
    $table->size [] = '120px';
    $table->wrap [] = 'nowrap';

    if ($edit_cap) $table->head [] = get_string('user_del','mod_lticontainer');
    else           $table->head [] = '&nbsp;';
    $table->align[] = 'center';
    $table->size [] = '120px';
    $table->wrap [] = 'nowrap';

    //
    $i = 1;
    foreach($users as $user) { 
        $role = 'none';
        $lstact = '';
        $status = $user->jh->status;
        if ($status=='OK') {
            if ($user->jh->admin=='1') $role = 'admin';
            else                       $role = 'user';
            $lstact = $user->jh->last_activity;
        }
        //
        $table->data[$i][] = $i;
        $table->data[$i][] = get_namehead($name_pattern, $user->firstname, $user->lastname, '');
        $table->data[$i][] = $user->username;
        $table->data[$i][] = $status;
        $table->data[$i][] = $role;
        $table->data[$i][] = passed_time($lstact);
        if ($edit_cap) $table->data[$i][] = '<input type="checkbox" name="delete['.$user->username.']" value="1" />';
        else           $table->data[$i][] = '&nbsp;';
        $i++;
    }

    echo '<div align="center" style="overflow-x: auto;">';
    echo html_writer::table($table);
    echo '</div>';
}

