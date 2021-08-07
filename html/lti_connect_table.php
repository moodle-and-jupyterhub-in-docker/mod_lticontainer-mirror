<?php


function show_demo_disp_table($items, $base_url)
{
    $table = new html_table();
    //
    $table->head [] = '#';
    $table->align[] = 'center';
    $table->size [] = '20px';
    $table->wrap [] = 'nowrap';

    $table->head [] = get_string('lti_name','mod_mdlds');
    $table->align[] = 'center';
    $table->size [] = '60px';
    $table->wrap [] = 'nowrap';

    //
    $i = 0;
    foreach($items as $item) { 
        $url_params = array("ltiid"=>$item->id);
        $action_url = new moodle_url($base_url, $url_params);
        $table->data[$i][] = $i + 1;
        $table->data[$i][] = "<a href=".$action_url.">".$item->name."</a>";
        $i++;
    }

    echo '<div align="center">';
    echo html_writer::table($table);
    echo '</div>';
}

