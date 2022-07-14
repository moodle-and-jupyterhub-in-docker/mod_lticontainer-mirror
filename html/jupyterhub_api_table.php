<?php


function show_jupyterhub_api_table($users, $edit_cap)
{
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '40px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('user_name','mod_lticontainer');
    $table->align[] = 'left';
    $table->size [] = '50px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('user_role','mod_lticontainer');
    $table->align[] = 'left';
    $table->size [] = '100px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('user_server','mod_lticontainer');
    $table->align[] = 'left';
    $table->size [] = '100px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('user_last','mod_lticontainer');
    $table->align[] = 'left';
    $table->size [] = '150px';
    $table->wrap [] = 'nowrap';

    if ($edit_cap) $table->head [] = get_string('user_del','mod_lticontainer');
    else           $table->head [] = '&nbsp;';
    $table->align[] = 'center';
    $table->size [] = '80px';
    $table->wrap [] = 'nowrap';

    //
    $i = 1;
    foreach($users as $user) { 
        if ($user->jh->admin=='1') $role = 'admin';
        else                       $role = 'user';
        //
        $table->data[$i][] = $i;
        $table->data[$i][] = $user->username;
        $table->data[$i][] = $role;
        $table->data[$i][] = $user->jh->server;
        $table->data[$i][] = passed_time($user->jh->last_activity);
        if ($edit_cap) $table->data[$i][] = '<input type="checkbox" name="delete['.$user->username.']" value="1" />';
        else           $table->data[$i][] = '&nbsp;';
        $i++;
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}

