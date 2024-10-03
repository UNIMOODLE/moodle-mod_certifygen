<?php
// This file is part of the mod_certifygen plugin for Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * certifygen_validations
 * @package   mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen\persistents;

use coding_exception;
use core\invalid_persistent_exception;
use core\persistent;
use core_text;
use dml_exception;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/certifygen/lib.php');
/**
 * certifygen_validations
 * @package   mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygen_validations extends persistent {
    /**
     * @var string TABLE
     */
    public const TABLE = 'certifygen_validations';
    /** @var int STATUS_NOT_STARTED */
    public const STATUS_NOT_STARTED = 1;
    /** @var int STATUS_IN_PROGRESS */
    public const STATUS_IN_PROGRESS = 2;
    /** @var int STATUS_VALIDATION_OK */
    public const STATUS_VALIDATION_OK = 3;
    /** @var int STATUS_VALIDATION_ERROR */
    public const STATUS_VALIDATION_ERROR = 4;
    /** @var int STATUS_STORAGE_OK */
    public const STATUS_STORAGE_OK = 5;
    /** @var int STATUS_STORAGE_ERROR */
    public const STATUS_STORAGE_ERROR = 6;
    /** @var int STATUS_ERROR */
    public const STATUS_ERROR = 7;
    /** @var int STATUS_FINISHED */
    public const STATUS_FINISHED = 8;
    /** @var int STATUS_TEACHER_ERROR */
    public const STATUS_TEACHER_ERROR = 9;
    /** @var int STATUS_STUDENT_ERROR */
    public const STATUS_STUDENT_ERROR = 10;
    /** @var int TEACHER_REQUEST_CODE_STARTSWITH */
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
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'courses' => [
                'type' => PARAM_TEXT,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'code' => [
                'type' => PARAM_TEXT,
                'default' => null,
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
                'default' => null,
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
     * manage_validation
     * @param int $id
     * @param stdClass $data
     * @return self
     * @throws coding_exception
     * @throws invalid_persistent_exception|dml_exception
     */
    public static function manage_validation(int $id, stdClass $data): self {
        // Check if lang exists.
        if (!mod_certifygen_lang_is_installed($data->lang)) {
            $a = new stdClass();
            $a->lang = $data->lang;
            throw new moodle_exception('lang_not_exists', 'mod_certifygen', '', $a);
        }
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
     * @throws dml_exception
     */
    public static function generate_code(int $userid = null): string {
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
     * @param stdClass|null $user
     * @return string
     */
    private static function generate_code_string(stdClass $user = null): string {
        $code = '';
        for ($i = 1; $i <= 10; $i++) {
            $code .= mt_rand(0, 9);
        }
        if ($user) {
            foreach ([$user->firstname, $user->lastname] as $item) {
                $initial = core_text::substr(core_text::strtoupper(core_text::specialtoascii($item)), 0, 1);
                $code .= preg_match('/[A-Z0-9]/', $initial) ? $initial : core_text::strtoupper(random_string(1));
            }
        } else {
            $code .= core_text::strtoupper(random_string(2));
        }
        return $code;
    }

    /**
     * get_lang_by_code_for_activities
     * @param string $code
     * @return string
     * @throws dml_exception
     */
    public static function get_lang_by_code_for_activities(string $code): string {

        global $DB;
        $params = [
            'code' => $code,
            'component' => 'mod_certifygen',
        ];
        $sql = "SELECT cv.lang
                  FROM {tool_certificate_issues} ci
                  JOIN {certifygen_validations} cv
                        ON (cv.issueid = ci.id AND cv.userid = ci.userid)
                  WHERE code =:code AND component =:component

        ";
        $record = $DB->get_record_sql($sql, $params);
        return $record->lang;
    }

    /**
     * get_validation_by_lang_and_instance
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
     * count_my_requests_as_teachers
     * @param int $userid
     * @return string
     */
    public static function count_my_requests_as_teachers(int $userid): string {

        $params = [
            'userid' => $userid,
            'certifygenid' => 0,
        ];

        return self::count_records($params);
    }

    /**
     * get_my_requests_as_teacher
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
        $sql = "SELECT tr.id, tr.name, tr.modelid, tr.status, tr.lang, tr.courses, tr.userid,
                       tr.timecreated, m.validation, m.report, m.mode, m.timeondemmand, m.validation
                  FROM {certifygen_model} m
                  JOIN {certifygen_validations} tr ON tr.modelid = m.id
                 WHERE tr.userid = :userid
                       AND tr.certifygenid = :certifygenid
                       ORDER BY tr.timemodified DESC";

        return $DB->get_records_sql($sql, $params, $start, $pagesize);
    }

    /**
     * get_request_by_data_for_teachers
     * @param int $userid
     * @param string $courses
     * @param string $lang
     * @param int $modelid
     * @param string $name
     * @return false|mixed
     * @throws dml_exception
     */
    public static function get_request_by_data_for_teachers(
        int $userid,
        string $courses,
        string $lang,
        int $modelid,
        string $name = ''
    ) {
        global $DB;


        $comparelang = $DB->sql_compare_text('ct.lang');
        $comparelangplaceholder = $DB->sql_compare_text(':lang');
        $comparecourses = $DB->sql_compare_text('ct.courses');
        $comparecoursesplaceholder = $DB->sql_compare_text(':courses');

        $params = [
            'userid' => $userid,
            'courses' => $courses,
            'lang' => $lang,
            'modelid' => $modelid,
            'certifygenid' => 0,
        ];
        $comparenamecondition = "";
        if (!empty($name)) {
            $comparename = $DB->sql_compare_text('ct.name');
            $comparenameplaceholder = $DB->sql_compare_text(':name');
            $comparenamecondition = "AND {$comparename} = {$comparenameplaceholder}";
            $params['name'] = $name;
        }
        $sql = "SELECT ct.*
                  FROM {certifygen_validations} ct
                 WHERE {$comparelang} = {$comparelangplaceholder}
                       AND {$comparecourses} = {$comparecoursesplaceholder}
                       $comparenamecondition
                       AND ct.userid = :userid
                       AND ct.certifygenid = :certifygenid
                       AND ct.modelid = :modelid";
        return $DB->get_record_sql($sql, $params);
    }

    /**
     * get_certificate_code
     * @param certifygen_validations $validation
     * @return bool|mixed|null
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_certificate_code(certifygen_validations $validation) {
        $code = $validation->get('code');
        if (!empty($validation->get('certifygenid'))) {
            global $DB;
            $code = $DB->get_field(
                'tool_certificate_issues',
                'code',
                ['userid' => $validation->get('userid'),
                'id' => $validation->get('issueid')]
            );
        }
        return $code;
    }

    /**
     * get_status_error
     * @return array
     */
    public static function get_status_error(): array {
        return [
            self::STATUS_ERROR,
            self::STATUS_STUDENT_ERROR,
            self::STATUS_STORAGE_ERROR,
            self::STATUS_TEACHER_ERROR,
            self::STATUS_VALIDATION_ERROR,
        ];
    }
}
