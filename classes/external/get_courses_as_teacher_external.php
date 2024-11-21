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
 * Ws Get courses as teacher
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen\external;
use certifygenfilter;
use context_course;
use context_system;
use dml_exception;
use invalid_parameter_exception;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_context;
use mod_certifygen\persistents\certifygen_model;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/classes/filters/certifygenfilter.php');
require_once($CFG->dirroot . '/mod/certifygen/lib.php');
/**
 * Get courses as teacher
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_courses_as_teacher_external extends \core_external\external_api {
    /**
     * Describes the external function parameters.
     *
     * @return \core_external\external_function_parameters
     */
    public static function get_courses_as_teacher_parameters(): \core_external\external_function_parameters {
        return new \core_external\external_function_parameters(
            [
                'userid' => new \core_external\external_value(PARAM_INT, 'user id'),
                'userfield' => new \core_external\external_value(PARAM_RAW, 'user field'),
                'lang' => new \core_external\external_value(PARAM_LANG, 'user lang'),
            ]
        );
    }

    /**
     * Get courses as teacher
     * @param int $userid
     * @param string $userfield
     * @param string $lang
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_courses_as_teacher(int $userid, string $userfield, string $lang): array {

        $params = self::validate_parameters(
            self::get_courses_as_teacher_parameters(),
            ['userid' => $userid, 'userfield' => $userfield, 'lang' => $lang]
        );
        $context = context_system::instance();
        $results = ['courses' => [], 'teacher' => [], 'error' => []];
        $haserror = false;
        $courses = [];
        try {
            if (!has_capability('mod/certifygen:manage', $context)) {
                unset($results['courses']);
                unset($results['teacher']);
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
                unset($results['teacher']);
                $results['error']['code'] = 'user_not_found';
                $results['error']['message'] = get_string('user_not_found', 'mod_certifygen');
                return $results;
            }
            $results['teacher'] = [
                'id' => $userid,
                'fullname' => fullname(reset($users)),
            ];

            // Lang exists.
            $langstrings = get_string_manager()->get_list_of_translations();
            if (!empty($lang) && !in_array($lang, array_keys($langstrings))) {
                unset($results['courses']);
                unset($results['teacher']);
                $results['error']['code'] = 'lang_not_found';
                $results['error']['message'] = get_string('lang_not_found', 'mod_certifygen');
                return $results;
            }
            // Filter to return course names in $lang language.
            $filter = new certifygenfilter(context_system::instance(), [], $lang);
            // Get courses with a certifygen_model asociated where the user is editingteacher.
            $enrolments = enrol_get_all_users_courses($userid, true);
            foreach ($enrolments as $enrolment) {
                $coursecontext = context_course::instance($enrolment->ctxinstance);
                if (!has_capability('moodle/course:managegroups', $coursecontext, $userid)) {
                    continue;
                }
                $coursefullname = $filter->filter($enrolment->fullname);
                $coursefullname = strip_tags($coursefullname);
                $courseshortname = $filter->filter($enrolment->shortname);
                $courseshortname = strip_tags($courseshortname);
                $models = certifygen_context::get_course_context_modelids($enrolment->ctxinstance);
                $coursemodels = [];
                foreach ($models as $modelid) {
                    $model = new certifygen_model($modelid);
                    if ($model->get('type') == certifygen_model::TYPE_ACTIVITY) {
                        continue;
                    }
                    $validationplugin = $model->get('validation');
                    $validationpluginclass = $validationplugin . '\\' . $validationplugin;
                    if (get_config($validationplugin, 'enabled') === '0') {
                        continue;
                    }
                    /** @var ICertificateValidation $subplugin */
                    $subplugin = new $validationpluginclass();
                    if (!$subplugin->is_visible_in_ws()) {
                        continue;
                    }
                    $coursemodels[] = (array)$model->to_record();
                }
                if (!empty($coursemodels)) {
                    $courses[] = [
                            'id' => $enrolment->ctxinstance,
                            'shortname' => $courseshortname,
                            'fullname' => $coursefullname,
                            'categoryid' => $enrolment->category,
                            'models' => $coursemodels,
                    ];
                }
            }
            $results['courses'] = $courses;
        } catch (moodle_exception $e) {
            unset($results['courses']);
            unset($results['teacher']);
            $haserror = true;
            $results['error']['code'] = $e->getCode();
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
     * @return \core_external\external_single_structure
     */
    public static function get_courses_as_teacher_returns(): \core_external\external_single_structure {
        return new \core_external\external_single_structure([
                'courses' => new \core_external\external_multiple_structure(
                    new \core_external\external_single_structure(
                        [
                            'id'   => new \core_external\external_value(PARAM_RAW, 'Course id', VALUE_OPTIONAL),
                            'shortname'   => new \core_external\external_value(
                                PARAM_RAW,
                                'Course shortname',
                                VALUE_OPTIONAL
                            ),
                            'fullname' => new \core_external\external_value(
                                PARAM_RAW,
                                'Course fullname',
                                VALUE_OPTIONAL
                            ),
                            'categoryid' => new \core_external\external_value(
                                PARAM_INT,
                                'Course category id',
                                VALUE_OPTIONAL
                            ),
                            'models' => new \core_external\external_multiple_structure(
                                new \core_external\external_single_structure(
                                    [
                                                    'id' => new \core_external\external_value(
                                                        PARAM_INT,
                                                        'Instance id',
                                                        VALUE_OPTIONAL
                                                    ),
                                                    'idnumber' => new \core_external\external_value(
                                                        PARAM_RAW,
                                                        'Model name',
                                                        VALUE_OPTIONAL
                                                    ),
                                                    'name' => new \core_external\external_value(
                                                        PARAM_RAW,
                                                        'Model name',
                                                        VALUE_OPTIONAL
                                                    ),
                                                    'mode' => new \core_external\external_value(
                                                        PARAM_INT,
                                                        'Model mode',
                                                        VALUE_OPTIONAL
                                                    ),
                                                    'timeondemmand' => new \core_external\external_value(
                                                        PARAM_INT,
                                                        'Model timeondemmand',
                                                        VALUE_OPTIONAL
                                                    ),
                                                    'type' => new \core_external\external_value(
                                                        PARAM_INT,
                                                        'Model type',
                                                        VALUE_OPTIONAL
                                                    ),
                                                    'templateid' => new \core_external\external_value(
                                                        PARAM_INT,
                                                        'Model template id',
                                                        VALUE_OPTIONAL
                                                    ),
                                                    'langs' => new \core_external\external_value(
                                                        PARAM_RAW,
                                                        'Model langs',
                                                        VALUE_OPTIONAL
                                                    ),
                                                    'validation' => new \core_external\external_value(
                                                        PARAM_RAW,
                                                        'Model validation',
                                                        VALUE_OPTIONAL
                                                    ),
                                                    'repository' => new \core_external\external_value(
                                                        PARAM_RAW,
                                                        'Model validation',
                                                        VALUE_OPTIONAL
                                                    ),
                                                ],
                                    'courses list',
                                    VALUE_OPTIONAL
                                ),
                                '',
                                VALUE_OPTIONAL
                            ),
                        ],
                        'courses list',
                        VALUE_OPTIONAL
                    ),
                    '',
                    VALUE_OPTIONAL
                ),
                'teacher' => new \core_external\external_single_structure(
                    [
                        'fullname' => new \core_external\external_value(PARAM_RAW, 'User fullname', VALUE_OPTIONAL),
                        'id' => new \core_external\external_value(PARAM_INT, 'User id', VALUE_OPTIONAL),
                    ],
                    'Student info',
                    VALUE_OPTIONAL
                ),
                'error' => new \core_external\external_single_structure([
                    'message' => new \core_external\external_value(PARAM_RAW, 'Error message', VALUE_OPTIONAL),
                    'code' => new \core_external\external_value(PARAM_RAW, 'Error code', VALUE_OPTIONAL),
                ], 'Errors information', VALUE_OPTIONAL),
            ]);
    }
}
