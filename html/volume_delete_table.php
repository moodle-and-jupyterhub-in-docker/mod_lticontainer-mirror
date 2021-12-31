<?php


function show_volume_delete_table($deletes, $items)
{
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '40px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('driver_name','mod_ltids');
    $table->align[] = 'left';
    $table->size [] = '50px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('volume_name','mod_ltids');
    $table->align[] = 'left';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('volume_role','mod_ltids');
    $table->align[] = 'left';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    //
    $i = 1;
    foreach($items as $item) { 
        if (array_key_exists($item->fullname, $deletes)) {
            $table->data[$i][] = $i;
            $table->data[$i][] = $item->driver;
            $table->data[$i][] = $item->shrtname . '<input type="hidden" name="delete['.$item->fullname.']" value="1" />';
            $table->data[$i][] = $item->role;
            $i++;
        }
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}

