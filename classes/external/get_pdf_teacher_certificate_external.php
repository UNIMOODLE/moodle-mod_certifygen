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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos..
/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\external;


use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use mod_certifygen\interfaces\ICertificateReport;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;

global $CFG;
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/mod/certifygen/lib.php');
require_once($CFG->dirroot.'/mod/certifygen/classes/filters/certifygenfilter.php');

class get_pdf_teacher_certificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_pdf_teacher_certificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
                'userfield' => new external_value(PARAM_RAW, 'user field'),
                'name' => new external_value(PARAM_RAW, 'request name'),
                'courses' => new external_value(PARAM_RAW, 'course list separated by commas.'),
                'modelid' => new external_value(PARAM_INT, 'Model id'),
                'lang' => new external_value(PARAM_RAW, 'certificate model type'),
            ]
        );
    }
    public static function get_pdf_teacher_certificate(int $userid, string $userfield, string $name, string $courses,
                                                       int $modelid, string $lang): array {
        /**
         * Devuelve el PDF del certificado de que el profesor ha impartido docencia en el curso
         * indicado con el detalle del uso que ha realizado de la herramienta que aparecerá en el
         * certificado. Este servicio web llamará a getJsonTeaching para obtener la información a
         * maquetar
         */
        $params = self::validate_parameters(
            self::get_pdf_teacher_certificate_parameters(),
            ['userid' => $userid, 'userfield' => $userfield, 'name' => $name, 'courses' => $courses,
                'modelid' => $modelid, 'lang' => $lang]
        );
        try {
            // Choose user parameter.
            $uparam = mod_certifygen_validate_user_parameters_for_ws($params['userid'], $params['userfield']);
            if (array_key_exists('error', $uparam)) {
                return $uparam;
            }
            $userid = $uparam['userid'];

            // User exists.
            $user = user_get_users_by_id([$params['userid']]);
            if (empty($user)) {
                $result['error']['code'] = 'user_not_found';
                $result['error']['message'] = 'User not found';
                return $result;
            }

            // Is user enrolled on this course as teacher?
            $coursesarray = explode(',', $courses);
            foreach ($coursesarray as $course) {
                $context = \context_course::instance($course);
                if (!has_capability('moodle/course:managegroups', $context, $userid)) {
                    $result['error']['code'] = 'user_not_enrolled_as_teacher';
                    $result['error']['message'] = 'User not enrolled on course id=' . $course . ', as teacher';
                    return $result;
                }
            }
            // Check model type.
            $certifygenmodel = new certifygen_model($modelid);
            if ($certifygenmodel->get('type') == certifygen_model::TYPE_ACTIVITY) {
                $result['error']['code'] = 'model_type_assigned_to_activity';
                $result['error']['message'] = 'This model is assigned for activities.';
                return $result;
            }
            // Check certificate report.
            $reportplugin = $certifygenmodel->get('report');
            if (empty($reportplugin)) {
                $result['error']['code'] = 'no_reportplugin_set';
                $result['error']['message'] = 'This model has no report plugin set';
                return $result;
            }
            // Check if request exists.
            $trequest = certifygen_validations::get_request_by_data_for_teachers($userid, $courses, $lang, $modelid, $name);
            $id = 0;
            if ($trequest) {
                $id = $trequest->id;
            } else {
                // Create teacher request.
                $data = [
                    'name' => $name,
                    'modelid' => $modelid,
                    'status' => certifygen_validations::STATUS_IN_PROGRESS,
                    'lang' => $lang,
                    'courses' => $courses,
                    'userid' => $userid,
                    'certifygenid' => 0,
                ];
                $trequest = certifygen_validations::manage_validation($id, (object) $data);
                $id = $trequest->get('id');
                // Emit teacher request.
                $output = emitteacherrequest_external::emitteacherrequest($id);
            }
            // Ask again in case status has changed.
            $trequest = new certifygen_validations($id);
            if ((int)$trequest->get('status') === certifygen_validations::STATUS_FINISHED) {
                // Get file.
                $validationplugin = $certifygenmodel->get('validation');
                $validationpluginclass = $validationplugin . '\\' . $validationplugin;
                $code = $trequest->get('code') . '.pdf';
                if (empty($validationplugin)) {
                    // Get file from moodledata.
//                    $code = ICertificateReport::FILE_NAME_STARTSWITH . $trequest->get('id') . '.pdf';
                    $fs = get_file_storage();
                    $contextid = \context_system::instance()->id;
                    $file = $fs->get_file($contextid, ICertificateReport::FILE_COMPONENT,
                        ICertificateReport::FILE_AREA, $trequest->get('id'), ICertificateReport::FILE_PATH, $code);
                } else if (get_config($validationplugin, 'enabled') === '1') {
                    /** @var ICertificateValidation $subplugin */
                    $subplugin = new $validationpluginclass();
                    $file = $subplugin->getFile(0, $trequest->get('id'), $code);
                } else {
                    $result['error']['code'] = 'validation_plugin_not_enabled';
                    $result['error']['message'] = 'Certificate validation plugin is not enabled';
                    return $result;
                }
            } else {
                $result['error']['code'] = 'certificate_not_ready';
                $result['error']['message'] = 'Certificate validation status is: ' . $trequest->get('status');
                return $result;
            }

        } catch (\moodle_exception $e) {
            $result['error']['code'] = 'teacherrequest_pdf_can_not_be_obtained';
            $result['error']['message'] = $e->getMessage();
            return $result;
        }
        $certificate = [
            'validationid' => $id,
            'status' => $trequest->get('status'),
            'file' => $file->get_contenthash(),
            'reporttype' => $certifygenmodel->get('type'),
            'reporttypestr' => get_string('status_' . $certifygenmodel->get('type'), 'mod_certifygen'),
        ];
        return ['certificate' => $certificate];
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_pdf_teacher_certificate_returns(): external_single_structure {
        return new external_single_structure([
            'certificate' => new external_single_structure(
                [
                    'validationid'   => new external_value(PARAM_INT, 'Valiation id'),
                    'status'   => new external_value(PARAM_INT, 'Teacher request status'),
                    'file' => new external_value(PARAM_CLEANFILE, 'certificate'),
                    'reporttype' => new external_value(PARAM_INT, 'model type'),
                ], 'Certificate info', VALUE_OPTIONAL),
            'error' => new external_single_structure([
                'message' => new external_value(PARAM_RAW, 'Error message'),
                'code' => new external_value(PARAM_RAW, 'Error code'),
            ], 'Errors information', VALUE_OPTIONAL),
        ]);
    }
}