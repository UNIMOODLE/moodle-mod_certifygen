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
 * WS Get pdf certificate
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen\external;
use context_course;
use context_system;
use dml_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use required_capability_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/lib.php');
/**
 * Get studetns certificate (issue it if it is not already created)
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_pdf_certificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_pdf_certificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
                'userfield' => new external_value(PARAM_RAW, 'user field'),
                'idinstance' => new external_value(PARAM_INT, 'instance id'),
                'lang' => new external_value(PARAM_LANG, 'lang'),
                'customfields' => new external_value(PARAM_RAW, 'customfields'),
            ]
        );
    }

    /**
     * Get student certificate (issue certificate if it is necessary)
     * @param int $userid
     * @param string $userfield
     * @param int $idinstance
     * @param string $lang
     * @param string $customfields
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public static function get_pdf_certificate(
        int $userid,
        string $userfield,
        int $idinstance,
        string $lang,
        string $customfields
    ): array {

        $params = self::validate_parameters(
            self::get_pdf_certificate_parameters(),
            ['userid' => $userid, 'userfield' => $userfield, 'idinstance' => $idinstance, 'lang' => $lang,
                'customfields' => $customfields]
        );
        $context = context_system::instance();
        require_capability('mod/certifygen:manage', $context);
        try {
            // Choose user parameter.
            $uparam = mod_certifygen_validate_user_parameters_for_ws($params['userid'], $params['userfield']);
            if (array_key_exists('error', $uparam)) {
                return $uparam;
            }
            $userid = $uparam['userid'];

            // User exists.
            $user = user_get_users_by_id([$userid]);
            if (empty($user)) {
                $result['error']['code'] = 'user_not_found';
                $result['error']['message'] = get_string('user_not_found', 'mod_certifygen');
                return $result;
            }
            // Activity exists?.
            $certifygen = new certifygen($params['idinstance']);

            // Is user enrolled on this course as student?
            $context = context_course::instance($certifygen->get('course'));
            if (!has_capability('mod/certifygen:emitmyactivitycertificate', $context, $userid)) {
                $result['error']['code'] = 'user_not_enrolled_on_idinstance_course_as_student';
                $result['error']['message'] = get_string(
                    'student_not_enrolled',
                    'mod_certifygen',
                    $certifygen->get('course')
                );
                return $result;
            }

            // Model info.
            $model = new certifygen_model(certifygen::get_modelid_from_certifygenid($params['idinstance']));

            // Already emtited?
            $validation = certifygen_validations::get_validation_by_lang_and_instance($lang, $idinstance, $userid);
            if (is_null($validation)) {
                // Emit certificate.
                $result = emitcertificate_external::emitcertificate(
                    0,
                    $idinstance,
                    $model->get('id'),
                    $lang,
                    $userid,
                    $certifygen->get('course')
                );
                if (!$result['result']) {
                    unset($result['file']);
                    $result['error']['code'] = 'certificate_can_not_be_emited';
                    $result['error']['message'] = $result['message'];
                    return $result;
                }
            }
            // Ask again in case status has changed.
            $validation = certifygen_validations::get_validation_by_lang_and_instance($lang, $idinstance, $userid);
            if ((int)$validation->get('status') === certifygen_validations::STATUS_FINISHED) {
                // Get file.
                $repositoryplugin = $model->get('repository');
                $repositorypluginclass = $repositoryplugin . '\\' . $repositoryplugin;
                if (get_config($repositoryplugin, 'enabled') === '1') {
                    /** @var ICertificateRepository $subplugin */
                    $subplugin = new $repositorypluginclass();
                    $filecontent = $subplugin->get_file_content($validation);
                    $filecontent = base64_encode($filecontent);
                    if (empty($filecontent)) {
                        $result['error']['code'] = 'file_url_empty';
                        $result['error']['message'] = get_string('empty_repository_url', 'mod_certifygen');
                        return $result;
                    }
                } else {
                    $result['error']['code'] = 'validation_plugin_not_enabled';
                    $result['error']['message'] = get_string('validationplugin_not_enabled', 'mod_certifygen');
                    return $result;
                }
            } else {
                $result['error']['code'] = 'status_not_finished';
                $result['error']['message'] = get_string('statusnotfinished', 'mod_certifygen');
                return $result;
            }
        } catch (moodle_exception $e) {
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $result['error']['code'] = $e->errorcode;
            $result['error']['message'] = $e->getMessage();
            return $result;
        }
        $certificate = [
                'validationid' => $validation->get('id'),
                'status' => $validation->get('status'),
                'statusstr' => get_string('status_' . $validation->get('status'), 'mod_certifygen'),
                'file' => $filecontent,
                'reporttype' => $model->get('type'),
                'reporttypestr' => get_string('type_' . $model->get('type'), 'mod_certifygen'),
        ];

        return ['certificate' => $certificate];
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_pdf_certificate_returns(): external_single_structure {
        return new external_single_structure([
                'certificate' => new external_single_structure(
                    [
                                'validationid'   => new external_value(PARAM_INT, 'Valiation id'),
                                'status'   => new external_value(PARAM_INT, 'Teacher request status'),
                                'statusstr'   => new external_value(PARAM_RAW, 'Teacher request status'),
                                'file' => new external_value(PARAM_RAW, 'certificate'),
                                'reporttype' => new external_value(PARAM_INT, 'report type'),
                                'reporttypestr' => new external_value(PARAM_RAW, 'report type'),
                        ],
                    'Certificate info',
                    VALUE_OPTIONAL
                ),
                'error' => new external_single_structure(
                    [
                        'message' => new external_value(PARAM_CLEANFILE, 'Error message'),
                        'code' => new external_value(PARAM_RAW, 'Error code'),
                    ],
                    'Errors information',
                    VALUE_OPTIONAL
                ),
            ]);
    }
}
