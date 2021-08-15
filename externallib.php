<?php

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once(dirname(__FILE__).'/classes/mdlds_webservice_handler.php');


//
// see https://docs.moodle.org/dev/Adding_a_web_service_to_a_plugin#Deprecation
//


class mod_mdlds_external extends external_api 
{
    /**
     * Get parameter list.
     * @return external_function_parameters
     */
    public static function get_mdlds()
    {
        return "HELL World!!";
    }


    /**
     * Get list of courses with active sessions for today.
     * @param int $userid
     * @return array
     */
    public static function get_mdlds_handler($userid)
    {
        return mdlds_handler::get_mdlds_handler($userid);
    }



    /**
    client side:
        $data->host = ....;
        $data->session = ....;
        $params = array($data);
        $post = xmlrpc_encode_request($functionname, $params);
    */

    public static function write_nb_data($data)
    {
        global $CFG, $DB;

        $param = self::validate_parameters(self::write_nb_data_parameters(), array($data));
    
        $nb_data = (object)$param[0];
        file_put_contents("/xtmp/ZZ", "------------------------\n", FILE_APPEND);
        file_put_contents("/xtmp/ZZ", 'host     = '.$nb_data->host."\n", FILE_APPEND);
        file_put_contents("/xtmp/ZZ", 'lti_id   = '.$nb_data->lti_id."\n", FILE_APPEND);
        file_put_contents("/xtmp/ZZ", 'session  = '.$nb_data->session."\n", FILE_APPEND);
        file_put_contents("/xtmp/ZZ", 'message  = '.$nb_data->message."\n", FILE_APPEND);
        file_put_contents("/xtmp/ZZ", 'status   = '.$nb_data->status."\n", FILE_APPEND);
        file_put_contents("/xtmp/ZZ", 'username = '.$nb_data->username."\n", FILE_APPEND);
        file_put_contents("/xtmp/ZZ", 'cell_id  = '.$nb_data->cell_id."\n", FILE_APPEND);
        file_put_contents("/xtmp/ZZ", 'tags     = '.$nb_data->tags."\n", FILE_APPEND);
        file_put_contents("/xtmp/ZZ", 'date     = '.$nb_data->date."\n", FILE_APPEND);

        return $data;
    }



    //
    public static function write_nb_data_parameters()
    {
        return new external_function_parameters(
            array (
                new external_single_structure(
                    array(
                        'host' => new external_value(PARAM_TEXT, 'server or client'),
                        'lti_id' => new external_value(PARAM_TEXT, 'id of LTI module instance'),
                        'session' => new external_value(PARAM_TEXT, 'id of session'),
                        'message' => new external_value(PARAM_TEXT, 'id of message'),
                        'status' => new external_value(PARAM_TEXT, 'status of jupyter'),
                        'username' => new external_value(PARAM_TEXT, 'user name'),
                        'cell_id' => new external_value(PARAM_TEXT, 'id of cell'),
                        'tags' => new external_value(PARAM_TEXT, 'tags of cell'),
                        'date' => new external_value(PARAM_TEXT, 'date'),
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
                'host' => new external_value(PARAM_TEXT, 'server or client'),
                'session' => new external_value(PARAM_TEXT, 'session id'),
            )
        );
    }

}
