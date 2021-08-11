<?php

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once(dirname(__FILE__).'/classes/mdlds_webservice_handler.php');


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


    //
    public static function write_nb_data($data)
    {
        return "HeroHero";
    }


    //
    public static function write_nb_data_parameters()
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'course id'),
            )
        );
    }


    //
    public static function write_nb_data_returns()
    {
        //return new external_single_structure(array());
        return new external_value(PARAM_TEXT, '....');
    }

}
