<?php

/// SETUP - NEED TO BE CHANGED
$token = 'deaf443d3a403c5cb5847dd352683e0d';
$domainname = 'https://el.mml.tuis.ac.jp';
$functionname = 'mod_mdlds_write_nbdata';

//////// moodle_user_create_users ////////

$data = new stdClass();
$data->host = 'HOST';
$data->lti_id = '1234';
$data->session = '456';
$data->message = 'mess';
$data->status = 'XXXX';
$data->username = 'iseki';
$data->cell_id = '987';
$data->tags = '2021/08/21';
$data->date = '2021/08/21';
$params = array($data);

/// XML-RPC CALL
header('Content-Type: text/plain');
$serverurl = $domainname . '/moodle/webservice/xmlrpc/server.php'. '?wstoken=' . $token;
//$serverurl = $domainname . '/moodle/mod/mdlds/actions/json_post.php'. '?wstoken=' . $token;
require_once('./curl.php');
$curl = new curl;
//$post = xmlrpc_encode_request($functionname, array($params));
$post = xmlrpc_encode_request($functionname, $params);
//echo $serverurl;
//echo $post;
$resp = xmlrpc_decode($curl->post($serverurl, $post));
print_r($resp);
