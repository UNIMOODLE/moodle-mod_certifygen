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

use coding_exception;
use context_system;
use core\invalid_persistent_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateReport;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;

class reemitteacherrequest_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function reemitteacherrequest_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'id'),
            ]
        );
    }

    /**
     * @param int $id
     * @return array
     * @throws \dml_exception
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws invalid_persistent_exception
     */
    public static function reemitteacherrequest(int $id): array {

        global $PAGE, $USER;
        $PAGE->set_context(context_system::instance());
        self::validate_parameters(
            self::reemitteacherrequest_parameters(), ['id' => $id]
        );
        $result = ['result' => true, 'message' => get_string('ok', 'mod_certifygen')];

        try {
            // Step 1: Copy data from the old validation id.
            $oldvalidation  = new certifygen_validations($id);
            $data = [
                'name' => $oldvalidation->get('name'),
                'courses' => $oldvalidation->get('courses'),
                'certifygenid' => $oldvalidation->get('certifygenid'),
                'issueid' => $oldvalidation->get('issueid'),
                'userid' => $oldvalidation->get('userid'),
                'modelid' => $oldvalidation->get('modelid'),
                'lang' => $oldvalidation->get('lang'),
                'status' => certifygen_validations::STATUS_IN_PROGRESS,
                'usermodified' => $USER->id,
            ];
            $teacherrequest = certifygen_validations::manage_validation(0, (object) $data);
            $certifygenmodel = new certifygen_model($teacherrequest->get('modelid'));
            $reportplugin = $certifygenmodel->get('report');
            if (empty($reportplugin)) {
                $result['result'] = false;
                $result['message'] = 'report plugin must be set on the model';
                return $result;
            }
            $reportpluginclass = $reportplugin . '\\' . $reportplugin;
            /** @var ICertificateReport $subplugin */
            $subplugin = new $reportpluginclass();
            $result = $subplugin->createFile($teacherrequest);
            if (!$result['result']) {
                $teacherrequest->set('status', certifygen_validations::STATUS_ERROR);
                $teacherrequest->save();
                return $result;
            }
            $file = $result['file'];
            $userid = $teacherrequest->get('userid');
            $lang = $teacherrequest->get('lang');
            $modelid = $teacherrequest->get('modelid');
            $certifygenfile = new certifygen_file($file, $userid, $lang, $modelid, $id);
            $data = [
                'lang' => $lang,
                'user_id' => $userid,
                'user_fullname' => fullname($certifygenfile->get_user()),
            ];
            $certifygenfile->set_metadata($data);
            // Step 3: Call to validation plugin.
            $certifygenmodel = new certifygen_model($teacherrequest->get('modelid'));
            $validationplugin = $certifygenmodel->get('validation');
            $validationpluginclass = $validationplugin . '\\' . $validationplugin;
            if (empty($validationplugin)) {
                $teacherrequest->set('status', certifygen_validations::STATUS_VALIDATION_OK);
                $teacherrequest->save();
            } else if (get_config($validationplugin, 'enabled') === '1') {
                /** @var ICertificateValidation $subplugin */
                $subplugin = new $validationpluginclass();
                $response = $subplugin->sendFile($certifygenfile);
                if ($response['haserror']) {
                    $result['result'] = false;
                    $result['message'] = $response['message'];
                    if (!array_key_exists('message', $response)) {
                        $result['message'] = 'validation_plugin_send_file_error';
                    }
                    $teacherrequest->set('status', certifygen_validations::STATUS_VALIDATION_ERROR);
                    $teacherrequest->save();
                } else if (!$subplugin->checkStatus()) {
                    $teacherrequest->set('status', certifygen_validations::STATUS_VALIDATION_OK);
                    $teacherrequest->save();
                }
            }
            unset($result['file']);
        } catch (moodle_exception $e) {
            error_log(__FUNCTION__ . ' ' . ' error: '.var_export($e->getMessage(), true));
            $result['result'] = false;
            $result['message'] = $e->getMessage();
            $teacherrequest->set('status', certifygen_validations::STATUS_ERROR);
            $teacherrequest->save();
        }
        return $result;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function reemitteacherrequest_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'model deleted'),
                'message' => new external_value(PARAM_RAW, 'meesage', VALUE_OPTIONAL),
            ]
        );
    }
}