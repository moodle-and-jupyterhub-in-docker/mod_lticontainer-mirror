<?php
/**
 * dashboard_view.php
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    
function show_dashboard_view($cmid, $charts_data)
{
    global $OUTPUT;

    $chart_url = new moodle_url('/mod/ltids/actions/chart_view.php', array('id'=>$cmid));

/*
    $table = new html_table();

    $table->align[] = 'center';
    $table->size [] = '500px';

    $table->align[] = 'center';
    $table->size [] = '500px';

    $i = 0;
    foreach ($charts_data as $data) {
        $chart_url->param('chart_kind', $data->kind);
        $table->data[$i/2][]= '<a href='.$chart_url.' ><h4><strong>'.$data->title.'</strong></h4>'.$OUTPUT->render_chart($data->charts[0], false).'</a>';
        $i++;
    }
    echo html_writer::table($table);
*/

    // チャートを描画

    $col_num = 3;

    echo '<table border="1" align="center" cellpadding="0" cellspacing="0">';
    $i = 0;
    foreach ($charts_data as $data) {
        if ($i%$col_num==0) echo '<tr>';
        $chart_url->param('chart_kind', $data->kind);
        echo '<td width="400" align="center">';
        echo '<a href='.$chart_url.' >';
        echo '<h4><strong>'.$data->title.'</strong></h4>';
        echo $OUTPUT->render_chart($data->charts[0], false);
        echo '</a>';
        echo '</td>';
        if (($i+1)%$col_num==0) echo '</tr>';
        $i++;
    }
    echo '</table>';

    return;
}

