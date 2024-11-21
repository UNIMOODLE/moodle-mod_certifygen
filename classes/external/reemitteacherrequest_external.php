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

use coding_exception;
use context_system;
use dml_exception;
use invalid_parameter_exception;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;

/**
 * Re emit teacher certificate
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reemitteacherrequest_external extends \core_external\external_api {
    /**
     * Describes the external function parameters.
     *
     * @return \core_external\external_function_parameters
     */
    public static function reemitteacherrequest_parameters(): \core_external\external_function_parameters {
        return new \core_external\external_function_parameters(
            [
                'id' => new \core_external\external_value(PARAM_INT, 'id'),
            ]
        );
    }

    /**
     * Reemit certificate
     * @param int $id
     * @return array
     * @throws dml_exception
     * @throws coding_exception
     * @throws invalid_parameter_exception
     */
    public static function reemitteacherrequest(int $id): array {

        global $PAGE, $USER;
        $PAGE->set_context(context_system::instance());
        self::validate_parameters(
            self::reemitteacherrequest_parameters(),
            ['id' => $id]
        );
        $result = ['result' => true, 'message' => get_string('ok', 'mod_certifygen')];
        $teacherrequest = null;
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
            $result = emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));
        } catch (moodle_exception $e) {
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
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
     * @return \core_external\external_single_structure
     */
    public static function reemitteacherrequest_returns(): \core_external\external_single_structure {
        return new \core_external\external_single_structure(
            [
                'result' => new \core_external\external_value(PARAM_BOOL, 'model deleted'),
                'message' => new \core_external\external_value(PARAM_RAW, 'meesage', VALUE_OPTIONAL),
            ]
        );
    }
}
