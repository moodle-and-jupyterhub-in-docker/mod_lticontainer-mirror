<?php

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
//require_once(dirname(__FILE__).'/classes/ltids_webservice_handler.php');


//
// see https://docs.moodle.org/dev/Adding_a_web_service_to_a_plugin#Deprecation
//


class mod_ltids_external extends external_api 
{
    /**
     * Get list of courses with active sessions for today.
     * @param int $userid
     * @return array
     */

    /**
    client side:
        $data->host = ....;
        $data->session = ....;
        $params = array($data);
        $post = xmlrpc_encode_request($functionname, $params);
    */
    public static function write_nb_data($data)
    {
        global $DB;

        $param = self::validate_parameters(self::write_nb_data_parameters(), array($data));
        $nb_data = (object)$param[0];
        $nb_data->updatetm = time();

        //file_put_contents('/xtmp/ZZ', "-----------------------------\n", FILE_APPEND);
        //file_put_contents('/xtmp/ZZ', 'lti_id = '. $nb_data->lti_id."\n", FILE_APPEND);

        if ($nb_data->host=='server') {
            $condition = array('host'=>'client', 'session'=>$nb_data->session, 'message'=>$nb_data->message);
            $recs = $DB->get_records('ltids_websock_data', $condition);
            if ($recs) {
                $DB->insert_record('ltids_websock_data', $nb_data);
            }
        }
        else if ($nb_data->host=='client') {
            $DB->insert_record('ltids_websock_data', $nb_data);
            if ($nb_data->tags!='') {
                $rec = $DB->get_record('ltids_websock_tags', array('cell_id'=>$nb_data->cell_id)); 
                if (!$rec) {
                    $DB->insert_record('ltids_websock_tags', $nb_data);
                }
            }
        }
        else {  // fesvr: cookie
            if ($nb_data->lti_id!='') {
                $rec = $DB->get_record('ltids_websock_session', array('session'=>$nb_data->session)); 
                if (!$rec) {
                    $rec = $DB->get_record('lti', array('id'=>$nb_data->lti_id), 'course'); 
                    $nb_data->course = $rec->course;
                    $DB->insert_record('ltids_websock_session', $nb_data);
                }
            }
        }

        return $nb_data;
    }



    //
    public static function write_nb_data_parameters()
    {
        return new external_function_parameters(
            array (
                new external_single_structure(
                    array(
                        'host'     => new external_value(PARAM_TEXT, 'server or client'),
                        'inst_id'  => new external_value(PARAM_TEXT, 'id of mod_ltids instance'),
                        'lti_id'   => new external_value(PARAM_TEXT, 'id of LTI module instance'),
                        'session'  => new external_value(PARAM_TEXT, 'id of session'),
                        'message'  => new external_value(PARAM_TEXT, 'id of message'),
                        'status'   => new external_value(PARAM_TEXT, 'status of jupyter'),
                        'username' => new external_value(PARAM_TEXT, 'user name'),
                        'cell_id'  => new external_value(PARAM_TEXT, 'id of cell'),
                        'tags'     => new external_value(PARAM_TEXT, 'tags of cell'),
                        'date'     => new external_value(PARAM_TEXT, 'date'),
                    )
                )
            )
        );
    }


    //
    public static function write_nb_data_returns()
    {
        return new external_single_structure(
            array (
                //'session' => new external_value(PARAM_TEXT, 'session id'),
            )
        );
    }

}
