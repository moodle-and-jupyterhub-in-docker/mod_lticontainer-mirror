<?php
/**
 * Use Moodle's Charts API to visualize learning data.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


    
function show_dashboard_view_chart($cmid, $args)
{
    global $OUTPUT;

    // チャートを描画
    echo '<h3><strong>'.$args->chart_title.'</strong></h3>';
    echo '<table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">';
    foreach ($args->charts as $chart) {
    echo   '<tr><td align="center">';
        echo $OUTPUT->render_chart($chart, false);
    echo   '</td></tr>';
    }
    echo '</table>';

    return;
}

