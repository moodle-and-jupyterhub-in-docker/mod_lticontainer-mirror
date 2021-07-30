<?php


function show_demo_disp_table($items)
{
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '20px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('lti_name','mdlds');
    $table->align[] = 'center';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

/*
    $table->head [] = get_string('value','mdlds');
    $table->align[] = 'center';
    $table->size [] = '120px';
    $table->wrap [] = 'nowrap';
*/

    //
    $i = 0;
    foreach($items as $item) { 
        $table->data[$i][] = $i + 1;
        $table->data[$i][] = $item->name;
        //$table->data[$i][] = $item->instructorcustomparameters;
        $i++;
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}

