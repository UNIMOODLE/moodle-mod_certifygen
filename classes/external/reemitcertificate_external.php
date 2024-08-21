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
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
require_once($CFG->dirroot . '/mod/certifygen/externalcompatibility.php');

class reemitcertificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function reemitcertificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'id'),
            ]
        );
    }

    /**
     * @param int $id
     * @return array
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws invalid_persistent_exception
     */
    public static function reemitcertificate(int $id): array {

        global $PAGE, $USER;
        $PAGE->set_context(context_system::instance());
        self::validate_parameters(
            self::reemitcertificate_parameters(), ['id' => $id]
        );
        $result = ['result' => true, 'message' => get_string('ok', 'mod_certifygen')];

        try {
            // Step 1: Copy data from the old validation id.
            error_log(__FUNCTION__ . ' ' . ' id: '.var_export($id, true));
            $oldvalidation  = new certifygen_validations($id);
            error_log(__FUNCTION__ . ' ' . ' oldvalidation: '.var_export($oldvalidation, true));
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
            // Remove the old one.
            $oldvalidation->delete();
            // Create a new one.
            $validation = certifygen_validations::manage_validation(0, (object) $data);
            // Emit the new one.
            error_log(__FUNCTION__ . ' ' . ' validation: '.var_export($validation, true));
            $certifygen= new certifygen($validation->get('certifygenid'));
            $result = emitcertificate_external::emitcertificate($validation->get('id'), $validation->get('certifygenid'),
                $validation->get('modelid'), $validation->get('lang'), $validation->get('userid'), $certifygen->get('course'));
            error_log(__FUNCTION__ . ' ' . ' result: '.var_export($result, true));

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
    public static function reemitcertificate_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'model deleted'),
                'message' => new external_value(PARAM_RAW, 'meesage', VALUE_OPTIONAL),
            ]
        );
    }
}