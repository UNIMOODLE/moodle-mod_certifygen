<?php

namespace mod_certifygen\persistents;

use coding_exception;
use core\invalid_persistent_exception;
use core\persistent;
use dml_exception;
use stdClass;

class certifygen_validations extends persistent {
    /**
     * @var string table
     */
    public const TABLE = 'certifygen_validations';
    public const STATUS_NOT_STARTED = 1;
    public const STATUS_IN_PROGRESS = 2;
    public const STATUS_VALIDATION_OK = 3;
    public const STATUS_VALIDATION_ERROR = 4;
    public const STATUS_STORAGE_OK = 5;
    public const STATUS_STORAGE_ERROR = 6;
    public const STATUS_ERROR = 7;
    public const STATUS_FINISHED = 8;
    public const TEACHER_REQUEST_CODE_STARTSWITH = 'TR';

    /**
     * Define properties
     *
     * @return array[]
     */
    protected static function define_properties(): array {
        return [
            'name' => [
                'type' => PARAM_TEXT,
                'default' => NULL,
                'null' => NULL_ALLOWED,
            ],
            'courses' => [
                'type' => PARAM_TEXT,
                'default' => NULL,
                'null' => NULL_ALLOWED,
            ],
            'code' => [
                'type' => PARAM_TEXT,
                'default' => NULL,
                'null' => NULL_ALLOWED,
            ],
            'certifygenid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'userid' => [
                'type' => PARAM_INT,
            ],
            'issueid' => [
                'type' => PARAM_INT,
                'default' => NULL,
                'null' => NULL_ALLOWED,
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
    public static function manage_validation(int $id, stdClass $data) : self {
        if (!empty($data->courses)) {
            // Order courses by id.
            $courses = explode(',', $data->courses);
            asort($courses);
            $data->courses = implode(',', $courses);
        }
        if (!$id && !empty($data->courses)) {
            $data->code = self::generate_code($data->userid);
        }
        $validation = new self($id, $data);
        if (empty($id)) {
            $validation->create();
        } else {
            $validation->update();
        }
        return $validation;
    }

    /**
     * Generates a unique 10-digit code of random numbers and firstname, lastname initials if userid is passed as parameter.
     *
     * @param int|null $userid
     * @return string
     */
    public static function generate_code($userid = null) {
        global $DB;
        $uniquecodefound = false;
        $user = $userid ? $DB->get_record('user', ['id' => $userid]) : null;
        $code = self::generate_code_string($user);
        while (!$uniquecodefound) {
            if (!$DB->record_exists('tool_certificate_issues', ['code' => $code])) {
                $uniquecodefound = true;
            } else {
                $code = self::generate_code_string($user);
            }
        }
        return self::TEACHER_REQUEST_CODE_STARTSWITH . $code;
    }

    /**
     * Generates a 10-digit code of random numbers and firstname, lastname initials if userid is passed as parameter.
     *
     * @param \stdClass|null $user
     * @return string
     */
    private static function generate_code_string(\stdClass $user = null): string {
        $code = '';
        for ($i = 1; $i <= 10; $i++) {
            $code .= mt_rand(0, 9);
        }
        if ($user) {
            foreach ([$user->firstname, $user->lastname] as $item) {
                $initial = \core_text::substr(\core_text::strtoupper(\core_text::specialtoascii($item)), 0, 1);
                $code .= preg_match('/[A-Z0-9]/', $initial) ? $initial : \core_text::strtoupper(random_string(1));
            }
        } else {
            $code .= \core_text::strtoupper(random_string(2));
        }
        return $code;
    }

    /**
     * @param string $code
     * @return string
     * @throws dml_exception
     */
    public static function get_lang_by_code_for_activities(string $code) : string {

        global $DB;
        $params = [
            'code' => $code,
            'component' => 'mod_certifygen'
        ];
        $sql = "SELECT cv.lang
                  FROM {tool_certificate_issues} ci
                  JOIN {certifygen_validations} cv
                    ON (cv.issueid = ci.id AND cv.userid = ci.userid)
                    WHERE code =:code AND component =:component";

        $record = $DB->get_record_sql($sql, $params);
        return $record->lang;
    }

    /**
     * @param string $lang
     * @param int $instanceid
     * @param int $userid
     * @return certifygen_validations|null
     * @throws coding_exception
     */
    public static function get_validation_by_lang_and_instance(string $lang, int $instanceid, int $userid) {

        $cvalidations = self::get_records(['userid' => $userid, 'certifygenid' => $instanceid]);
        foreach ($cvalidations as $cvalidation) {
            if ($cvalidation->get('lang') == $lang) {
                return $cvalidation;
            }
        }
        return null;
    }

    /**
     * @param int $userid
     * @return string
     */
    public static function count_my_requests_as_teachers(int $userid) : string {

        $params = [
            'userid' => $userid,
            'certifygenid' => 0,
        ];

        return self::count_records($params);
    }

    /**
     * @param int $userid
     * @param int $start
     * @param int $pagesize
     * @return certifygen_validations[]
     * @throws dml_exception
     */
    public static function get_my_requests_as_teacher(int $userid, int $start, int $pagesize) {
        global $DB;
        $params = [
            'userid' => $userid,
            'certifygenid' => 0,
        ];
        $sql = "SELECT tr.id, tr.name, tr.modelid, tr.status, tr.lang, tr.courses, tr.userid, m.validation, m.report
        FROM {certifygen_model} m 
        INNER JOIN {certifygen_validations} tr ON tr.modelid = m.id
        WHERE tr.userid = :userid
        AND tr.certifygenid = :certifygenid
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
    public static function get_request_by_data_for_teachers(int $userid, string $courses, string $lang, int $modelid, string $name) {
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
            'certifygenid' => 0,
        ];
        $sql = "SELECT ct.* 
                FROM {certifygen_validations} ct
                WHERE {$comparelang} = {$comparelangplaceholder} 
                    AND {$comparecourses} = {$comparecoursesplaceholder} 
                    AND {$comparename} = {$comparenameplaceholder} 
                    AND ct.userid = :userid
                    AND ct.certifygenid = :certifygenid
                    AND ct.modelid = :modelid ";
        return $DB->get_record_sql($sql, $params);
    }

    /**
     * @param certifygen_validations $validation
     * @return bool|mixed|null
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_certificate_code(certifygen_validations $validation) {
        $code = $validation->get('code');
        if (!empty($validation->get('certifygenid'))) {
            global $DB;
            $code = $DB->get_field('tool_certificate_issues', 'code',
                ['userid' => $validation->get('userid'),
                    'id' => $validation->get('issueid')]);
        }
        return $code;
    }
}