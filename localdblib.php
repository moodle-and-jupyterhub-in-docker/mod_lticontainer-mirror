<?php
/**
 Local DB Library

*/

defined('MOODLE_INTERNAL') || die;

define('DATA_TABLE',       'ltids_websock_data');
define('TAGS_TABLE',       'ltids_websock_tags');
define('SESSION_TABLE',    'ltids_websock_session');

define('SQL_DATETIME_FMT', '%Y-%m-%dT%T.%fZ');
define('PHP_DATETIME_FMT', 'Y-m-d\TH:i:s.u\Z');



function  db_get_valid_ltis($courseid, $minstance, $sort = '')
{
    global $DB;

    $nodisp = explode(',', $minstance->no_disp_lti);
    $fields = 'id, name, instructorcustomparameters';
    $ltis   = $DB->get_records('lti', array('course' => $courseid), $sort, $fields);

    foreach ($ltis as $key => $lti) {
        if (in_array($lti->id, $nodisp)) {
            unset($ltis[$key]);
        }
    }

    return $ltis;
}



function  db_get_valid_users($courseid, $lti_id)
{
    global $DB;



}



function  db_get_valid_files($courseid, $lti_id)
{
    global $DB;



}



function  db_get_valid_file_code($courseid, $lti_id)
{
    global $DB;

}








function  make_sql($courseid, $lti_id, $start_date, $end_date='*') 
{
    global $CFG, $DB;

    $data_table    = $CFG->prefix.DATA_TABLE;
    $tags_table    = $CFG->prefix.TAGS_TABLE;
    $session_table = $CFG->prefix.SESSION_TABLE;
    $datetime_fmt  = SQL_DATETIME_FMT;

    //
    $sql = <<<SQL

SELECT
  ROW_NUMBER() OVER(ORDER BY s_date ASC) id,
  username,
  tags,
  status,
  c_date,
  s_date,
  course,
  lti_id
FROM
  (
    SELECT
      username,
      tags,
      status,
      c_date,
      s_date,
      session
    FROM
      (
        SELECT
          username,
          cell_id,
          status,
          C.date AS c_date,
          S.date AS s_date,
          C.session
        FROM
          (
            SELECT
              session,
              message,
              cell_id,
              date
            FROM
              $data_table
            WHERE
              host = 'client'
            AND STR_TO_DATE(date, '$datetime_fmt') >= STR_TO_DATE('$start_date', '$datetime_fmt')
            AND STR_TO_DATE(date, '$datetime_fmt') <= STR_TO_DATE('$end_date',   '$datetime_fmt')
          ) C,
          (
            SELECT
              session,
              message,
              status,
              username,
              date
            FROM
              $data_table
            WHERE
              host = 'server'
            AND STR_TO_DATE(date, '$datetime_fmt') >= STR_TO_DATE('$start_date', '$datetime_fmt')
            AND STR_TO_DATE(date, '$datetime_fmt') <= STR_TO_DATE('$end_date',   '$datetime_fmt')
          ) S
        WHERE
          C.message = S.message
        AND C.session = S.session
      ) CS1
      LEFT OUTER JOIN $tags_table
      ON  CS1.cell_id = $tags_table.cell_id
  ) CS2
  LEFT OUTER JOIN $session_table
  ON  CS2.session = $session_table.session

SQL;

    $output_and = false;

    // course, lti_id が指定されているならSQL末尾に条件追加
    if($courseid !== '*' || $lti_id !== '*')
        $sql .= 'WHERE'.PHP_EOL;

    // course
    if($courseid !== '*') {
        $sql .= '  course = '.$courseid.PHP_EOL;
        $output_and = true;
    }

    // lti
    if($lti_id !== '*') {
        if($output_and)
            $sql .= 'AND ';
        else
            $sql .= '  ';
        $sql .= 'lti_id = '.$lti_id.PHP_EOL;
    }

    $sql .= ';'; // End of SQL

    return $sql;
}

