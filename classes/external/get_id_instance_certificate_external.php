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
 * WS get id certificate
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\external;
use cm_info;
use context_course;
use context_system;
use dml_exception;
use \core_external\external_api;
use \core_external\external_function_parameters;
use \core_external\external_multiple_structure;
use \core_external\external_single_structure;
use \core_external\external_value;
use \core\exception\invalid_parameter_exception;
use mod_certifygen\interfaces\icertificatevalidation;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use certifygenfilter;
use \core\exception\moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/classes/filters/certifygenfilter.php');
/**
 * Get instances where there is a mod_certifygen
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_id_instance_certificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_id_instance_certificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
                'userfield' => new external_value(PARAM_RAW, 'user field'),
                'lang' => new external_value(PARAM_LANG, 'user id'),
            ]
        );
    }

    /**
     * get_id_instance_certificate
     * @param int $userid
     * @param string $userfield     
     * @param string $lang
     * @return string[]
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws dml_exception
     */
    public static function get_id_instance_certificate(int $userid, string $userfield, string $lang): array {
        global $DB;
        $params = self::validate_parameters(
            self::get_id_instance_certificate_parameters(),
            ['userid' => $userid, 'userfield' => $userfield,
                'lang' => $lang]
        );
        $context = context_system::instance();
        $results = ['error' => []];
        $haserror = false;
        $instances = [];
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
            $user = user_get_users_by_id([$userid]);
            if (empty($user)) {
                $results['error']['code'] = 'user_not_found';
                $results['error']['message'] = get_string('user_not_found', 'mod_certifygen');
                return $results;
            }
            // Lang exists.
            $langstrings = get_string_manager()->get_list_of_translations();
            if (!empty($lang) && !in_array($lang, array_keys($langstrings))) {
                $results['error']['code'] = 'lang_not_found';
                $results['error']['message'] = get_string('lang_not_found', 'mod_certifygen');
                return $results;
            }

            // Filter to return course names in $lang language.
            $filter = new certifygenfilter(context_system::instance(), [], $lang);

            // Get all mod_certifygen activities.
            $allactivities = certifygen::get_records();
            $courseids = array_map(function ($activity) {
                return $activity->get('course');
            }, $allactivities);

            // Get courses with mod_certifygen activity where the user is student.
            $enrolments = enrol_get_all_users_courses($userid, true);
            foreach ($enrolments as $enrolment) {
                if (!in_array($enrolment->ctxinstance, $courseids)) {
                    continue;
                }
                $coursecontext = context_course::instance($enrolment->ctxinstance);
                if (
                    !has_capability(
                        'mod/certifygen:emitmyactivitycertificate',
                        $coursecontext,
                        $userid
                    )
                ) {
                    continue;
                }
                $coursefullname = $filter->filter($enrolment->fullname);
                $coursefullname = strip_tags($coursefullname);
                $courseshortname = $filter->filter($enrolment->shortname);
                $courseshortname = strip_tags($courseshortname);
                $course = [
                        'id' => $enrolment->ctxinstance,
                        'shortname' => $courseshortname,
                        'fullname' => $coursefullname,
                        'categoryid' => $enrolment->category,
                ];
                $instance['course'] = $course;
                foreach ($allactivities as $activity) {
                    if ($activity->get('course') != $enrolment->ctxinstance) {
                        continue;
                    }
                    $modelid = $DB->get_field('certifygen_cmodels', 'modelid', ['certifygenid' => $activity->get('id')]);
                    $data = get_course_and_cm_from_instance($activity->get('id'), 'certifygen', $activity->get('course'));
                    /** @var cm_info $cm */
                    $cm = $data[1];
                    if (!$cm->visible) {
                        continue;
                    }
                    if (!$cm->available) {
                        continue;
                    }
                    $model = certifygen_model::get_record(['id' => $modelid]);
                    $validationplugin = $model->get('validation');
                    $validationpluginclass = $validationplugin . '\\' . $validationplugin;
                    if (get_config($validationplugin, 'enabled') === '0') {
                        continue;
                    }
                    /** @var icertificatevalidation $subplugin */
                    $subplugin = new $validationpluginclass();
                    if (!$subplugin->is_visible_in_ws()) {
                        continue;
                    }
                    $actvname = $filter->filter($activity->get('name'));
                    $actvname = strip_tags($actvname);
                    $instance['instance'] = [
                            'id' => $activity->get('id'),
                            'name' => $actvname,
                            'modelid' => $model->get('id'),
                            'modelname' => $model->get('name'),
                            'modelidnumber' => $model->get('idnumber'),
                            'modelmode' => $model->get('mode'),
                            'modeltimeondemmand' => $model->get('timeondemmand'),
                            'modeltype' => $model->get('type'),
                            'modeltemplateid' => $model->get('templateid'),
                            'modellangs' => $model->get('langs'),
                            'modelvalidation' => $model->get('validation'),
                            'modelrepository' => $model->get('repository'),
                    ];
                    $instances[] = $instance;
                }
            }
            $results['instances'] = $instances;
        } catch (moodle_exception $e) {
            unset($results['instances']);
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
     * @return external_single_structure
     */
    public static function get_id_instance_certificate_returns(): external_single_structure {
        return new external_single_structure([
                'instances' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'course'   => new external_single_structure([
                                'id' => new external_value(PARAM_INT, 'Course id', VALUE_OPTIONAL),
                                'shortname' => new external_value(
                                    PARAM_RAW,
                                    'Course shortname',
                                    VALUE_OPTIONAL
                                ),
                                'fullname' => new external_value(
                                    PARAM_RAW,
                                    'Course fullname',
                                    VALUE_OPTIONAL
                                ),
                                'categoryid' => new external_value(
                                    PARAM_INT,
                                    'Category id',
                                    VALUE_OPTIONAL
                                ),
                            ], 'Course information', VALUE_OPTIONAL),
                            'instance'   => new external_single_structure([
                                'id' => new external_value(
                                    PARAM_INT,
                                    'Instance id',
                                    VALUE_OPTIONAL
                                ),
                                'name' => new external_value(
                                    PARAM_RAW,
                                    'Instance name',
                                    VALUE_OPTIONAL
                                ),
                                'modelid' => new external_value(
                                    PARAM_INT,
                                    'Model id',
                                    VALUE_OPTIONAL
                                ),
                                'modelname' => new external_value(
                                    PARAM_RAW,
                                    'Model name',
                                    VALUE_OPTIONAL
                                ),
                                'modelidnumber' => new external_value(
                                    PARAM_RAW,
                                    'Model name',
                                    VALUE_OPTIONAL
                                ),
                                'modelmode' => new external_value(
                                    PARAM_INT,
                                    'Model mode',
                                    VALUE_OPTIONAL
                                ),
                                'modeltimeondemmand' => new external_value(
                                    PARAM_INT,
                                    'Model timeondemmand',
                                    VALUE_OPTIONAL
                                ),
                                'modeltype' => new external_value(
                                    PARAM_INT,
                                    'Model type',
                                    VALUE_OPTIONAL
                                ),
                                'modeltemplateid' => new external_value(
                                    PARAM_INT,
                                    'Model template id',
                                    VALUE_OPTIONAL
                                ),
                                'modellangs' => new external_value(
                                    PARAM_RAW,
                                    'Model langs',
                                    VALUE_OPTIONAL
                                ),
                                'modelvalidation' => new external_value(
                                    PARAM_RAW,
                                    'Model validation',
                                    VALUE_OPTIONAL
                                ),
                                'modelrepository' => new external_value(
                                    PARAM_RAW,
                                    'Model repository',
                                    VALUE_OPTIONAL
                                ),
                            ], 'Module Instance information', VALUE_OPTIONAL),
                        ],
                        'Module Instances list',
                        VALUE_OPTIONAL
                    ),
                    '',
                    VALUE_OPTIONAL
                ),
                'error' => new external_single_structure([
                    'message' => new external_value(PARAM_RAW, 'Error message'),
                    'code' => new external_value(PARAM_RAW, 'Error code'),
                ], 'Errors information', VALUE_OPTIONAL),
            ]);
    }
}
