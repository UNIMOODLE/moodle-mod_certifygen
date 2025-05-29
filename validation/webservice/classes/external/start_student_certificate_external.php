<?php
// This file is part of the certifygenvalidation_webservice plugin for Moodle - http://moodle.org/
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
 *
 * @package    certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace certifygenvalidation_webservice\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/lib.php');

use certifygenvalidation_webservice\certifygenvalidation_webservice;
use coding_exception;
use context_module;
use core\invalid_persistent_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_certifygen\certifygen;
use mod_certifygen\event\certificate_issued;
use mod_certifygen\persistents\certifygen_error;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
/**
 * Issue student certificate
 * @package    certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class start_student_certificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function start_student_certificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'Validation id'),
                'instanceid' => new external_value(PARAM_INT, 'instance id'),
                'lang' => new external_value(PARAM_RAW, 'model lang'),
                'userid' => new external_value(PARAM_RAW, 'user id'),
                'userfield' => new external_value(PARAM_RAW, 'user field'),
            ]
        );
    }

    /**
     * start_student_certificate
     * @param int $id
     * @param int $instanceid
     * @param string $lang
     * @param int $userid
     * @param string $userfield
     * @return array
     * @throws \dml_exception
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws invalid_persistent_exception
     * @throws moodle_exception
     */
    public static function start_student_certificate(
        int $id,
        int $instanceid,
        string $lang,
        int $userid,
        string $userfield
    ): array {
        global $USER;

        $params = self::validate_parameters(
            self::start_student_certificate_parameters(),
            [
                'id' => $id,
                'instanceid' => $instanceid,
                'lang' => $lang,
                'userid' => $userid,
                'userfield' => $userfield,
            ]
        );

        $result = ['result' => true, 'id' => 0, 'message' => get_string('ok', 'mod_certifygen')];
        // Choose user parameter.
        $uparam = mod_certifygen_validate_user_parameters_for_ws($params['userid'], $params['userfield']);
        if (array_key_exists('error', $uparam)) {
            return $uparam;
        }
        $userid = $uparam['userid'];
        [$course, $cm] = get_course_and_cm_from_instance($instanceid, 'certifygen');

        // Get model.
        $modelid = \mod_certifygen\persistents\certifygen::get_modelid_from_certifygenid($instanceid);

        // Step 1: Change status to in progress.
        $data = [
            'userid' => $userid,
            'certifygenid' => $instanceid,
            'lang' => $lang,
            'modelid' => $modelid,
            'status' => certifygen_validations::STATUS_IN_PROGRESS,
            'issueid' => null,
            'usermodified' => $USER->id,
        ];
        $validation = null;
        if ($id > 0) {
            $validation  = new certifygen_validations($id);
        } else {
            // Search if the certificate already exists.
            $info = ['userid' => $userid, 'certifygenid' => $instanceid, 'modelid' => $modelid];
            $validations = certifygen_validations::get_records($info);
            foreach ($validations as $v) {
                if ($v->get('lang') == $data['lang']) {
                    $validation = $v;
                    break;
                }
            }
        }
        if (
            $validation && $validation->get('status') != certifygen_validations::STATUS_NOT_STARTED
        ) {
            $result['result'] = false;
            $result['id'] = $validation->get('id');
            $result['message'] = get_string('cannotreemit', 'mod_certifygen');
            return $result;
        }

        try {
            if (is_null($validation)) {
                $validation = certifygen_validations::manage_validation($id, (object) $data);
            }
            // Step 2: Generate issue.
            $users = user_get_users_by_id([$userid]);
            $user = reset($users);
            $certifygenmodel = new certifygen_model($modelid);
            if ($certifygenmodel->get('validation') != 'certifygenvalidation_webservice') {
                $result['result'] = false;
                unset($result['id']);
                unset($result['message']);
                $result['error']['code'] = 'validationplugin_not_accepted';
                $result['error']['message'] = get_string('validationplugin_not_accepted', 'certifygenvalidation_webservice');
                return $result;
            }
            if ($certifygenmodel->get('repository') != 'certifygenrepository_url') {
                $result['result'] = false;
                unset($result['id']);
                unset($result['message']);
                $result['error']['code'] = 'repositoryplugin_not_accepted';
                $result['error']['message'] = get_string('repositoryplugin_not_accepted', 'certifygenvalidation_webservice');
                return $result;
            }
            // Check if lang exists on model configuration.
            $validlangs = explode(',', $certifygenmodel->get('langs'));
            if (!in_array($lang, $validlangs)) {
                $result['result'] = false;
                unset($result['id']);
                unset($result['message']);
                $result['error']['code'] = 'invalid_language';
                $result['error']['message'] = get_string('invalid_language', 'mod_certifygen');
                return $result;
            }

            $issueid = certifygen::issue_certificate(
                $instanceid,
                $user,
                $certifygenmodel->get('templateid'),
                $course,
                $lang
            );
            $saved = false;
            if ($issueid) {
                $saved = true;
                $validation->set('issueid', $issueid);
                $validation->save();
            }
            if (
                $existingcertificate = certifygen::get_user_certificate(
                    $instanceid,
                    $userid,
                    $course->id,
                    $certifygenmodel->get('templateid'),
                    $lang
                )
            ) {
                if (!$saved) {
                    $saved = true;
                    $validation->set('issueid', $existingcertificate->id);
                    $validation->save();
                }
            }

            // Step 3: Generate the tool_certificate certificate.
            $file = certifygen::get_user_certificate_file(
                $instanceid,
                $certifygenmodel->get('templateid'),
                $userid,
                $course->id,
                $lang
            );
            if (is_null($file)) {
                $result['result'] = false;
                $result['message'] = get_string('file_not_found', 'mod_certifygen');
                $validation->set('status', certifygen_validations::STATUS_STUDENT_ERROR);
                $validation->save();
                $data = [
                    'validationid' => $validation->get('id'),
                    'status' => $validation->get('status'),
                    'code' => 'file_not_found',
                    'message' => $result['message'],
                    'usermodified' => $USER->id,
                ];
                certifygen_error::manage_certifygen_error(0, (object)$data);
            } else {
                // Step 4: Save file on moodledata.
                $cvalidation = new certifygenvalidation_webservice();
                $cvalidation->save_file_moodledata($validation->get('id'));
                $validation->set('status', certifygen_validations::STATUS_IN_PROGRESS);
                $validation->update();

                $result['id'] = $validation->get('id');
                // Step 5: event trigger.
                if ($result['result']) {
                    certificate_issued::create_from_validation($validation)->trigger();
                }
            }
        } catch (moodle_exception $e) {
            $result['result'] = false;
            $result['error']['code'] = $e->getCode();
            $result['error']['message'] = $e->getMessage();
            $id = 0;
            $status = certifygen_validations::STATUS_NOT_STARTED;
            if (!is_null($validation)) {
                $validation->set('status', certifygen_validations::STATUS_ERROR);
                $validation->save();
                $id = $validation->get('id');
                $status = $validation->get('status');
            }
            $data = [
                'validationid' => $id,
                'status' => $status,
                'code' => $e->getCode(),
                'message' => $result['message'],
                'usermodified' => $USER->id,
            ];
            certifygen_error::manage_certifygen_error(0, (object)$data);
        }
        return $result;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function start_student_certificate_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'certificate emited.'),
                'id' => new external_value(PARAM_INT, 'Validation id', VALUE_OPTIONAL),
                'message' => new external_value(PARAM_RAW, 'meesage', VALUE_OPTIONAL),
                'error' => new external_single_structure([
                        'message' => new external_value(PARAM_RAW, 'Error message', VALUE_OPTIONAL),
                        'code' => new external_value(PARAM_RAW, 'Error code', VALUE_OPTIONAL),
                ], 'Errors information', VALUE_OPTIONAL),
            ]
        );
    }
}
