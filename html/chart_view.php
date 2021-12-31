<?php
/**
 * chart_view.php
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function show_chart_view($args)
{
    global $OUTPUT;

    // チャートを描画
    echo '<h3><strong>'.$args->chart_title.'</strong></h3>';
    echo '<table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">';
    echo   '<tr><td align="center">';
    //
    if (!empty($args->charts)) {
        foreach ($args->charts as $chart) {
            echo $OUTPUT->render_chart($chart, false);
        }
    }
    else {
        echo '<h3>No Data!</h3>';
    }
    //
    echo   '</td></tr>';
    echo '</table>';

    return;
}

