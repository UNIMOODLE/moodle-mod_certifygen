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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
use coding_exception;
use context_course;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use invalid_parameter_exception;
use mod_certifygen\event\certificate_revoked;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use required_capability_exception;
use tool_certificate\template;

/**
 * Revoke certificate
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
     * Revoke certificate
     * @param int $issueid
     * @param int $userid
     * @param int $modelid
     * @return array
     * @throws coding_exception
     * @throws invalid_parameter_exception
     */
    public static function revokecertificate(int $issueid, int $userid, int $modelid): array {
        global $DB, $USER;

        self::validate_parameters(
            self::revokecertificate_parameters(),
            ['issueid' => $issueid, 'userid' => $userid, 'modelid' => $modelid]
        );
        $result = ['result' => true, 'message' => get_string('ok', 'mod_certifygen')];

        // Step 1: Find validation id..
        $data = [
            'issueid' => $issueid,
            'userid' => $userid,
            'modelid' => $modelid,
        ];
        $validation = null;
        try {
            if ($USER->id == $userid) {
                $result['result'] = false;
                $result['message'] = get_string('nopermissiontorevokecerts', 'mod_certifygen');
                return $result;
            }
            // Step 2: Remove tool_certificate_issues record.
            $issue = $DB->get_record('tool_certificate_issues', ['id' => $issueid], '*', MUST_EXIST);
            $template = template::instance($issue->templateid);
            // Make sure the user has the required capabilities.
            $context = context_course::instance($issue->courseid, IGNORE_MISSING) ?: $template->get_context();
            self::validate_context($context);
            if (!$template->can_revoke($issue->userid, $context)) {
                throw new required_capability_exception(
                    $template->get_context(),
                    'tool/certificate:issue',
                    'nopermissions',
                    'error'
                );
            }

            // Step 4: Delete the issue.
            $template->revoke_issue($issueid);

            // Step 5: Remove validation id.
            $validation = certifygen_validations::get_record($data, MUST_EXIST);
            $model = new certifygen_model($validation->get('modelid'));
            $eventdata = [
                'objectid' => $validation->get('id'),
                'userid' => $USER->id,
                'context' => $context,
                'other' => [
                    'validation' => $model->get('validation'),
                    'repository' => $model->get('repository'),
                    'report' => $model->get('report'),
                ],
            ];
            certificate_revoked::create($eventdata)->trigger();
            $validation->delete();
        } catch (moodle_exception $e) {
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $result['result'] = false;
            $result['message'] = $e->getMessage();
            if (!is_null($validation)) {
                $validation->set('status', certifygen_validations::STATUS_ERROR);
                $validation->save();
            }
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
