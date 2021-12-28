<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * DataProvider class for retrieving data stored in the DB.
 *
 * @package     mod_ltidsbds
 * @copyright   2021 Urano Masanori <j18081mu@edu.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/../localdblib.php');


class DataProvider 
{
    private $records;
    private $userdata;
    private $sql;


    // 結合済みのレコード(stdClass)を返す
    public function get_records()
    {
        return $this->records;
    }


    // ユーザ毎のレコード(stdClass)を返す
    public function get_userdata() 
    {
        return $this->userdata;
    }


    // 実行したSQLを返す
    public function get_sql() 
    {
        return $this->sql;
    }


    //
    private function __construct(
        $course,
        $lti_id,
        $start_date,
        $end_date
    ) 
    {
        global $CFG, $DB;

        //$this->sql = get_course_lti_sql($course, $lti_id, $start_date, $end_date);
        //$_records  = $DB->get_records_sql($this->sql);
        
        $sql  = get_base_sql($start_date, $end_date);
        $sql .= get_lti_sql_condition($lti_id);
        $sql .= get_course_sql_condition($course);

        $_records  = $DB->get_records_sql($sql);

        $records = [];
        foreach($_records as $record) {
            $record->tags = self::tags_decode($record->tags);
            $records[] = $record;
        }
        $this->records = $records;

        $userdata = [];
        foreach($this->records as $record) {
            $username = $record->username;
            $userdata[$username][] = $record;
        }
        $this->userdata = $userdata;
    }


    // コンストラクタのラッパメソッド
    //
    // 全てのレコードから
    // $dp = DataProvider::instance_generation();
    // 2021年10月6日以降のレコード
    // $dp = DataProvider::instance_generation('2021-10-06 00:00:00');
    // 2021年10月6日～2021年10月7日のレコード
    // $dp = DataProvider::instance_generation('2021-10-06 00:00:00', '2021-10-07 23:59:59');
    // 指定したコースの特定のLTIインスタンス
    // $dp = DataProvider::instance_generation('*', '*', 182, 6);
    //
    public static function instance_generation(
        $course     = '*',
        $lti_id     = '*',
        $start_date = '*',
        $end_date   = '*'
    ) 
    {
        $datetime_fmt = PHP_DATETIME_FMT;

        if(empty($start_date))  $start_date = '*';
        if(empty($end_date))    $end_date   = '*';
        if(empty($course))      $course     = '*';
        if(empty($lti_id))      $lti_id     = '*';

        if($start_date === '*') $start_date = (new DateTime('1970-01-01'))->format($datetime_fmt);
        else                    $start_date = (new DateTime($start_date) )->format($datetime_fmt);

        if($end_date === '*')   $end_date   = (new DateTime())->format($datetime_fmt);
        else                    $end_date   = (new DateTime($end_date))->format($datetime_fmt);

        if($course  !== '*') $course  = intval($course);
        if($lti_id  !== '*') $lti_id  = intval($lti_id);

        return new static($course, $lti_id, $start_date, $end_date);
    }


    // DBのtagsの内容をPHPのオブジェクト(stdClass)に変換してそれを返す
    // ※ filename, codenumの情報だけ抜く
    // ※ それ以外の値は無視
    //
    // tagsの内容
    // ["filename: 1-2.ipynb","codenum: 0"]
    // ["raises-exception","filename: 1-4.ipynb","codenum: 1"]
    //
    public static function tags_decode($tags) 
    {
        if(empty($tags)) return NULL;

        // \"property: value\" の形式の文字列を $tags から探してきて，
        // それを以下のようなPHPの配列に変換する。
        // 結果は $matches に格納
        // ※ : の前後のスペースはあってもなくても結果には影響しない
        // Array
        // (
        //     [0] => Array
        //         (
        //             [0] => "property: value"
        //             [1] => property
        //             [2] => value
        //         )
        //
        //     [1] => Array
        //         (
        //             [0] => "property: value"
        //             [1] => property
        //             [2] => value
        //         )
        // )
        $properties = 'filename|codenum'; // l(小文字のエル)と|(パイプ)は要注意
        $patterns   = "/\"(${properties})\s*:\s*([^\s\"]+)\"/u";
        preg_match_all($patterns, $tags, $matches, PREG_SET_ORDER);

        if(empty($matches)) return NULL;

        // $matches を元にして，オブジェクトを構築
        $result = new stdClass();
        foreach($matches as $match) {
            $result->{$match[1]} = $match[2];
        }

        return $result;
    }
}

