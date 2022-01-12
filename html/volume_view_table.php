<?php


function show_volume_view_table($items, $edit_cap, $base_url)
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

    if ($edit_cap) $table->head [] = get_string('volume_del','mod_ltids');
    else           $table->head [] = '&nbsp;';
    $table->align[] = 'center';
    $table->size [] = '80px';
    $table->wrap [] = 'nowrap';

    //
    $i = 1;
    foreach($items as $item) { 
        $table->data[$i][] = $i;
        $table->data[$i][] = $item->driver;
        $table->data[$i][] = $item->shrtname;
        $table->data[$i][] = $item->role;
        if ($edit_cap) $table->data[$i][] = '<input type="checkbox" name="delete['.$item->fullname.']" value="1" />';
        else           $table->data[$i][] = '&nbsp;';
        $i++;
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}

