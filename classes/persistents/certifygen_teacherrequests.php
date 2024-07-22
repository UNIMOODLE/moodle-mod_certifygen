<?php

namespace mod_certifygen\persistents;

use coding_exception;
use core\invalid_persistent_exception;
use core\persistent;
use dml_exception;
use stdClass;

class certifygen_teacherrequests extends persistent {
    /**
     * @var string table
     */
    public const TABLE = 'certifygen_teacherrequests';
    public const STATUS_NOT_STARTED = 1;
    public const STATUS_IN_PROGRESS = 2;
    public const STATUS_VALIDATION_OK = 3;
    public const STATUS_VALIDATION_ERROR = 4;
    public const STATUS_STORAGE_OK = 5;
    public const STATUS_STORAGE_ERROR = 6;
    public const STATUS_ERROR = 7;
    public const STATUS_FINISHED = 8;

    /**
     * Define properties
     *
     * @return array[]
     */
    protected static function define_properties(): array {
        return [
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'modelid' => [
                'type' => PARAM_INT,
            ],
            'status' => [
                'type' => PARAM_INT,
            ],
            'lang' => [
                'type' => PARAM_TEXT,
            ],
            'courses' => [
                'type' => PARAM_TEXT,
            ],
            'userid' => [
                'type' => PARAM_INT,
            ],
            'usermodified' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    /**
     * @param int $id
     * @param stdClass $data
     * @return self
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
    public static function manage_teacherrequests(int $id, stdClass $data) : self {

        $courses = explode(',', $data->courses);
        asort($courses);
        $data->courses = implode(',', $courses);
        $validation = new self($id, $data);
        if (empty($id)) {
            $validation->create();
        } else {
            $validation->update();
        }
        return $validation;
    }

    /**
     * @param int $userid
     * @return string
     */
    public static function count_my_requests(int $userid) : string {

        $params = [
            'userid' => $userid,
        ];

        return self::count_records($params);
    }

    /**
     * @param int $userid
     * @param string $sort
     * @param int $start
     * @param int $pagesize
     * @return certifygen_teacherrequests[]
     */
    public static function get_my_requests(int $userid, int $start, int $pagesize) {
        global $DB;
        $params = [
            'userid' => $userid,
        ];
        $sql = "SELECT tr.id, tr.name, tr.modelid, tr.status, tr.lang, tr.courses, tr.userid, m.validation, m.report
        FROM {certifygen_model} m 
        INNER JOIN {certifygen_teacherrequests} tr ON tr.modelid = m.id
        WHERE tr.userid = :userid
        ORDER BY tr.timemodified DESC ";
        return $DB->get_records_sql($sql, $params, $start, $pagesize);
    }

    /**
     * @param int $userid
     * @param string $courses
     * @param string $lang
     * @param int $modelid
     * @return false|mixed
     * @throws dml_exception
     */
    public static function get_request_by_data(int $userid, string $courses, string $lang, int $modelid, string $name) {
        global $DB;
        $comparename = $DB->sql_compare_text('ct.name');
        $comparenameplaceholder = $DB->sql_compare_text(':name');
        $comparelang = $DB->sql_compare_text('ct.lang');
        $comparelangplaceholder = $DB->sql_compare_text(':lang');
        $comparecourses = $DB->sql_compare_text('ct.courses');
        $comparecoursesplaceholder = $DB->sql_compare_text(':courses');

        $params = [
            'userid' => $userid,
            'courses' => $courses,
            'lang' => $lang,
            'modelid' => $modelid,
            'name' => $name,
        ];
        $sql = "SELECT ct.* 
                FROM {certifygen_teacherrequests} ct
                WHERE {$comparelang} = {$comparelangplaceholder} 
                    AND {$comparecourses} = {$comparecoursesplaceholder} 
                    AND {$comparename} = {$comparenameplaceholder} 
                    AND ct.userid = :userid
                    AND ct.modelid = :modelid ";
        return $DB->get_record_sql($sql, $params);
    }
}