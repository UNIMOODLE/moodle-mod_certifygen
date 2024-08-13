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

/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\external;

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');

use coding_exception;
use core\invalid_persistent_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_certifygen\certifygen;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;

class emitcertificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function emitcertificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'id'),
                'instanceid' => new external_value(PARAM_INT, 'instance id'),
                'modelid' => new external_value(PARAM_INT, 'model id'),
                'lang' => new external_value(PARAM_RAW, 'model lang'),
                'userid' => new external_value(PARAM_RAW, 'user id'),
                'courseid' => new external_value(PARAM_RAW, 'course id'),
            ]
        );
    }

    /**
     * @param int $id
     * @param int $instanceid
     * @param int $modelid
     * @param string $lang
     * @param int $userid
     * @param int $courseid
     * @return array
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws invalid_persistent_exception
     */
    public static function emitcertificate(int $id, int $instanceid, int $modelid, string $lang, int $userid, int $courseid): array {
        global $USER;

        self::validate_parameters(
            self::emitcertificate_parameters(), ['id' => $id, 'instanceid' => $instanceid, 'modelid' => $modelid, 'lang' => $lang, 'userid' => $userid, 'courseid' => $courseid]
        );

        $result = ['result' => true, 'message' => get_string('ok', 'mod_certifygen')];

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
        if ($id > 0) {
            $validation  = new certifygen_validations($id);
            if ($validation->get('status') != certifygen_validations::STATUS_NOT_STARTED
            && $validation->get('status') != certifygen_validations::STATUS_ERROR) {
                $result['result'] = false;
                $result['message'] = 'Certificate can not be emitted again';
                return $result;
            }
        }
        $validation = certifygen_validations::manage_validation($id, (object) $data);
        try {
            // Step 2: Generate issue.
            $users = user_get_users_by_id([$userid]);
            $user = reset($users);
            $certifygenmodel = new certifygen_model($modelid);
            $course = get_course($courseid);
            $issueid = certifygen::issue_certificate($instanceid, $user, $certifygenmodel->get('templateid'), $course, $lang);
            $saved = false;
            if ($issueid) {
                $saved = true;
                $validation->set('issueid', $issueid);
                $validation->save();
            }
            if ($existingcertificate = certifygen::get_user_certificate($instanceid, $userid, $courseid, $certifygenmodel->get('templateid'), $lang)) {
                if (!$saved) {
                    $saved = true;
                    $validation->set('issueid', $existingcertificate->id);
                    $validation->save();
                }
            }

            // Step 3: Generate the tool_certificate certificate.
            $file = certifygen::get_user_certificate_file($instanceid, $certifygenmodel->get('templateid'), $userid, $courseid, $lang);

            if (is_null($file)) {
                $result['result'] = false;
                $result['message'] = 'File not found';
            } else {
                $certifygenfile = new certifygen_file($file, $userid, $lang, $modelid, $validation->get('id'));
                $data = [
                    'lang' => $lang,
                    'user_id' => $userid,
                    'user_fullname' => fullname($user),
                    'courseid' => $courseid,
                    'course_fullname' => $course->fullname,
                    'course_shortname' => $course->shortname,
                ];
                $certifygenfile->set_metadata($data);
                // Step 4: Call to validation plugin.
                $validationplugin = $certifygenmodel->get('validation');
                $validationpluginclass = $validationplugin . '\\' . $validationplugin;
                if (empty($validationplugin)) {
                    // TODO: change to STATUS_VALIDATION_OK
                    $validation->set('status', certifygen_validations::STATUS_FINISHED);
                    $validation->save();
                } else if (get_config($validationplugin, 'enabled') === '1') {
                    /** @var ICertificateValidation $subplugin */
                    $subplugin = new $validationpluginclass();
                    $response = $subplugin->sendFile($certifygenfile);
                    if ($response['haserror']) {
                        if (!array_key_exists('message', $result)) {
                            $result['message'] = 'validation_plugin_send_file_error';
                        }
                        $validation->set('status', certifygen_validations::STATUS_VALIDATION_ERROR);
                        $validation->save();
                    } else if (!$subplugin->checkStatus()) {
                        $validation->set('status', certifygen_validations::STATUS_VALIDATION_OK);
                        $validation->save();
                    }
                } else {
                    $result['result'] = false;
                    $result['message'] = 'plugin_not_enabled';
                    $validation->set('status', certifygen_validations::STATUS_ERROR);
                    $validation->save();
                }
            }
        } catch (moodle_exception $e) {
            error_log(__FUNCTION__ . ' ' . ' error: '.var_export($e->getMessage(), true));
            $result['result'] = false;
            $result['message'] = $e->getMessage();
            $validation->set('status', certifygen_validations::STATUS_ERROR);
            $validation->save();
        }
        return $result;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function emitcertificate_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'certificate emited.'),
                'message' => new external_value(PARAM_RAW, 'meesage'),
            ]
        );
    }

}