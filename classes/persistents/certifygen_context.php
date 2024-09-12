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
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * certifygen_context
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen\persistents;
use coding_exception;
use core\invalid_persistent_exception;
use core\persistent;
use core_course_category;
use dml_exception;
use moodle_exception;
use stdClass;

/**
 * certifygen_context
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygen_context extends persistent {

    /**
     * @var string table
     */
    public const TABLE = 'certifygen_context';
    /** @var int CONTEXT_TYPE_COURSE */
    public const CONTEXT_TYPE_COURSE = 1;
    /** @var int CONTEXT_TYPE_CATEGORY */
    public const CONTEXT_TYPE_CATEGORY = 2;
    /** @var int CONTEXT_TYPE_SYSTEM */
    public const CONTEXT_TYPE_SYSTEM = 3;

    /**
     * Define properties
     *
     * @return array[]
     */
    protected static function define_properties(): array {
        return [
            'modelid' => [
                'type' => PARAM_INT,
            ],
            'contextids' => [
                'type' => PARAM_TEXT,
                'default' => null,
            ],
            'type' => [
                'type' => PARAM_INT,
            ],
            'usermodified' => [
                'type' => PARAM_INT,
            ],
        ];
    }
    /**
     * save_model_object
     * @param object $data
     * @return self
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
    public static function save_model_object(object $data): self {
        global $USER;
        $modeldata = [
            'modelid' => $data->modelid,
            'contextids' => $data->contextids,
            'type' => $data->type,
            'usermodified' => $USER->id,
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        $id = $data->id ?? 0;
        $model = new self($id, (object)$modeldata);
        if ($id > 0) {
            $model->update();
            return $model;
        }

        return $model->create();
    }

    /**
     * exists_system_context_model
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function exists_system_context_model(): bool {
        global $DB;
        $mcontexts = [
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
        ];
        list($minsql, $minparams) = $DB->get_in_or_equal($mcontexts, SQL_PARAMS_NAMED);
        $ccontexts = [
            self::CONTEXT_TYPE_CATEGORY,
            self::CONTEXT_TYPE_COURSE,
        ];
        list($cinsql, $cinparams) = $DB->get_in_or_equal($ccontexts, SQL_PARAMS_NAMED);
        $scontexts = [
            self::CONTEXT_TYPE_SYSTEM,
        ];
        list($sinsql, $sinparams) = $DB->get_in_or_equal($scontexts, SQL_PARAMS_NAMED);

        $sql = "SELECT count(*) as total";
        $sql .= " FROM {certifygen_model} m";
        $sql .= " INNER JOIN {certifygen_context} c ON c.modelid = m.id";
        $sql .= " WHERE m.type $minsql ";
        $sql .= " AND ((c.contextids <> '' AND c.type $cinsql) OR (c.contextids = '' AND c.type $sinsql) )";
        $params = array_merge($minparams, $cinparams, $sinparams);
        $result = $DB->get_records_sql($sql, $params);
        $result = reset($result);
        return $result->total > 0;
    }

    /**
     * can_i_see_teacherrequestlink
     * @param int $userid
     * @return bool
     * @throws dml_exception
     */
    public static function can_i_see_teacherrequestlink(int $userid): bool {
        global $DB;
        $comparename = $DB->sql_compare_text('r.shortname');
        $comparenameplaceholder = $DB->sql_compare_text(':shortname');
        $select = "AND  {$comparename} = {$comparenameplaceholder}";

        $sql = "SELECT COUNT(c.id) as num";
        $sql .= " FROM {course} c ";
        $sql .= " INNER JOIN {enrol} e ON e.courseid = c.id";
        $sql .= " INNER JOIN {user_enrolments} ue ON ue.enrolid = e.id";
        $sql .= " INNER JOIN {user} u ON ue.userid = u.id";
        $sql .= " INNER JOIN {role_assignments} ra ON ra.userid = u.id ";
        $sql .= " INNER JOIN {context} con ON ( con.id = ra.contextid AND con.contextlevel = 50 AND con.instanceid = c.id)";
        $sql .= " INNER JOIN {role} r ON r.id = ra.roleid";
        $sql .= " WHERE u.id = :userid $select";
        $result = $DB->get_records_sql($sql, ['userid' => $userid, 'shortname' => 'editingteacher']);
        $result = reset($result);
        if ($result->num > 0) {
            return true;
        }
        return false;
    }
    /**
     * has_course_context
     * @throws moodle_exception
     * @throws dml_exception
     */
    public static function has_course_context(int $courseid): bool {
        global $DB;
        $hascontext = false;
        $contexts = $DB->get_records(self::TABLE);
        foreach ($contexts as $context) {
            if ($context->type == self::CONTEXT_TYPE_COURSE) {
                $hascontext = self::has_course_course_context($courseid, $context);
            } else if ($context->type == self::CONTEXT_TYPE_CATEGORY) {
                $hascontext = self::has_course_category_context($courseid, $context);
            }
            if ($hascontext) {
                break;
            }
        }
        return $hascontext;
    }

    /**
     * has_course_course_context
     * @param int $courseid
     * @param stdClass $context
     * @return bool
     */
    protected static function has_course_course_context(int $courseid, stdClass $context): bool {
        $hascontext = false;
        if ($context->type != self::CONTEXT_TYPE_COURSE) {
            return $hascontext;
        }
        $courseids = explode(',', $context->contextids);
        if (in_array($courseid, $courseids)) {
            $hascontext = true;
        }
        return $hascontext;
    }

    /**
     * has_course_category_context
     * @param int $courseid
     * @param stdClass $context
     * @return bool
     * @throws dml_exception
     * @throws moodle_exception
     */
    protected static function has_course_category_context(int $courseid, stdClass $context): bool {
        $hascontext = false;
        if ($context->type != self::CONTEXT_TYPE_CATEGORY) {
            return $hascontext;
        }
        $contextids = explode(',', $context->contextids);
        $course = get_course($courseid);
        $category = core_course_category::get($course->category);
        $categoryids = array_merge([$course->category], $category->get_parents());
        foreach ($categoryids as $categoryid) {
            if (in_array($categoryid, $contextids)) {
                $hascontext = true;
                break;
            }
        }
        return $hascontext;
    }

    /**
     * get_course_context_modelids
     * @param int $courseid
     * @return int
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_course_context_modelids(int $courseid): array {
        global $DB;
        $modelids = [];
        $contexts = $DB->get_records(self::TABLE);
        $hascontext = false;
        foreach ($contexts as $context) {
            if ($context->type == self::CONTEXT_TYPE_COURSE) {
                $hascontext = self::has_course_course_context($courseid, $context);
            } else if ($context->type == self::CONTEXT_TYPE_CATEGORY) {
                $hascontext = self::has_course_category_context($courseid, $context);
            } else if ($context->type == self::CONTEXT_TYPE_SYSTEM) {
                $hascontext = true;
            }
            if ($hascontext) {
                $modelids[] = $context->modelid;
            }
        }
        return $modelids;
    }

    /**
     * get_course_valid_modelids
     * @param int $courseid
     * @return array
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_course_valid_modelids(int $courseid): array {
        global $DB;
        $modelids = [];
        $contexts = $DB->get_records(self::TABLE);
        $hascontext = false;
        foreach ($contexts as $context) {
            if ($context->type == self::CONTEXT_TYPE_COURSE) {
                $hascontext = self::has_course_course_context($courseid, $context);
            } else if ($context->type == self::CONTEXT_TYPE_CATEGORY) {
                $hascontext = self::has_course_category_context($courseid, $context);
            } else if ($context->type == self::CONTEXT_TYPE_SYSTEM) {
                $hascontext = true;
            }
            if ($hascontext) {
                $modelids[] = $context->modelid;
            }
        }
        return $modelids;
    }

    /**
     * get_system_context_modelids_and_langs
     * @return array[]
     * @throws dml_exception
     */
    public static function get_system_context_modelids_and_langs(): array {
        global $DB;
        $modelids = [];
        $langs = [];
        $contexts = [
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
        ];
        list($insql, $inparams) = $DB->get_in_or_equal($contexts, SQL_PARAMS_NAMED);
        $sql = "SELECT m.id as modelid, m.name, m.langs";
        $sql .= " FROM {certifygen_model} m";
        $sql .= " INNER JOIN {certifygen_context} c ON c.modelid = m.id";
        $sql .= " WHERE m.type $insql ";

        $contexts = $DB->get_records_sql($sql, $inparams);
        $langstrings = get_string_manager()->get_list_of_translations();
        foreach ($contexts as $context) {
            $modelids[$context->modelid] = $context->name;
            $modellangs = explode(',', $context->langs);
            $modellangstrings = [];
            foreach ($modellangs as $modellang) {
                $modellangstrings[$modellang] = $langstrings[$modellang];
            }
            $langs[$context->modelid] = $modellangstrings;
        }
        return [$modelids, $langs];
    }
}
