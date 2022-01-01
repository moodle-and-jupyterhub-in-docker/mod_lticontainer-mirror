<?php
/**
 * Use Moodle's Charts API to visualize learning data.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__.'/locallib.php');


define('CHART_BAR_MAX_USER_NUM',  10);
define('CHART_BAR_MAX_CODE_NUM',  15);
define('CHART_LINE_MAX_INTERVAL', 1800);       // 1800s
define('CHART_LINE_MAX_USER_NUM', 15);

define('CHART_NULL_FILENAME',     'unknown');
define('CHART_NULL_CODENUM',      'null');



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
    $charts_data[1]->title  = 'Activities per User';

    $charts_data[2] = new StdClass();
    $charts_data[2]->charts = chart_codecell_bar($recs, '*', '*', true);
    $charts_data[2]->kind   = 'codecell_bar';
    $charts_data[2]->title  = 'Activities per Code Cell';

    $charts_data[3] = new StdClass();
    $charts_data[3]->charts = chart_codecell_line($recs, '*', '*', true);
    $charts_data[3]->kind   = 'codecell_line';
    $charts_data[3]->title  = 'User Progress on the Task';

    return $charts_data;
}



function  chart_total_pie($recs, $username, $filename, $dashboard=false)
{
    $ok = 0;
    $er = 0;

    $exclsn = false;
    foreach ($recs as $rec) {
        if (empty($rec->filename)) $rec->filename = CHART_NULL_FILENAME; 
        //
        if ($username!=='*' and $rec->username!==$username) $exclsn = true;
        if (!$exclsn) {
            if ($filename!=='*' and $rec->filename!==$filename) $exclsn = true;
        }
        //
        if (!$exclsn) {
            if ($rec->status == 'ok') $ok++;
            else                      $er++;
        }
        $exclsn = false;
    }

    // Total(Known + Unknown) activities
    $series = new \core\chart_series('Count', [$ok, $er]);
    $labels = ['OK', 'ERROR'];
    $chart  = new \core\chart_pie();
    $chart->add_series($series);
    $chart->set_labels($labels);
    if ($dashboard) $chart->set_legend_options(['display' => false]);
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
        if (empty($rec->filename)) $rec->filename = CHART_NULL_FILENAME;
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
            if ($rec->status == 'ok') $user_data[$uname]['ok']++;
            else                      $user_data[$uname]['er']++;
        }
        $exclsn = false;
    }

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
        $stepsz = ceil($maxval/4);
        $pw     = 10**(strlen($stepsz)-1);
        $stepsz = floor($stepsz/$pw)*$pw;
        if ($stepsz==0) $stepsz = 1; 
        $maxval = $stepsz*5;
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
    $cnt = 0;
    $num = 0;
    $charts = array();
    while ($num < $array_num) {
        //            
        $us_wrk = array();
        $ok_wrk = array();
        $er_wrk = array();

        $stop_i = CHART_BAR_MAX_USER_NUM;
        if (($cnt+1)*CHART_BAR_MAX_USER_NUM > $array_num) {
            $stop_i = $array_num % CHART_BAR_MAX_USER_NUM;
        }

        for ($i=0; $i<$stop_i; $i++) {
            $us_wrk[] = $us_srs[$num];
            $ok_wrk[] = $ok_srs[$num];
            $er_wrk[] = $er_srs[$num];
            $num++;
        }
        for ($i=$stop_i; $i<CHART_BAR_MAX_USER_NUM; $i++) {
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
        if ($dashboard or $cnt>0) $chart->set_legend_options(['display' => false]);
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
function  chart_codecell_bar($recs, $username, $filename, $dashboard=false)
{
    $code_data = array();
    //
    $exclsn = false;
    foreach ($recs as $rec) {
        if (empty($rec->filename))  $rec->filename = CHART_NULL_FILENAME; 
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
            if ($rec->status == 'ok') $code_data[$codenum]['ok']++;
            else                      $code_data[$codenum]['er']++;
        }
        $exclsn = false;
    }
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
        $stepsz = ceil($maxval/4);
        $pw     = 10**(strlen($stepsz)-1);
        $stepsz = floor($stepsz/$pw)*$pw;
        if ($stepsz==0) $stepsz = 1; 
        $maxval = $stepsz*5;
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
    $cnt = 0;
    $num = 0;
    $charts = array();
    while ($num < $array_num) {
        //            
        $cd_wrk = array();
        $ok_wrk = array();
        $er_wrk = array();

        $stop_i = CHART_BAR_MAX_CODE_NUM;
        if (($cnt+1)*CHART_BAR_MAX_CODE_NUM > $array_num) {
            $stop_i = $array_num % CHART_BAR_MAX_CODE_NUM;
        }

        for ($i=0; $i<$stop_i; $i++) {
            $cd_wrk[] = $cd_srs[$num];
            $ok_wrk[] = $ok_srs[$num];
            $er_wrk[] = $er_srs[$num];
            $num++;
        }
        for ($i=$stop_i; $i<CHART_BAR_MAX_CODE_NUM; $i++) {
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
        if ($dashboard or $cnt>0) $chart->set_legend_options(['display' => false]);
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
function  chart_codecell_line($recs, $username, $filename, $dashboard=false)
{
    $date_data = array();
    $usernames = array();
    //
    $exclsn = false;
    foreach ($recs as $rec) {
        if (empty($rec->filename))  $rec->filename = CHART_NULL_FILENAME; 
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
    $i = 0;
    foreach ($date_data as $dt => $users) {
        if ($dashboard) $dt_srs[$i] = '';
        else            $dt_srs[$i] = (new DateTime($dt))->format('m/d H:i');
        $tm_srs[$i] = strtotime($dt);

        foreach ($usernames as $uname) {
            if(!array_key_exists($uname, $users)) {
                if ($i>0 and $tm_srs[$i]-$tm_srs[$i-1]<CHART_LINE_MAX_INTERVAL) $us_srs[$uname][$i] = $us_srs[$uname][$i-1]; 
                else                                                            $us_srs[$uname][$i] = null; 
            }
            else {
                $cellcode = $users[$uname]['codecell']; 
                if ($cellcode==CHART_NULL_CODENUM) {
                    if ($i>0 and $tm_srs[$i]-$tm_srs[$i-1]<CHART_LINE_MAX_INTERVAL) $us_srs[$uname][$i] = $us_srs[$uname][$i-1]; 
                    else                                                            $us_srs[$uname][$i] = null; 
                }
                else {
                    $us_srs[$uname][$i] = $users[$uname]['codecell']; 
                }
            }
        }
        $i++;
    }

    ////////////////////////////
    $array_num = count($us_srs);
    $cnt = 0;
    $num = 0;
    $charts = array();
    while ($num < $array_num) {
        //            
        $us_wrk = array();

        $stop_i = CHART_LINE_MAX_USER_NUM;
        if (($cnt+1)*CHART_LINE_MAX_USER_NUM > $array_num) {
            $stop_i = $array_num % CHART_LINE_MAX_USER_NUM;
        }

        for ($i=0; $i<$stop_i; $i++) {
            $udt = array_slice($us_srs, $num, 1, true);
            $us_wrk[key($udt)] = current($udt);
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
        if ($dashboard) $chart->set_legend_options(['display' => false]);

        $charts[] = $chart;

        $cnt++;
        if ($dashboard) break;
   }

    return $charts;
}

