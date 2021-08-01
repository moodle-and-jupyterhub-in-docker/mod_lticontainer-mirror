<?php


function show_volume_view_table($items, $base_url)
{
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '20px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('volume_name','mdlds');
    $table->align[] = 'center';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

    //
    $i = 0;
    foreach($items as $item) { 
        if ($i>0) {
            $table->data[$i][] = $i;
            $table->data[$i][] = $item;
        }
        $i++;
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}

