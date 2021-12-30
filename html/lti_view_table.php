<?php

function show_ltis_table($ltis, $base_url)
{
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '40px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('lti_name','mod_ltids');
    $table->align[] = '';
    $table->size [] = '200px';
    $table->wrap [] = 'nowrap';

    $table->head [] = ' ';
    $table->align[] = '';
    $table->size [] = '150px';
    $table->wrap [] = 'nowrap';

    //
    $i = 0;
    foreach($ltis as $lti) { 
        $url_params = array("lti_id"=>$lti->id);
        $action_url = new moodle_url($base_url, $url_params);
        $table->data[$i][] = $i + 1;
        $table->data[$i][] = "<a href=".$action_url.">".$lti->name."</a>";
        $table->data[$i][] = '';
        $i++;
    }

    echo '<div align="">';
    echo html_writer::table($table);
    echo '</div>';
}

