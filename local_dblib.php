<?php
/**
 * Local DB Library for retrieving data stored in the DB.
 *
 * @package     mod_lticontainer
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp> and Fumi.Iseki
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__.'/local_lib.php');


define('DATA_TABLE',        'lticontainer_data');
define('SERVER_TABLE',      'lticontainer_server_data');
define('CLIENT_TABLE',      'lticontainer_client_data');
define('SESSION_TABLE',     'lticontainer_session');
define('TAGS_TABLE',        'lticontainer_tags');



function  db_get_lti_module_id()
{
    global $DB;

    $ltimod = $DB->get_record('modules', array('name' => 'lti'));
    if (!$ltimod) return 0;
    
    return $ltimod->id;
}


function  db_instance_is_delprgs($courseid, $moduleid, $instanceid)
{
    global $DB;

    $ltimod = $DB->get_record('course_modules', array('course' => $courseid, 'module'=>$moduleid, 'instance'=>$instanceid));
    if (!$ltimod or $ltimod->deletioninprogress==1) return true;

    return false;
}


function  db_get_valid_ltis($courseid, $sort = '', $fields = '*')
{
    global $DB;

    $ltis = $DB->get_records('lti', array('course' => $courseid), $sort, $fields);
    $lti_modid = db_get_lti_module_id(); 

    foreach ($ltis as $key => $lti) {
        if (db_instance_is_delprgs($courseid, $lti_modid, $lti->id)) unset($ltis[$key]);
    }

    return $ltis;
}


function  db_get_disp_ltis($courseid, $minstance, $sort = '')
{
    //global $DB;

    $fields = 'id, name, instructorcustomparameters';
    $ltis   = db_get_valid_ltis($courseid, $sort, $fields);
    //$ltis   = $DB->get_records('lti', array('course' => $courseid), $sort, $fields);

    $disp = explode(',', $minstance->display_lti);
    foreach ($ltis as $key => $lti) {
        if (!in_array($lti->id, $disp)) unset($ltis[$key]);
    }

    return $ltis;
}



//
// Get SQL
//

function  get_base_sql($courseid, $start_date, $end_date)
{
    global $CFG;
   
    $server_table  = $CFG->prefix.SERVER_TABLE;
    $client_table  = $CFG->prefix.CLIENT_TABLE;
    $session_table = $CFG->prefix.SESSION_TABLE;
    $tags_table    = $CFG->prefix.TAGS_TABLE;

    // server data
    $select  = 'SELECT SERVER.date, SERVER.updatetm, username, status';
    $from    = ' FROM '.$server_table.' SERVER'; 
    $join    = '';

    // client data
    $select .= ', CLIENT.cell_id';
    $join    = ' INNER JOIN '.$client_table. ' CLIENT ON SERVER.message = CLIENT.message';
    $join   .= ' AND SERVER.updatetm >= '.strtotime($start_date);
    $join   .= ' AND SERVER.updatetm <= '.strtotime($end_date);

    // session
    $select .= ', SESSION.lti_id, SESSION.course';
    $join   .= ' INNER JOIN '.$session_table. ' SESSION ON SERVER.session = SESSION.session';

    // tags
    $select .= ', TAGS.filename, TAGS.codenum';
    //$join   .= ' LEFT OUTER JOIN '.$tags_table. ' TAGS ON CLIENT.cell_id = TAGS.cell_id';
    $join   .= ' LEFT OUTER JOIN '.$tags_table. ' TAGS ON CLIENT.cell_id = TAGS.cell_id AND (CLIENT.filename = TAGS.filename OR CLIENT.filename = "")';

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

