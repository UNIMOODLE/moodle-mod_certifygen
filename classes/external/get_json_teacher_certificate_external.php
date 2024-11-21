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
 * WS Get json teacher certificate
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen\external;
use context_system;
use mod_certifygen\interfaces\ICertificateReport;
use mod_certifygen\persistents\certifygen_context;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/certifygen/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/classes/filters/certifygenfilter.php');
/**
 * Get teacher certificate elements
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_json_teacher_certificate_external extends \core_external\external_api {
    /**
     * Describes the external function parameters.
     *
     * @return \core_external\external_function_parameters
     */
    public static function get_json_teacher_certificate_parameters(): \core_external\external_function_parameters {
        return new \core_external\external_function_parameters(
            [
                'userid' => new \core_external\external_value(PARAM_INT, 'user id'),
                'userfield' => new \core_external\external_value(PARAM_RAW, 'user field'),
                'courses' => new \core_external\external_value(PARAM_RAW, 'courses'),
                'lang' => new \core_external\external_value(PARAM_RAW, 'lang'),
                'modelid' => new \core_external\external_value(PARAM_INT, 'model id'),
            ]
        );
    }

    /**
     * get_json_teacher_certificate
     * @param int $userid
     * @param string $userfield
     * @param string $courses
     * @param string $lang
     * @return string[]
     */
    public static function get_json_teacher_certificate(
        int $userid,
        string $userfield,
        string $courses,
        string $lang,
        int $modelid
    ): array {

        $params = self::validate_parameters(
            self::get_json_teacher_certificate_parameters(),
            [
                'userid' => $userid,
                'userfield' => $userfield,
                'courses' => $courses,
                'lang' => $lang,
                'modelid' => $modelid,
            ]
        );
        $context = context_system::instance();
        $results = ['error' => []];
        try {
            if (!has_capability('mod/certifygen:manage', $context)) {
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

            // Model exists.
            $model = new certifygen_model($modelid);
            if (!$model) {
                $results['error']['code'] = 'model_not_found';
                $results['error']['message'] = get_string('model_not_found', 'mod_certifygen');
                return $results;
            }
            if ($model->get('type') != certifygen_model::TYPE_TEACHER_ALL_COURSES_USED) {
                $results['error']['code'] = 'model_not_valid';
                $results['error']['message'] = get_string('model_not_valid', 'mod_certifygen');
                return $results;
            }
            // Already emtited?
            $validation = certifygen_validations::get_request_by_data_for_teachers($userid, $courses, $lang, $modelid);
            if (!$validation) {
                // Courses are valid for the model?
                $coursesarray = explode(',', $courses);
                foreach ($coursesarray as $courseid) {
                    $validmodelids = certifygen_context::get_course_valid_modelids($courseid);
                    if (!in_array($modelid, $validmodelids)) {
                        $results['error']['code'] = 'course_not_valid_with_model';
                        $results['error']['message'] = get_string('course_not_valid_with_model', 'mod_certifygen', $courseid);
                        return $results;
                    }
                }

                // Create teacher request.
                $data = [
                        'name' => 'ws_request_' . time(),
                        'modelid' => $modelid,
                        'status' => certifygen_validations::STATUS_NOT_STARTED,
                        'lang' => $lang,
                        'courses' => $courses,
                        'userid' => $userid,
                        'certifygenid' => 0,
                ];
                $validation = certifygen_validations::manage_validation(0, (object)$data);

                // Emit certificate.
                $result = emitteacherrequest_external::emitteacherrequest($validation->get('id'));
                if (!$result['result']) {
                    $result['error']['code'] = 'certificate_can_not_be_emited';
                    $result['error']['message'] = $result['message'];
                    return $result;
                }
            }
            $reportplugin = $model->get('report');
            $reportpluginclass = $reportplugin . '\\' . $reportplugin;
            /** @var ICertificateReport $subplugin */
            $subplugin = new $reportpluginclass();
            $celemtns = $subplugin->get_certificate_elements($validation);
            if (array_key_exists('error', $celemtns)) {
                $results = $celemtns;
            } else {
                $results['json'] = json_encode($celemtns['list']);
                unset($results['error']);
            }
        } catch (moodle_exception $e) {
            $results['error']['code'] = $e->getCode();
            $results['error']['message'] = $e->getMessage();
        }
        return $results;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return \core_external\external_single_structure
     */
    public static function get_json_teacher_certificate_returns(): \core_external\external_single_structure {
        return new \core_external\external_single_structure([
                'json' => new \core_external\external_value(PARAM_RAW, 'Certificate elements in a json'),
                'error' => new \core_external\external_single_structure([
                        'message' => new \core_external\external_value(PARAM_RAW, 'Error message'),
                        'code' => new \core_external\external_value(PARAM_RAW, 'Error code'),
                ], 'Errors information', VALUE_OPTIONAL),
            ]);
    }
}
