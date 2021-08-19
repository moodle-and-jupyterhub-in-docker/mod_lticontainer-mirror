<?php
/**
 ipynb_conv.php : ipnb ファイル変換  v0.9
         filename: と codenum: を metadata の tags の配列に追加
                                    by Fumi.Iseki    2021 08/18
*/


if ($argc<2) {
    print('usage ... '.$argv[0]." filename\n");
    exit(1);
}

//
$filename = $argv[1];

$handle = fopen($filename, 'r');
$contents = fread($handle, filesize($filename));
fclose($handle);
$json = json_decode($contents, false);


/**

*/
function  chek_code_cell(&$p_val, $filename, &$num)
{
    if (property_exists($p_val, 'cell_type') and $p_val->cell_type=='code') {
        if (!property_exists($p_val, 'metadata')) {
            $p_val->metadata = new stdClass();
        }
        if (!property_exists($p_val->metadata, 'tags')) {
            $p_val->metadata->tags = array();
        }
        $tags = &$p_val->metadata->tags;
        //
        foreach ($tags as $key=>&$tag) {
            if      (substr($tag, 0, 10)=='filename: ') unset($tags[$key]);
            else if (substr($tag, 0,  9)=='codenum: ')  unset($tags[$key]);
        }
        $insnum = $num*10;
        $tags[] = "filename: ".$filename;
        $tags[] = "codenum: ".$insnum;
        $num++;
    }
    else { 
        foreach ($p_val as &$val) {
            if (is_object($val)) {
                chek_code_cell($val, $filename, $num);
            }
        }
    }
}


//
$num = 0;
if (is_object($json)) {
    if (property_exists($json, "cells")) {
        foreach ($json->cells as &$val) {
            if (is_object($val)) {
                chek_code_cell($val, $filename, $num);
            }
        }
    }
}

$text = json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
print($text);

