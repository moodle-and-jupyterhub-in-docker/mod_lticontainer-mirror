<?php


function show_jhuser_delete_table($deletes, $users, $name_pattern)
{
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '40px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('user_name','mod_lticontainer');
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

    $table->head [] = get_string('user_last','mod_lticontainer');
    $table->align[] = 'left';
    $table->size [] = '150px';
    $table->wrap [] = 'nowrap';

    //
    $i = 0;
    foreach($deletes as $del_name=>$del) { 
        foreach($users as $user) { 
            if ($user->username==$del_name and $del=='1') {
                $table->data[$i][] = $i + 1;
                $table->data[$i][] = $user->username. '<input type="hidden" name="delete['.$user->username.']" value="1" />';
                $table->data[$i][] = get_namehead($name_pattern, $user->firstname, $user->lastname, '');
                $table->data[$i][] = $user->status;
                $table->data[$i][] = $user->role;
                $table->data[$i][] = passed_time($user->lstact);
                $i++;
                break;
            }
        }
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}

