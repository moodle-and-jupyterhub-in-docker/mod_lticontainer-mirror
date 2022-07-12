<?php
/**
 * Use Moodle's Charts API to visualize learning data.
 *
 * @package     mod_lticontainer
 * @copyright   2021 Urano Masanori and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


require_once(__DIR__.'/locallib.php');
require_once(__DIR__.'/local_define.php');


/*
function  chart_dashboard($recs_r, $recs_a, $minstance)
function  chart_total_pie($recs, $username, $filename, $minstance, $dashboard=false)
function  chart_users_bar($recs, $username, $filename, $minstance, $dashboard=false)
function  chart_codecell_bar($recs, $username, $filename, $minstance, $dashboard=false)
function  chart_codecell_line($recs, $username, $filename, $minstance, $dashboard=false)
*/



function  chart_dashboard($recs_r, $recs_a, $minstance)
{
    $charts_data = array();

    $charts_data[0] = new StdClass();
    $charts_data[0]->charts = chart_total_pie($recs_r, '*', '*', $minstance, true);
    $charts_data[0]->kind   = 'total_pie';
    $charts_data[0]->period = 'real';
    $charts_data[0]->title  = 'Real Time Total Activities';

    $charts_data[1] = new StdClass();
    $charts_data[1]->charts = chart_codecell_bar($recs_r, '*', '*', $minstance, true);
    $charts_data[1]->kind   = 'codecell_bar';
    $charts_data[1]->period = 'real';
    $charts_data[1]->title  = 'Real Time Activity per Code Cell';

    $charts_data[2] = new StdClass();
    $charts_data[2]->charts = chart_users_bar($recs_r, '*', '*', $minstance, true);
    $charts_data[2]->kind   = 'users_bar';
    $charts_data[2]->period = 'real';
    $charts_data[2]->title  = 'Real Time Activity per User';

    $charts_data[3] = new StdClass();
    $charts_data[3]->charts = chart_codecell_line($recs_r, '*', '*', $minstance, true);
    $charts_data[3]->kind   = 'codecell_line';
    $charts_data[3]->period = 'real';
    $charts_data[3]->title  = 'Real Time User Progress on the Task';

    $charts_data[4] = new StdClass();
    $charts_data[4]->charts = chart_total_pie($recs_a, '*', '*', $minstance, true);
    $charts_data[4]->kind   = 'total_pie';
    $charts_data[4]->period = 'any';
    $charts_data[4]->title  = 'Total Activities';

    $charts_data[5] = new StdClass();
    $charts_data[5]->charts = chart_codecell_bar($recs_a, '*', '*', $minstance, true);
    $charts_data[5]->kind   = 'codecell_bar';
    $charts_data[5]->period = 'any';
    $charts_data[5]->title  = 'Activity per Code Cell';

    $charts_data[6] = new StdClass();
    $charts_data[6]->charts = chart_users_bar($recs_a, '*', '*', $minstance, true);
    $charts_data[6]->kind   = 'users_bar';
    $charts_data[6]->period = 'any';
    $charts_data[6]->title  = 'Activity per User';

    $charts_data[7] = new StdClass();
    $charts_data[7]->charts = chart_codecell_line($recs_a, '*', '*', $minstance, true);
    $charts_data[7]->kind   = 'codecell_line';
    $charts_data[7]->period = 'any';
    $charts_data[7]->title  = 'User Progress on the Task';

    return $charts_data;
}



//
// 全体の正答率
//
function  chart_total_pie($recs, $username, $filename, $minstance, $dashboard=false)
{
    $ok = 0;
    $er = 0;

    $exclsn = false;
    foreach ($recs as $rec) {
        if (empty($rec->filename))  $rec->filename = CHART_NULL_FILENAME; 
        if (empty($rec->username))  $rec->username = CHART_NULL_USERNAME; 
        if (is_null($rec->codenum)) $rec->codenum  = CHART_NULL_CODENUM; 
        //
        if ($username!=='*' and $rec->username!==$username) $exclsn = true;
        if (!$exclsn) {
            if ($filename!=='*' and $rec->filename!==$filename) $exclsn = true;
        }
        //
        if (!$exclsn) {
            if      ($rec->status == 'ok')    $ok++;
            else if ($rec->status == 'ok/nc') $ok++;    // no client (cell) data
            else                              $er++;
        }
        $exclsn = false;
    }

    // Total(Known + Unknown) activities
    //$series = new \core\chart_series('Count', [0, $ok, $er]);   // 0 -> 色調整
    //$labels = ['', 'OK', 'ERROR'];
    $series = new \core\chart_series('Count', [$ok, $er]);
    $labels = ['OK', 'ERROR'];
    $chart  = new \core\chart_pie();
    $chart->add_series($series);
    $chart->set_labels($labels);
    //$chart->set_doughnut(true);
    if ($dashboard) {
        if (method_exists($chart, 'set_legend_options')) $chart->set_legend_options(['display' => false]);
    }
    //
    $charts = array($chart);

    return $charts;
}



