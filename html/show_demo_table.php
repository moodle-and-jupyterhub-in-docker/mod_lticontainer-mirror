<?php

///////////////////////////////////////////////////////////////////////////////////////////////
// Show Demo


function show_demo_set_header(&$table)
{
    unset($table->head);
    unset($table->align);
    unset($table->size);
    unset($table->wrap);

    // Header
    $table->head [] = '#';
    $table->align[] = 'right';
    $table->size [] = '20px';
    $table->wrap [] = 'nowrap';

    $table->head [] = 'Name';
    $table->align[] = 'center';
    $table->size [] = '80px';
    $table->wrap [] = 'nowrap';

    $table->head [] = 'Value';
    $table->align[] = 'left';
    $table->size [] = '100px';
    $table->wrap [] = 'nowrap';

    return;
}



function show_demo_disp_table()
{
    global $CFG, $DB;

    $table = new html_table();
    $datas = array();


    $datas[0]['num']   = 0;
    $datas[0]['name']  = "AAA";
    $datas[0]['value'] = "VVV";

    $datas[1]['num']   = 1;
    $datas[1]['name']  = "BBB";
    $datas[1]['value'] = "WWW";

    $i = 0;
    foreach($datas as $data) {
        $table->data[$i][] = $i + 1;
        $table->data[$i][] = $data['name'];
        $table->data[$i][] = $data['value'];
        $i++;
    }

    show_demo_set_header($table);
    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';

    //

    return;
}
