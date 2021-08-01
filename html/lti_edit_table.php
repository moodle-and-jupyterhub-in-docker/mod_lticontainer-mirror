<?php


function show_lti_edit_table($comms)
{
    $table = new html_table();
    //
    $table->head [] = 'Command';
    $table->align[] = 'left';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

    $table->head [] = 'Name/Users';
    $table->align[] = 'left';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    $table->head [] = 'Volume Name';
    $table->align[] = 'left';
    $table->size [] = '150px';
    $table->wrap [] = 'nowrap';

    $table->head [] = 'Display Name';
    $table->align[] = 'left';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    //
    $i = 0;

    if (isset($comms->custom_com)) {
        foreach($comms->custom_com as $key => $value) { 
            $table->data[$i][] = $key;
            $table->data[$i][] = $value;
            $table->data[$i][] = '';
            $table->data[$i][] = '';
            $i++;
        }
    }

    if (isset($comms->mount_vol)) {
        foreach($comms->mount_vol as $key => $value) { 
            $table->data[$i][] = 'Mount Volume';
            if (isset($comms->vol_user[$key])) $table->data[$i][] = $comms->vol_user[$key];
            else $table->data[$i][] = '-';
            $table->data[$i][] = $key;
            $table->data[$i][] = $value;
            $i++;
        }
    }

    if (isset($comms->mount_sub)) {
        foreach($comms->mount_sub as $key => $value) { 
            $table->data[$i][] = 'Submit Volume';
            if (isset($comms->sub_user[$key])) $table->data[$i][] = $comms->sub_user[$key];
            else $table->data[$i][] = '-';
            $table->data[$i][] = $key;
            $table->data[$i][] = $value;
            $i++;
        }
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}

