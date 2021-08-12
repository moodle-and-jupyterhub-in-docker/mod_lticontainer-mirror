<?php

/// SETUP - NEED TO BE CHANGED
$token = '6807de8a19ed1e1db10c0895abf6714c';
$domainname = 'https://el.mml.tuis.ac.jp';
$functionname = 'mod_mdlds_write_nbdata';

//////// moodle_user_create_users ////////

$data = new stdClass();
$data->host = 'HOST';
$data->session = '1234';
//$data->message = '5678';
//$data->status = 'ok';
//$data->username = 'iseki';
//$data->cell_id = '1123';
//$data->tags = '感じ';
//$data->date = '202100';
$params = array($data);

/// XML-RPC CALL
header('Content-Type: text/plain');
$serverurl = $domainname . '/moodle/webservice/xmlrpc/server.php'. '?wstoken=' . $token.'&courseid=98';
//$serverurl = $domainname . '/moodle/mod/mdlds/actions/json_post.php'. '?wstoken=' . $token;
require_once('./curl.php');
$curl = new curl;
//$post = xmlrpc_encode_request($functionname, array($params));
$post = xmlrpc_encode_request($functionname, $params);
//echo $post;
$resp = xmlrpc_decode($curl->post($serverurl, $post));
print_r($resp);
