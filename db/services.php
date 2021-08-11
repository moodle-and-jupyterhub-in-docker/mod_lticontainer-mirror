<?php

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'mod_mdlds_write_nbdata' => array(
        'classname'     => 'mod_mdlds_external',
        'methodname'    => 'write_nb_data',
        'description'   => 'Write Jupyter Lab/Notebook data to DB',
        'type'          => 'write',
        'capabilities'  => 'mod/mdlds:db_write',
    ),
);


$services = array(
    'Jupyter Lab/Notebook Data' => array(
        'functions' => array(
            'mod_mdlds_write_nbdata',
        ),
        'restrictedusers' => 1,
        'enabled' => 1,
        'shortname' => 'moodle_ds'
    )
);
