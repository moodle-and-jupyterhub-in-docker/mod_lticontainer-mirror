<?php
/**
 * Local DB Library for retrieving data stored in the DB.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp> and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

define('DATA_TABLE',        'ltids_websock_data');
define('SERVER_TABLE',      'ltids_websock_server_data');
define('CLIENT_TABLE',      'ltids_websock_client_data');
define('SESSION_TABLE',     'ltids_websock_session');
define('TAGS_TABLE',        'ltids_websock_tags');

define('SQL_DATETIME_FMT',  '%Y-%m-%dT%T.%fZ');
//define('PHP_DATETIME_FMT',  'Y-m-d\TH:i:s.u\Z');
define('PHP_DATETIME_FMT',  '%Y-%m-%d %H:%i');



function  db_get_valid_ltis($courseid, $minstance, $sort = '')
{
    global $DB;

    $fields = 'id, name, instructorcustomparameters';
    $ltis   = $DB->get_records('lti', array('course' => $courseid), $sort, $fields);

    $nodisp = explode(',', $minstance->no_disp_lti);
    foreach ($ltis as $key => $lti) {
        if (in_array($lti->id, $nodisp)) {
            unset($ltis[$key]);
        }
    }

    return $ltis;
}



function  db_get_valid_users($course, $lti_id, $start_date, $end_date)
{
    global $DB;


}



function  db_get_valid_files($course, $lti_id, $start_date, $end_date)
{
    global $DB;

    $sql = get_course_lti_sql($course, $lti_id, $start_date, $end_date);
    $_records = $DB->get_records_sql($sql);

    $records = [];
    foreach($_records as $record) {
        $record->tags = decode_tags($record->tags);
        $records[] = $record;
    }
    $records = $records;
}



function  db_get_valid_file_code($courseid, $lti_id)
{
    global $DB;

}




//
// make SQL
//



function  get_base_sql($courseid, $start_date, $end_date)
{
    global $CFG;
   
    $server_table  = $CFG->prefix.SERVER_TABLE;
    $client_table  = $CFG->prefix.CLIENT_TABLE;
    $session_table = $CFG->prefix.SESSION_TABLE;
    $tags_table    = $CFG->prefix.TAGS_TABLE;

    // server data
    $select  = 'SELECT SERVER.date, username, status';
    $from    = ' FROM '.$server_table.' SERVER'; 
    $join    = '';

    // client data
    $select .= ', CLIENT.cell_id';
    $join    = ' INNER JOIN '.$client_table. ' CLIENT ON SERVER.message = CLIENT.message';
    $join   .= ' AND STR_TO_DATE(CLIENT.date, \''.SQL_DATETIME_FMT.'\') >= STR_TO_DATE(\''.$start_date.'\', \''.PHP_DATETIME_FMT.'\')';
    $join   .= ' AND STR_TO_DATE(CLIENT.date, \''.SQL_DATETIME_FMT.'\') <= STR_TO_DATE(\''.$end_date.  '\', \''.PHP_DATETIME_FMT.'\')';

    // session
    $select .= ', SESSION.lti_id, SESSION.course';
    $join   .= ' INNER JOIN '.$session_table. ' SESSION ON SERVER.session = SESSION.session';

    // tags
    $select .= ', TAGS.filename, TAGS.codenum';
    $join   .= ' LEFT OUTER JOIN '.$tags_table. ' TAGS ON CLIENT.cell_id = TAGS.cell_id';

    // course
    $where   = ' WHERE course = '.$courseid;

    $sql = $select.$from.$join.$where;

    return $sql;
}


function  get_lti_sql_condition($lti_id = '*') 
{
    if (empty($lti_id)) $lti_id = '*';

    $cnd = '';
    //
    if (is_array($lti_id)) {
        $cnd = ' AND (';
        $i = 0;
        foreach($lti_id as $id) {
            if ($i==0) $cnd .= ' lti_id = '.$id;
            else       $cnd .= ' OR  lti_id = '.$id;
            $i++;
        }
        $cnd .= ' )';
    }
    else {
        if ($lti_id !== '*') {
            $cnd = ' AND lti_id = '.$lti_id;
        }
    }

    return $cnd;
}


















function  get_course_lti_sql($courseid, $lti_id, $start_date, $end_date) 
{
    global $CFG;

    $sql = get_session_table_sql($start_date, $end_date, SQL_DATETIME_FMT);
    //$sql = _get_tags_table_sql($courseid, $lti_id, $start_date, $end_date);

    $output_and = false;

    // course, lti_id が指定されているならSQL末尾に条件追加
    if($courseid !== '*' || $lti_id !== '*')
        $sql .= ' WHERE';

    // course
    if($courseid !== '*') {
        $sql .= ' course = '.$courseid;
        $output_and = true;
    }

    // lti
    if($lti_id !== '*') {
        if($output_and) $sql .= ' AND';
        $sql .= ' lti_id = '.$lti_id;
    }

    $sql .= ';'; // End of SQL

    return $sql;
}



/////////////////////////////////////////////////////////////////////////////////////////////

function  get_data_table_sub_sql($host, $start_date, $end_date, $datetime_fmt)
{ 
    global $CFG;

    $data_table = $CFG->prefix.DATA_TABLE;

    if ($host=='server') $sql = 'SELECT session, message, status, username, date FROM '.$data_table.' WHERE host = \'server\'';
    else                 $sql = 'SELECT session, message, cell_id, date          FROM '.$data_table.' WHERE host = \'client\'';

    $sql .= ' AND STR_TO_DATE(date, \''.$datetime_fmt.'\') >= STR_TO_DATE(\''.$start_date.'\', \''.$datetime_fmt.'\')';  
    $sql .= ' AND STR_TO_DATE(date, \''.$datetime_fmt.'\') <= STR_TO_DATE(\''.$end_date.  '\', \''.$datetime_fmt.'\')';

    return $sql;
}


function  get_data_table_sql($start_date, $end_date, $datetime_fmt)
{
    $server = get_data_table_sub_sql('server', $start_date, $end_date, $datetime_fmt);
    $client = get_data_table_sub_sql('client', $start_date, $end_date, $datetime_fmt);

    $sql  = 'SELECT username, cell_id, status, C.date AS c_date, S.date AS s_date, C.session';
    $sql .= ' FROM ( '.$client.' ) C, ( '.$server.' ) S WHERE C.message = S.message AND C.session = S.session';

    return $sql;
}


function  get_tags_table_sql($start_date, $end_date, $datetime_fmt)
{
    global $CFG;
   
    $tags_table = $CFG->prefix.TAGS_TABLE;
    $data_table_sql = get_data_table_sql($start_date, $end_date, $datetime_fmt);

    $sql  = 'SELECT username, tags, status, c_date, s_date, session FROM ( '.$data_table_sql.' ) CS';
    $sql .= ' LEFT OUTER JOIN '.$tags_table.' ON CS.cell_id = '.$tags_table.'.cell_id ';

    return $sql;
}


function  get_session_table_sql($start_date, $end_date, $datetime_fmt)
{
    global $CFG;
   
    $session_table = $CFG->prefix.SESSION_TABLE;
    $tags_table_sql = get_tags_table_sql($start_date, $end_date, $datetime_fmt);

    $sql  = 'SELECT ROW_NUMBER() OVER(ORDER BY s_date ASC) id, username, tags, status, c_date, s_date, course, lti_id';
    $sql .= ' FROM ( '.$tags_table_sql.' ) CST';
    $sql .= ' LEFT OUTER JOIN '.$session_table.' ON CST.session = '.$session_table.'.session';

    return $sql;
}

