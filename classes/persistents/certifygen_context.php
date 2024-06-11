<?php
// This file is part of Moodle - http://moodle.org/
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

namespace mod_certifygen\persistents;
use coding_exception;
use core\invalid_persistent_exception;
use core\persistent;
use core_course_category;
use dml_exception;
use moodle_exception;
use stdClass;

/**
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
    public const CONTEXT_TYPE_COURSE = 1;
    public const CONTEXT_TYPE_CATEGORY = 2;

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
                'default' => null
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
     * @param object $data
     * @return self
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
    public static function save_model_object( object $data) : self {
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
     * @throws moodle_exception
     * @throws dml_exception
     */
    public static function has_course_context(int $courseid) : bool {
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
     * @param int $courseid
     * @param stdClass $context
     * @return bool
     */
    protected static function has_course_course_context(int $courseid, stdClass $context) : bool {
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
     * @param int $courseid
     * @param stdClass $context
     * @return bool
     * @throws dml_exception
     * @throws moodle_exception
     */
    protected static function has_course_category_context(int $courseid, stdClass $context) : bool {
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
     * @param int $courseid
     * @return int
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_course_context_modelid(int $courseid) : int {
        global $DB;
        $modelid = 0;
        $contexts = $DB->get_records(self::TABLE);
        foreach ($contexts as $context) {
            if ($context->type == self::CONTEXT_TYPE_COURSE) {
                $hascontext = self::has_course_course_context($courseid, $context);
            } else if ($context->type == self::CONTEXT_TYPE_CATEGORY) {
                $hascontext = self::has_course_category_context($courseid, $context);
            }
            if ($hascontext) {
                $modelid = $context->modelid;
                break;
            }
        }
        return $modelid;
    }

    /**
     * @param int $courseid
     * @return int
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_course_valid_modelids(int $courseid) : array {
        global $DB;
        $modelids = [];
        $contexts = $DB->get_records(self::TABLE);
        foreach ($contexts as $context) {
            if ($context->type == self::CONTEXT_TYPE_COURSE) {
                $hascontext = self::has_course_course_context($courseid, $context);
            } else if ($context->type == self::CONTEXT_TYPE_CATEGORY) {
                $hascontext = self::has_course_category_context($courseid, $context);
            }
            if ($hascontext) {
                $modelids[] = $context->modelid;
                break;
            }
        }
        return $modelids;
    }
}