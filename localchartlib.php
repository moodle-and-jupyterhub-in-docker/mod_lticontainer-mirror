<?php
/**
 * Use Moodle's Charts API to visualize learning data.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('CHART_BAR_LINENUM', 10);



function  chart_dashboard($recs)
{
    $charts_data = array();

    $charts_data[0] = new StdClass();
    $charts_data[0]->charts = chart_total_pie($recs, '*', '*', true);
    $charts_data[0]->kind   = 'total_pie';
    $charts_data[0]->title  = 'Total Activities';

    $charts_data[1] = new StdClass();
    $charts_data[1]->charts = chart_users_bar($recs, '*', '*', true);
    $charts_data[1]->kind   = 'users_bar';
    $charts_data[1]->title  = 'Total Activities per User';

    $charts_data[2] = new StdClass();
    $charts_data[2]->charts = chart_total_pie($recs, '*', '*', true);
    $charts_data[2]->kind   = 'total_pie';
    $charts_data[2]->title  = 'Total Activities';

    $charts_data[3] = new StdClass();
    $charts_data[3]->charts = chart_users_bar($recs, '*', '*', true);
    $charts_data[3]->kind   = 'users_bar';
    $charts_data[3]->title  = 'Total Activities per User';

    $charts_data[4] = new StdClass();
    $charts_data[4]->charts = chart_total_pie($recs, '*', '*', true);
    $charts_data[4]->kind   = 'total_pie';
    $charts_data[4]->title  = 'Total Activities';

    $charts_data[5] = new StdClass();
    $charts_data[5]->charts = chart_users_bar($recs, '*', '*', true);
    $charts_data[5]->kind   = 'users_bar';
    $charts_data[5]->title  = 'Total Activities per User';

    $charts_data[6] = new StdClass();
    $charts_data[6]->charts = chart_total_pie($recs, '*', '*', true);
    $charts_data[6]->kind   = 'total_pie';
    $charts_data[6]->title  = 'Total Activities';

    $charts_data[7] = new StdClass();
    $charts_data[7]->charts = chart_users_bar($recs, '*', '*', true);
    $charts_data[7]->kind   = 'users_bar';
    $charts_data[7]->title  = 'Total Activities per User';

    $charts_data[8] = new StdClass();
    $charts_data[8]->charts = chart_total_pie($recs, '*', '*', true);
    $charts_data[8]->kind   = 'total_pie';
    $charts_data[8]->title  = 'Total Activities';

    $charts_data[9] = new StdClass();
    $charts_data[9]->charts = chart_users_bar($recs, '*', '*', true);
    $charts_data[9]->kind   = 'users_bar';
    $charts_data[9]->title  = 'Total Activities per User';

    $charts_data[10] = new StdClass();
    $charts_data[10]->charts = chart_total_pie($recs, '*', '*', true);
    $charts_data[10]->kind   = 'total_pie';
    $charts_data[10]->title  = 'Total Activities';

    $charts_data[11] = new StdClass();
    $charts_data[11]->charts = chart_users_bar($recs, '*', '*', true);
    $charts_data[11]->kind   = 'users_bar';
    $charts_data[11]->title  = 'Total Activities per User';

    return $charts_data;
}



function  chart_total_pie($recs, $username, $filename, $dashboard=false)
{
    $ok = 0;
    $er = 0;

    $exclsn = false;
    foreach ($recs as $rec) {
        //
        if ($username !== '*' and $rec->username !== $username) $exclsn = true;
        if (!$exclsn) {
            //if (empty($rec->filename) or ($filename !== '*' and $rec->filename !== $filename)) $exclsn = true;
            if ($filename !== '*' and $rec->filename !== $filename) $exclsn = true; // filename 無しもカウント
        }
        //
        if (!$exclsn) {
            if ($rec->status == 'ok') $ok++;
            else                      $er++;
        }
        $exclsn = false;
    }

    // Total(Known + Unknown) activities
    $series = new core\chart_series('Count', [$ok, $er]);
    $labels = ['OK', 'ERROR'];
    $chart  = new \core\chart_pie();
    $chart->add_series($series);
    $chart->set_labels($labels);
    //
    $charts = array($chart);

    return $charts;
}



//
function  chart_users_bar($recs, $username, $filename, $dashboard=false)
{
    $user_data = array();
    //
    $exclsn = false;
    foreach ($recs as $rec) {
        //
        if ($username !== '*' and $rec->username !== $username) $exclsn = true;
        if (!$exclsn) {
            //if (empty($rec->filename) or ($filename !== '*' and $rec->filename !== $filename)) $exclsn = true;
            if ($filename !== '*' and $rec->filename !== $filename) $exclsn = true; // filename 無しもカウント
        }
        //
        if (!$exclsn) {
            $uname = $rec->username;
            if(!array_key_exists($uname, $user_data)) {
                $user_data[$uname] = ['ok'=>0,'er'=>0];
            }
            if ($rec->status == 'ok') {
                $user_data[$uname]['ok']++;
            }
            else {
                $user_data[$uname]['er']++;
            }
        }
        $exclsn = false;
    }

    // all data
    $maxval = 0;
    $us_srs = array();
    $ok_srs = array();
    $er_srs = array();

    foreach ($user_data as $name => $data) {
        if ($dashboard) $us_srs[] = '';
        else            $us_srs[] = $name;
        $ok_srs[] = $data['ok'];
        $er_srs[] = $data['er'];
        if ($maxval < $data['ok'] + $data['er']) $maxval = $data['ok'] + $data['er'];
    }
    //
    $stepsz = ceil($maxval/4);
    $pw     = 10**(strlen($stepsz)-1);
    $stepsz = floor($stepsz/$pw)*$pw;
    if ($stepsz==0) $stepsz = 1; 
    $maxval = $stepsz*5;

    //
    $charts = array();
    $array_num = count($us_srs);
    $cnt = 0;
    $num = 0;
    while ($num < $array_num) {
        //            
        $us_wrk = array();
        $ok_wrk = array();
        $er_wrk = array();

        $stop_i = CHART_BAR_LINENUM;
        if (($cnt+1)*CHART_BAR_LINENUM > $array_num) {
            $stop_i = $array_num % CHART_BAR_LINENUM;
        }
        $cnt++;

        for ($i=0; $i<$stop_i; $i++) {
            $us_wrk[] = $us_srs[$num];
            $ok_wrk[] = $ok_srs[$num];
            $er_wrk[] = $er_srs[$num];
            $num++;
        }
        for ($i=$stop_i; $i<CHART_BAR_LINENUM; $i++) {
            $us_wrk[] = '';
            $ok_wrk[] = 0;
            $er_wrk[] = 0;
        }

        //
        $chart = new core\chart_bar();
        $chart->set_horizontal(true);
        $chart->set_stacked(true);
        $chart->add_series(new core\chart_series('OK',    $ok_wrk));
        $chart->add_series(new core\chart_series('ERROR', $er_wrk));
        $chart->set_labels($us_wrk);
        //
        $xaxis = $chart->get_xaxis(0, true);
        $xaxis->set_max($maxval);
        $xaxis->set_stepsize($stepsz);
        //
        $charts[] = $chart;

        if ($dashboard) break;
    }

    return $charts;
}

