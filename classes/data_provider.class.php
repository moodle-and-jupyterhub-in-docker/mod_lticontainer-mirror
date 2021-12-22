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

class DataProvider {

    ///--- テーブル名 ---///
    const DATA_TABLE    = 'ltids_websock_data';
    const TAGS_TABLE    = 'ltids_websock_tags';
    const SESSION_TABLE = 'ltids_websock_session';

    // データベースの日付のフォーマット
    const SQL_DATETIME_FMT = '%Y-%m-%dT%T.%fZ';
    const PHP_DATETIME_FMT = 'Y-m-d\TH:i:s.u\Z';

    // 結合済みのレコード(stdClass)を返す
    public function get_records() {
        return $this->records;
    }

    // ユーザ毎のレコード(stdClass)を返す
    public function get_userdata() {
        return $this->userdata;
    }

    // 実行したSQLを返す
    public function get_sql() {
        return $this->sql;
    }

    private $records;
    private $userdata;
    private $sql;

    private $start_date;
    private $end_date;
    private $course;
    private $inst_id;
    private $lti_id;

    private function __construct(
        $start_date,
        $end_date,
        $course,
        $inst_id,
        $lti_id
    ) {
        global $CFG, $DB;

        $this->start_date = $start_date;
        $this->end_date   = $end_date;
        $this->course     = $course;
        $this->inst_id    = $inst_id;
        $this->lti_id     = $lti_id;

        $data_table = $CFG->prefix.self::DATA_TABLE;
        $tags_table = $CFG->prefix.self::TAGS_TABLE;
        $session_table = $CFG->prefix.self::SESSION_TABLE;
        $datetime_fmt = self::SQL_DATETIME_FMT;

        $this->sql = <<<SQL
SELECT
  ROW_NUMBER() OVER(ORDER BY s_date ASC) id,
  username,
  tags,
  status,
  c_date,
  s_date,
  course,
  inst_id,
  lti_id
FROM
  (
    SELECT
      username,
      tags,
      status,
      c_date,
      s_date,
      session
    FROM
      (
        SELECT
          username,
          cell_id,
          status,
          C.date AS c_date,
          S.date AS s_date,
          C.session
        FROM
          (
            SELECT
              session,
              message,
              cell_id,
              date
            FROM
              $data_table
            WHERE
              host = 'client'
            AND STR_TO_DATE(date, '$datetime_fmt') >= STR_TO_DATE('$start_date', '$datetime_fmt')
            AND STR_TO_DATE(date, '$datetime_fmt') <= STR_TO_DATE('$end_date', '$datetime_fmt')
          ) C,
          (
            SELECT
              session,
              message,
              status,
              username,
              date
            FROM
              $data_table
            WHERE
              host = 'server'
            AND STR_TO_DATE(date, '$datetime_fmt') >= STR_TO_DATE('$start_date', '$datetime_fmt')
            AND STR_TO_DATE(date, '$datetime_fmt') <= STR_TO_DATE('$end_date', '$datetime_fmt')
          ) S
        WHERE
          C.message = S.message
        AND C.session = S.session
      ) CS1
      LEFT OUTER JOIN $tags_table
      ON  CS1.cell_id = $tags_table.cell_id
  ) CS2
  LEFT OUTER JOIN $session_table
  ON  CS2.session = $session_table.session

SQL;

        // course, inst_id, lti_id が指定されているならSQL末尾に条件追加
        if($course !== '*' || $inst_id !== '*' || $lti_id !== '*')
            $this->sql .= 'WHERE'.PHP_EOL;
        $output_and = false;
        if($course !== '*') {
            $this->sql .= '  course = '.$course.PHP_EOL;
            $output_and = true;
        }
        if($inst_id !== '*') {
            if($output_and)
                $this->sql .= 'AND ';
            else
                $this->sql .= '  ';
            $this->sql .= 'inst_id = '.$inst_id.PHP_EOL;
            $output_and = true;
        }
        if($lti_id !== '*') {
            if($output_and)
                $this->sql .= 'AND ';
            else
                $this->sql .= '  ';
            $this->sql .= 'lti_id = '.$lti_id.PHP_EOL;
            $output_and = true;
        }

        $this->sql .= ';'; // End of SQL


        $_records = $DB->get_records_sql($this->sql);
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
    public static function instance_generation(
        $start_date = '*',
        $end_date   = '*',
        $course     = '*',
        $inst_id    = '*',
        $lti_id     = '*'
    ) {
        $datetime_fmt = self::PHP_DATETIME_FMT;

        if(empty($start_date))
            $start_date = '*';
        if(empty($end_date))
            $end_date = '*';
        if(empty($course))
            $course = '*';
        if(empty($inst_id))
            $inst_id = '*';
        if(empty($lti_id))
            $lti_id = '*';

        if($start_date === '*')
            $start_date = (new DateTime('1970-01-01'))->format($datetime_fmt);
        else
            $start_date = (new DateTime($start_date))->format($datetime_fmt);

        if($end_date === '*')
            $end_date   = (new DateTime())->format($datetime_fmt);
        else
            $end_date   = (new DateTime($end_date))->format($datetime_fmt);

        if($course !== '*')
            $course = intval($course);
        if($inst_id !== '*')
            $inst_id = intval($inst_id);
        if($lti_id !== '*')
            $lti_id = intval($lti_id);

        return new static($start_date, $end_date, $course, $inst_id, $lti_id);
    }

    // DBのtagsの内容をPHPのオブジェクト(stdClass)に変換してそれを返す
    // ※ filename, codenumの情報だけ抜く
    // ※ それ以外の値は無視
    //
    // tagsの内容
    // ["filename: 1-2.ipynb","codenum: 0"]
    // ["raises-exception","filename: 1-4.ipynb","codenum: 1"]
    public static function tags_decode($tags) {
        if(empty($tags))
            return NULL;

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
        $patterns = "/\"(${properties})\s*:\s*([^\s\"]+)\"/u";
        preg_match_all($patterns, $tags, $matches, PREG_SET_ORDER);

        if(empty($matches))
            return NULL;

        // $matches を元にして，オブジェクトを構築
        $result = new stdClass();
        foreach($matches as $match)
            $result->{$match[1]} = $match[2];

        return $result;
    }
}


//?>