//
// ユーザ毎の活動状況
//
function  chart_users_bar($recs, $username, $filename, $minstance, $dashboard=false)
{
    $user_data = array();
    //
    $exclsn = false;
    foreach ($recs as $rec) {
        if (empty($rec->filename))  $rec->filename = CHART_NULL_FILENAME;
        if (empty($rec->username))  $rec->username = CHART_NULL_USERNAME; 
        if (is_null($rec->codenum)) $rec->codenum  = CHART_NULL_CODENUM; 
        //
        if ($username!=='*' and $rec->username!==$username) $exclsn = true;
        if (!$exclsn) {
            if ($filename!=='*' and $rec->filename!==$filename) $exclsn = true; 
        }
        //
        if (!$exclsn) {
            $uname = $rec->username;
            if(!array_key_exists($uname, $user_data)) {
                $user_data[$uname] = ['ok'=>0, 'er'=>0];
            }
            if      ($rec->status == 'ok')    $user_data[$uname]['ok']++;
            else if ($rec->status == 'ok/nc') $user_data[$uname]['ok']++;   // no client (cell) data
            else                              $user_data[$uname]['er']++;
        }
        $exclsn = false;
    }
    ksort($user_data);

    //
    // all data
    $maxval = 0;
    $us_srs = array();
    $ok_srs = array();
    $er_srs = array();
    //
    foreach ($user_data as $name => $data) {
        if ($dashboard) $us_srs[] = '';
        else            $us_srs[] = $name;
        $ok_srs[] = $data['ok'];
        $er_srs[] = $data['er'];
        if ($maxval < $data['ok'] + $data['er']) $maxval = $data['ok'] + $data['er'];
    }
    if ($maxval>0) {
        $stepsz = ceil($maxval/5);
        $pw     = 10**(strlen($stepsz)-1);
        $stepsz = floor($stepsz/$pw)*$pw;
        if ($stepsz==0) $stepsz = 1; 
        $maxval = (floor($maxval/$stepsz) + 1)*$stepsz;
    }
    //
    $array_num = count($us_srs);
    if ($array_num==0) {
        if ($username!=='*') $us_srs[] = $username;
        else                 $us_srs[] = '';
        $ok_srs[] = 0;
        $er_srs[] = 0;
        $array_num = 1;
    }

    ////////////////////////////
    $max_usernum = $minstance->chart_bar_usernum;
    if ($max_usernum <= 0) $max_usernum = CHART_BAR_MAX_USER_NUM;
    $cnt = 0;
    $num = 0;
    $charts = array();
    while ($num < $array_num) {
        //            
        $us_wrk = array();
        $ok_wrk = array();
        $er_wrk = array();

        $stop_i = $max_usernum;
        if (($cnt+1)*$max_usernum > $array_num) {
            $stop_i = $array_num % $max_usernum;
        }

        for ($i=0; $i<$stop_i; $i++) {
            $us_wrk[] = $us_srs[$num];
            $ok_wrk[] = $ok_srs[$num];
            $er_wrk[] = $er_srs[$num];
            $num++;
        }
        for ($i=$stop_i; $i<$max_usernum; $i++) {
            $us_wrk[] = '';
            $ok_wrk[] = 0;
            $er_wrk[] = 0;
        }

        //
        $chart = new \core\chart_bar();
        $chart->set_horizontal(true);
        $chart->set_stacked(true);
        $chart->set_labels($us_wrk);
        $chart->add_series(new \core\chart_series('OK',    $ok_wrk));
        $chart->add_series(new \core\chart_series('ERROR', $er_wrk));
        if ($dashboard or $cnt>0) {
            if (method_exists($chart, 'set_legend_options')) $chart->set_legend_options(['display' => false]);
        }
        //
        if ($maxval>0) {
            $xaxis = $chart->get_xaxis(0, true);
            $xaxis->set_max($maxval);
            $xaxis->set_stepsize($stepsz);
        }
        //
        $charts[] = $chart;

        $cnt++;
        if ($dashboard) break;
    }

    return $charts;
}



