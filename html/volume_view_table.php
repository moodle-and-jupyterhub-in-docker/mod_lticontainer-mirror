<?php


function show_volume_view_table($items, $base_url)
{
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '10px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('driver_name','mdlds');
    $table->align[] = 'center';
    $table->size [] = '30px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('volume_name','mdlds');
    $table->align[] = 'center';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('volume_name','mdlds');
    $table->align[] = 'center';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    //
    $i = 0;
    foreach($items as $item) { 
        $table->data[$i][] = $i;
        $table->data[$i][] = $item->driver;
        $table->data[$i][] = $item->volname;
        $table->data[$i][] = $item->command;
        $i++;
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}

