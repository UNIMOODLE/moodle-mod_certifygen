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

class revokecertificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function revokecertificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'issueid' => new external_value(PARAM_INT, 'tool_certificate_issues id'),
                'userid' => new external_value(PARAM_INT, 'user id'),
                'modelid' => new external_value(PARAM_INT, 'model id'),
            ]
        );
    }

    /**
     * @param int $issueid
     * @param int $userid
     * @param int $modelid
     * @return array
     * @throws coding_exception
     * @throws invalid_parameter_exception
     */
    public static function revokecertificate(int $issueid, int $userid, int $modelid): array {
        global $DB;

        self::validate_parameters(
            self::revokecertificate_parameters(), ['issueid' => $issueid, 'userid' => $userid, 'modelid' => $modelid]
        );
        $result = ['result' => true, 'message' => 'OK'];

        // Step 1: Find validation id..
        $data = [
            'issueid' => $issueid,
            'userid' => $userid,
            'modelid' => $modelid,
        ];
        $validation = null;
        try {
            // Step 2: Remove tool_certificate_issues record
            $issue = $DB->get_record('tool_certificate_issues', ['id' => $issueid], '*', MUST_EXIST);
            $template = \tool_certificate\template::instance($issue->templateid);
            // Make sure the user has the required capabilities.
            $context = \context_course::instance($issue->courseid, IGNORE_MISSING) ?: $template->get_context();
            self::validate_context($context);
            if (!$template->can_revoke($issue->userid, $context)) {
                throw new \required_capability_exception($template->get_context(), 'tool/certificate:issue', 'nopermissions', 'error');
            }

            // Step 4: Delete the issue.
            $template->revoke_issue($issueid);

            // Step 5: Remove validation id.
            try {
                $validation = certifygen_validations::get_record($data, MUST_EXIST);
                $validation->delete();
            } catch (moodle_exception $exception) {
                error_log(__FUNCTION__ . ' ' . __LINE__ . ' validation - getMessage: '.var_export($exception->getMessage(), true));
            }
        } catch (moodle_exception $e) {
            $result['result'] = false;
            $result['message'] = $e->getMessage();
            if (!is_null($validation)) {
                $validation->set('status', certifygen_validations::STATUS_FINISHED_ERROR);
                $validation->save();
            }
            $validation->set('status', certifygen_validations::STATUS_FINISHED_ERROR);
            $validation->save();
        }

        return $result;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function revokecertificate_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'model deleted'),
                'message' => new external_value(PARAM_RAW, 'meesage'),
            ]
        );
    }

}