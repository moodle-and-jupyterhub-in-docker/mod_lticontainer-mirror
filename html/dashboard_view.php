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

    $chart_ttl = array('Real Time', 'Any Period Time');
    $col_num   = 4;

    // チャートを描画
    echo '<table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">';
    $i = 0;
    foreach ($charts_data as $data) {
        if ($i%$col_num==0) {
            echo '<table border="0" align="center" cellpadding="0" cellspacing="0">';
            echo '<tr><td><strong>'.$chart_ttl[$i/$col_num].'</strong></td></tr>';
            echo '</table>';
            echo '<table border="1" align="center" cellpadding="0" cellspacing="0">';
            echo '<tr>';
        }
        //
        $chart_url->param('chart_kind',  $data->kind);
        $chart_url->param('time_period', $data->period);
        echo '<td width="320" align="center">';
        echo '<a href='.$chart_url.' >';
        echo '<h4><strong>'.$data->title.'</strong></h4>';
        echo $OUTPUT->render_chart($data->charts[0], false);
        echo '</a>';
        echo '</td>';
        //
        if (($i+1)%$col_num==0) {
            echo '</tr>';
            echo '</table>';
        }
        $i++;
    }
    echo '</table>';

    return;
}

