<?php

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once(dirname(__FILE__).'/classes/ltids_webservice_handler.php');


class mod_ltids_external extends external_api 
{
    /**
     * Get parameter list.
     * @return external_function_parameters
     */
    public static function get_ltids()
    {
        return "HELL World!!";
    }


    /**
     * Get list of courses with active sessions for today.
     * @param int $userid
     * @return array
     */
    public static function get_ltids_handler($userid)
    {
        return ltids_handler::get_ltids_handler($userid);
    }




    /**
    $data->host = ....;
    $data->session = ....;
    $params = array($data);
    $post = xmlrpc_encode_request($functionname, array($params));
    */
    public static function write_nb_data($data)
    {
        global $CFG, $DB;

        $params = self::validate_parameters(self::write_nb_data_parameters(), array('nb_data'=>$data));

        foreach ($params['nb_data'] as $nb_data) {
            $nb_data = (object)$nb_data;
            file_put_contents("/xtmp/ZZ", $nb_data->host."\n", FILE_APPEND);
            file_put_contents("/xtmp/ZZ", $nb_data->session."\n", FILE_APPEND);
        }

        return $data;
    }



    //
    public static function write_nb_data_parameters()
    {
        return new external_function_parameters(
            array (
                'nb_data' => new external_multiple_structure (
                    new external_single_structure(
                        array (
                            'host' => new external_value(PARAM_TEXT, 'server or client'),
                            'session' => new external_value(PARAM_TEXT, 'session id'),
                        )
                    )
                )
            )
        );
    }


    //
    public static function write_nb_data_returns()
    {
        return new external_multiple_structure(
            new external_single_structure(
                array (
                    'host' => new external_value(PARAM_TEXT, 'server or client'),
                    'session' => new external_value(PARAM_TEXT, 'session id'),
                )
            )
        );
    }

}