//
// 課題毎の活動状況
//
function  chart_codecell_bar($recs, $username, $filename, $minstance, $dashboard=false)
{
    $code_data = array();
    //
    $exclsn = false;
    foreach ($recs as $rec) {
        if (empty($rec->filename))  $rec->filename = CHART_NULL_FILENAME; 
        if (empty($rec->username))  $rec->username = CHART_NULL_USERNAME; 
        if (is_null($rec->codenum)) $rec->codenum  = CHART_NULL_CODENUM; 
        //
        if ($username!=='*' and $rec->username!==$username) $exclsn = true;
        if (!$exclsn) {
            if ($filename!=='*' and $rec->filename!==$filename) $exclsn = true; 
        }
        //
        if (!$exclsn) {
            $codenum = $rec->codenum;
            if(!array_key_exists($codenum, $code_data)) {
                $code_data[$codenum] = ['ok'=>0, 'er'=>0];
            }
            if      ($rec->status == 'ok')    $code_data[$codenum]['ok']++;
            else if ($rec->status == 'ok/nc') $code_data[$codenum]['ok']++; // no client (cell) data
            else                              $code_data[$codenum]['er']++;
        }
        $exclsn = false;
    }
    //ksort($code_data, SORT_STRING);
    ksort($code_data);

    //
    // all data
    $maxval = 0;
    $cd_srs = array();
    $ok_srs = array();
    $er_srs = array();
    //
    foreach ($code_data as $codenum => $data) {
        if ($dashboard) $cd_srs[] = '';
        else if ($codenum!==CHART_NULL_CODENUM) $cd_srs[] = substr('00'.$codenum, -3, 3);
        else $cd_srs[] = $codenum;
        $ok_srs[] = $data['ok'];
        $er_srs[] = $data['er'];
        if ($maxval < $data['ok'] + $data['er']) $maxval = $data['ok'] + $data['er'];
    }
    if ($maxval>0) {
        $stepsz = ceil($maxval/5);
        $pw     = 10**(strlen($stepsz)-1);
        $stepsz = floor($stepsz/$pw)*$pw;
        if ($stepsz==0) $stepsz = 1; 
        $maxval = (floor($maxval/$stepsz) + 1)*$stepsz;
    }
    //
    $array_num = count($cd_srs);
    if ($array_num==0) {
        $cd_srs[] = '';
        $ok_srs[] = 0;
        $er_srs[] = 0;
        $array_num = 1;
    }

    ////////////////////////////
    $max_codenum = $minstance->chart_bar_codenum;
    if ($max_codenum <= 0) $max_codenum = CHART_BAR_MAX_CODE_NUM;
    $cnt = 0;
    $num = 0;
    $charts = array();
    while ($num < $array_num) {
        //            
        $cd_wrk = array();
        $ok_wrk = array();
        $er_wrk = array();

        $stop_i = $max_codenum;
        if (($cnt+1)*$max_codenum > $array_num) {
            $stop_i = $array_num % $max_codenum;
        }

        for ($i=0; $i<$stop_i; $i++) {
            $cd_wrk[] = $cd_srs[$num];
            $ok_wrk[] = $ok_srs[$num];
            $er_wrk[] = $er_srs[$num];
            $num++;
        }
        for ($i=$stop_i; $i<$max_codenum; $i++) {
            $cd_wrk[] = '';
            $ok_wrk[] = 0;
            $er_wrk[] = 0;
        }

        //
        $chart = new \core\chart_bar();
        $chart->set_horizontal(true);
        $chart->set_stacked(true);
        $chart->set_labels($cd_wrk);
        $chart->add_series(new \core\chart_series('OK',    $ok_wrk));
        $chart->add_series(new \core\chart_series('ERROR', $er_wrk));
        if ($dashboard or $cnt>0) {
            if (method_exists($chart, 'set_legend_options')) $chart->set_legend_options(['display' => false]);
        }
        //
        if ($maxval>0) {
            $xaxis = $chart->get_xaxis(0, true);
            $xaxis->set_max($maxval);
            $xaxis->set_stepsize($stepsz);
        }
        //
        $charts[] = $chart;

        $cnt++;
        if ($dashboard) break;
    }

    return $charts;
}



