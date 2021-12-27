<?php
/**
 * Local DB Library for retrieving data stored in the DB.
 *
 * @package     mod_ltids
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp> and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

function  _get_session_table_sql($course, $lti_id, $start_date, $end_date)
{
    global $CFG;
   
    $session_table = $CFG->prefix.SESSION_TABLE;

    $sql  = 'SELECT session, course, inst_id, lti_id, updatetm FROM '.$session_table;
    $sql .= ' WHERE course = '.$course;

    // LTI ID
    if (empty($lti_id)) $lti_id = '*';

    if (is_array($lti_id)) {
        $sql .= ' AND (';
        $i = 0;
        foreach($lti_id as $id) {
            if ($i==0) $sql .= ' lti_id = '.$id;
            else       $sql .= ' OR  lti_id = '.$id;
            $i++;
        }
        $sql .= ' )';
    }
    else {
        if ($lti_id !== '*') {
            $sql .= ' AND lti_id = '.$lti_id;
        }
    }











    return $sql;
}



function  _get_data_table_sub_sql($host, $course, $lti_id, $start_date, $end_date)
{ 
    global $CFG;

    $data_table = $CFG->prefix.DATA_TABLE;
    $session_table_sql =  _get_session_table_sql($course, $lti_id, $start_date, $end_date);

    $sql = 'SELECT session, message, status, username, date FROM ( '.$session_table_sql.' ) SESSION ';
    if ($host=='server') $sql .= ' WHERE host = \'server\'';
    else                 $sql .= ' WHERE host = \'client\'';

    $sql .= ' AND SESSION.session = '.$data_table.'.session';

    return $sql;
}



function  _get_data_table_sql($course, $lti_id, $start_date, $end_date)
{
    $server = _get_data_table_sub_sql('server', $course, $lti_id, $start_date, $end_date);
    $client = _get_data_table_sub_sql('client', $course, $lti_id, $start_date, $end_date);

    $sql  = 'SELECT username, cell_id, status, C.date AS c_date, S.date AS s_date, C.session';
    $sql .= ' FROM ( '.$client.' ) C, ( '.$server.' ) S WHERE C.message = S.message';

    return $sql;
}



function  _get_tags_table_sql($course, $lti_id, $start_date, $end_date)
{
    global $CFG;
   
    $tags_table = $CFG->prefix.TAGS_TABLE;
    $data_table_sql = _get_data_table_sql($course, $lti_id, $start_date, $end_date);

    $sql  = 'SELECT username, tags, status, c_date, s_date, session FROM ( '.$data_table_sql.' ) CS';
    $sql .= ' LEFT OUTER JOIN '.$tags_table.' ON CS.cell_id = '.$tags_table.'.cell_id ';

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



//
// DB Tools
//

//
// DBのtagsの内容をPHPのオブジェクト(stdClass)に変換してそれを返す
// ※ filename, codenumの情報だけ抜く
// ※ それ以外の値は無視
//
// tagsの内容
// ["filename: 1-2.ipynb","codenum: 0"]
// ["raises-exception","filename: 1-4.ipynb","codenum: 1"]
//
function decode_tags($tags)
{
    if(empty($tags)) return NULL;

    // \"property: value\" の形式の文字列を $tags から探してきて，
    // それを以下のようなPHPの配列に変換する。
    // 結果は $matches に格納
    // ※ : の前後のスペースはあってもなくても結果には影響しない
    // Array
    // (
    //     [0] => Array
    //         (
    //             [0] => "property: value"
    //             [1] => property
    //             [2] => value
    //         )
    //
    //     [1] => Array
    //         (
    //             [0] => "property: value"
    //             [1] => property
    //             [2] => value
    //         )
    // )
    //
    $properties = 'filename|codenum'; // l(小文字のエル)と|(パイプ)は要注意
    $patterns   = "/\"(${properties})\s*:\s*([^\s\"]+)\"/u";
    preg_match_all($patterns, $tags, $matches, PREG_SET_ORDER);

    if(empty($matches)) return NULL;

    // $matches を元にして，オブジェクトを構築
    $result = new stdClass();
    foreach($matches as $match) {
        $result->{$match[1]} = $match[2];
    }

    return $result;
}
