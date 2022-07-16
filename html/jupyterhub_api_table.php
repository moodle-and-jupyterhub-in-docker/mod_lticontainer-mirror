<?php


define('PAGE_ROW_SIZE', 10);


function  make_jupyterhub_tablehead($edit_cap, $name_pattern)
{
    //$firstname = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=firstname&amp;order='.$order.'">'.get_string('firstname').'</a>';
    //$lastname  = '<a href="'.$wwwMyURL.$url_options.'&amp;sort=lastname&amp;order='.$order.'">' .get_string('lastname').'</a>';


    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '20px';

    $table->head [] = '';
    $table->align[] = '';
    $table->size [] = '20px';

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

    return $table;
}


function  show_jupyterhub_table($users, $courseid, $edit_cap, $name_pattern, $sort_params, $show_status)
{
    global $OUTPUT;

    $members = array();
    //
    $i = 0;
    foreach($users as $user) { 
        if ($show_status=='ALL' or $show_status==$user->jh->status) {
            $role = 'none';
            $lstact = '';
            $lsttm  = 0;
            $status = $user->jh->status;
            if ($status=='OK') {
                if ($user->jh->admin=='1') $role = 'admin';
                else                       $role = 'user';
                $lstact = $user->jh->last_activity;
                $lsttm  = strtotime($lstact);
            }
            //
            $members[$i] = new StdClass();
            $members[$i]->user     = $user;
            $members[$i]->username = $user->username;
            $members[$i]->status   = $status;
            $members[$i]->role     = $role;
            $members[$i]->lstact   = $lstact;
            $members[$i]->lsttm    = $lsttm;

            $i++;
        }   
    }

    // Sorting
    if ($sort_params['sort']=='nmsort') {
        if ($sort_params['nmsort']=='desc') {
            usort($members, function($a, $b) {return $a->username > $b->username ? -1 : 1;});
        }
    }
    else if ($sort_params['sort']=='tmsort') {
        if ($sort_params['tmsort']=='asc') {
            usort($members, function($a, $b) {return $a->lsttm > $b->lsttm ? -1 : 1;});
        }
        else if ($sort_params['tmsort']=='desc') {
            usort($members, function($a, $b) {return $a->lsttm < $b->lsttm ? -1 : 1;});
        }
    }

    //
    $pic_options = array('size'=>20, 'link'=>true, 'alttext'=>true, 'courseid'=>$courseid, 'popup'=>true);
    $table = make_jupyterhub_tablehead($edit_cap, $name_pattern);

    $i = 0;
    foreach($members as $member) { 
        //
        $table->data[$i][] = $i+1;
        $table->data[$i][] = $OUTPUT->user_picture($member->user, $pic_options);
        $table->data[$i][] = get_namehead($name_pattern, $member->user->firstname, $member->user->lastname, '');
        $table->data[$i][] = $member->username;
        $table->data[$i][] = $member->status;
        $table->data[$i][] = $member->role;
        $table->data[$i][] = passed_time($member->lstact);
        if ($edit_cap) $table->data[$i][] = '<input type="checkbox" name="delete['.$member->username.']" value="1" />';
        else           $table->data[$i][] = '&nbsp;';
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
    echo '<input type="submit" name="esv" value="'.get_string('submit').'" />';
    echo '&nbsp;&nbsp;';
    echo '<input type="reset"  name="esv" value="'.get_string('reset').'" />';
}