//
// ユーザ毎の課題進捗状況
//
function  chart_codecell_line($recs, $username, $filename, $minstance, $dashboard=false)
{
    $date_data = array();
    $usernames = array();
    //
    $exclsn = false;
    foreach ($recs as $rec) {
        if (empty($rec->filename))  $rec->filename = CHART_NULL_FILENAME; 
        if (empty($rec->username))  $rec->username = CHART_NULL_USERNAME; 
        if (is_null($rec->codenum)) $rec->codenum  = CHART_NULL_CODENUM; 
        //
        if ($username!=='*' and $rec->username!==$username) $exclsn = true;
        if (!$exclsn) {
            if ($filename!=='*' and $rec->filename!==$filename) $exclsn = true; 
        }
        //
        if (!$exclsn) {
            $date = get_tz_date_str($rec->date, PHP_DATETIME_FMT);
            if(!array_key_exists($date, $date_data)) {
                $date_data[$date] = array();
            }
            if(!array_key_exists($rec->username, $date_data[$date])) {
                $date_data[$date][$rec->username] = array();
            }
            $date_data[$date][$rec->username]['codecell'] = $rec->codenum;

            $usernames[$rec->username] = $rec->username;
        }
        $exclsn = false;
    }
    ksort($date_data);
    ksort($usernames);

    //
    // all data
    $dt_srs = array();
    $tm_srs = array();
    $us_srs = array();
    foreach ($usernames as $uname) {
        $us_srs[$uname] = array();
    }
    //
    $max_interval = $minstance->chart_line_interval;
    if ($max_interval <= 0) $max_interval = CHART_LINE_MAX_INTERVAL;
    $max_codenum = 0;

    $i = 0;
    foreach ($date_data as $dt => $users) {
        if ($dashboard) $dt_srs[$i] = '';
        else            $dt_srs[$i] = (new DateTime($dt))->format('m/d H:i');
        $tm_srs[$i] = strtotime($dt);

        foreach ($usernames as $uname) {
            if(!array_key_exists($uname, $users)) {
                if ($i>0 and $tm_srs[$i]-$tm_srs[$i-1]<$max_interval) $us_srs[$uname][$i] = $us_srs[$uname][$i-1]; 
                else                                                  $us_srs[$uname][$i] = null; 
            }
            else {
                $cellcode = $users[$uname]['codecell']; 
                if ($cellcode==CHART_NULL_CODENUM) {
                    if ($i>0 and $tm_srs[$i]-$tm_srs[$i-1]<$max_interval) $us_srs[$uname][$i] = $us_srs[$uname][$i-1]; 
                    else                                                  $us_srs[$uname][$i] = null; 
                }
                else {
                    $codenum = $users[$uname]['codecell']; 
                    $us_srs[$uname][$i] = $codenum; 
                    if ($codenum > $max_codenum) $max_codenum = $codenum;
                }
            }
        }
        $i++;
    }

    $array_num = count($us_srs);
    if ($array_num==0) {
        $us_srs[null] = array(null, null);
        $dt_srs[] = date('m/d H:i', time() - 24*3600);
        $dt_srs[] = date('m/d H:i', time());
        $array_num = 1;
    }

    ////////////////////////////
    $max_usernum = $minstance->chart_line_usernum;
    if ($max_usernum <=0) $max_usernum = CHART_LINE_MAX_USER_NUM;

    $cnt = 0;
    $num = 0;
    $charts = array();
    while ($num < $array_num) {
        //            
        $us_wrk = array();

        $stop_i = $max_usernum;
        if (($cnt+1)*$max_usernum > $array_num) {
            $stop_i = $array_num % $max_usernum;
        }

        for ($i=0; $i<$stop_i; $i++) {
            $usr = array_slice($us_srs, $num, 1, true);
            $us_wrk[key($usr)] = current($usr);
            $num++;
        }

        $chart = new \core\chart_line();
        $chart->set_labels($dt_srs);
        foreach ($us_wrk as $uname => $val) {
            $coords = new \core\chart_series($uname, $val);
            $chart->add_series($coords);
        }
        $yaxis = $chart->get_yaxis(0, true);
        $yaxis->set_min(0);
        $yaxis->set_max($max_codenum + 1);
        $yaxis->set_stepsize(1);
        if (!$dashboard) {
            $yaxis->set_label("Code Cell No.");
        }
        else {
            if (method_exists($chart, 'set_legend_options')) $chart->set_legend_options(['display' => false]);
        }

        $charts[] = $chart;

        $cnt++;
        if ($dashboard) break;
   }

    return $charts;
}

