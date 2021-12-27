<?php

function show_lti_disp_table($items)
{
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '40px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('lti_name','mod_ltids');
    $table->align[] = 'center';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    $table->head [] = 'No Display';
    $table->align[] = 'center';
    $table->size [] = '150px';
    $table->wrap [] = 'nowrap';

    //
    $i = 0;
    foreach($items as $item) { 
        $checked = '';
        if ($item->disp==0) $checked = 'checked';
        //
        $url_params = array("lti_id"=>$item->id);
        $table->data[$i][] = $i + 1;
        $table->data[$i][] = $item->name;
        $table->data[$i][] = '<input type="checkbox" name="nodisp['.$item->id.']" value="1" '.$checked.' />';
        $i++;
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}

