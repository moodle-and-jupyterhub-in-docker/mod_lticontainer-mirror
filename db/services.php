<?php

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'mod_lticontainer_write_nbdata' => array(               // Service Function Name
        'classname'     => 'mod_lticontainer_external',     // 
        'methodname'    => 'write_nb_data',                 // External Function Name
        'description'   => 'Write Jupyter Lab/Notebook data to DB',
        'type'          => 'write',
        'capabilities'  => 'mod/lticontainer:db_write',
    ),
);


$services = array(
    'Jupyter Lab/Notebook Data' => array(                   // Service Name
        'functions' => array(
            'mod_lticontainer_write_nbdata',                // Service Function Name
        ),
        'restrictedusers' => 1,
        'enabled' => 1,
        'shortname' => 'moodle_ds'
    )
);
