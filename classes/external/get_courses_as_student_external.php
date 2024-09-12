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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos..

/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen\external;
use certifygenfilter;
use context_system;
use core_completion_external;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_context;
use mod_certifygen\persistents\certifygen_model;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/mod/certifygen/classes/filters/certifygenfilter.php');
require_once($CFG->dirroot.'/mod/certifygen/lib.php');
/**
 * Get courses as student
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_courses_as_student_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_courses_as_student_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
                'userfield' => new external_value(PARAM_RAW, 'user field'),
                'lang' => new external_value(PARAM_LANG, 'user lang'),
            ]
        );
    }

    /**
     * Get courses as student
     * @param int $userid
     * @param string $userfield
     * @param string $lang
     * @return array
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     */
    public static function get_courses_as_student(int $userid, string $userfield, string $lang): array {

        $params = self::validate_parameters(
            self::get_courses_as_student_parameters(), ['userid' => $userid, 'userfield' => $userfield, 'lang' => $lang]
        );
        $context = context_system::instance();
        $results = ['courses' => [], 'student' => [], 'error' => []];
        $haserror = false;
        $courses = [];
        try {
            if (!has_capability('mod/certifygen:manage', $context)) {
                unset($results['courses']);
                unset($results['student']);
                $results['error']['code'] = 'nopermissiontogetcourses';
                $results['error']['message'] = get_string('nopermissiontogetcourses', 'mod_certifygen');
                return $results;
            }

            // Choose user parameter.
            $uparam = mod_certifygen_validate_user_parameters_for_ws($params['userid'], $params['userfield']);
            if (array_key_exists('error', $uparam)) {
                return $uparam;
            }
            $userid = $uparam['userid'];

            // User exists.
            $users = user_get_users_by_id([$userid]);
            if (empty($users)) {
                unset($results['courses']);
                unset($results['student']);
                $results['error']['code'] = 'user_not_found';
                $results['error']['message'] = 'User not found';
                return $results;
            }
            $results['student'] = [
                'id' => $userid,
                'fullname' => fullname(reset($users)),
            ];

            // Lang exists.
            $langstrings = get_string_manager()->get_list_of_translations();
            if (!empty($lang) && !in_array($lang, array_keys($langstrings))) {
                $results['error']['code'] = 'lang_not_found';
                $results['error']['message'] = 'Lang not found on platform';
                return $results;
            }
            // Filter to return course names in $lang language.
            $filter = new certifygenfilter(context_system::instance(), [], $lang);
            // Get courses with a certifygen_model asociated where the user is student.
            $enrolments = enrol_get_all_users_courses($userid, true);
            foreach ($enrolments as $enrolment) {
                $coursecontext = \context_course::instance($enrolment->ctxinstance);
                if (has_capability('moodle/course:managegroups', $coursecontext, $userid)) {
                    continue;
                }
                if (!certifygen::get_record(['course' => $enrolment->ctxinstance])) {
                    continue;
                }
                $coursefullname = $filter->filter($enrolment->fullname);
                $coursefullname = strip_tags($coursefullname);
                $courseshortname = $filter->filter($enrolment->shortname);
                $courseshortname = strip_tags($courseshortname);
                $completed = false;
                try {
                    $completion = core_completion_external::get_course_completion_status($enrolment->ctxinstance, $userid);
                    $completed = $completion['completionstatus']['completed'];
                } catch (moodle_exception $e) {
                    //debugging(__FUNCTION__ . ' completion error: '.$e->getMessage());
                }

                $modellist = certifygen_context::get_course_valid_modelids($enrolment->ctxinstance);
                $modellist = implode(',', $modellist);
                $courses[] = [
                    'id' => $enrolment->ctxinstance,
                    'shortname' => $courseshortname,
                    'fullname' => $coursefullname,
                    'categoryid' => $enrolment->category,
                    'completed'   => $completed,
                    'modellist'   => $modellist,
                ];
            }
            $results['courses'] = $courses;
        } catch (moodle_exception $e) {
            $haserror = true;$results['error']['code'] = $e->getCode();
            $results['error']['message'] = $e->getMessage();
        }
        if (!$haserror) {
            unset($results['error']);
        }
        return $results;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_courses_as_student_returns(): external_single_structure {
        return new external_single_structure([
                'courses' => new external_multiple_structure( new external_single_structure(
                        [
                            'id'   => new external_value(PARAM_RAW, 'Course id'),
                            'shortname'   => new external_value(PARAM_RAW, 'Course shortname'),
                            'fullname' => new external_value(PARAM_RAW, 'Course fullname'),
                            'categoryid' => new external_value(PARAM_INT, 'Course category id'),
                            'completed' => new external_value(PARAM_BOOL, 'student has course completed '),
                            'modellist' => new external_value(PARAM_RAW, 'model id list separated by commas.'),
                        ], 'course info')
                , 'courses list', VALUE_OPTIONAL),
                'student' => new external_single_structure (
                    [
                        'fullname' => new external_value(PARAM_RAW, 'User fullname'),
                        'id' => new external_value(PARAM_INT, 'User id'),
                        'userfield' => new external_value(PARAM_RAW, 'User id', VALUE_OPTIONAL),
                    ], 'Student info', VALUE_OPTIONAL),
                'error' => new external_single_structure([
                    'message' => new external_value(PARAM_RAW, 'Error message', VALUE_OPTIONAL),
                    'code' => new external_value(PARAM_RAW, 'Error code', VALUE_OPTIONAL),
                ], 'Errors information', VALUE_OPTIONAL),
            ]
        );
    }
}
