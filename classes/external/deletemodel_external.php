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
 * @author     Idef21 <https://idef21.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
use context_system;
use context_module;
use dml_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_certifygen\persistents\certifygen_model;
use moodle_exception;
require_once($CFG->dirroot . '/mod/certifygen/lib.php');
/**
 * Delete model
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deletemodel_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function deletemodel_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'model id'),
            ]
        );
    }

    /**
     * Delete model
     *
     * @param int $id
     * @return array
     * @throws invalid_parameter_exception|dml_exception
     */
    public static function deletemodel(int $id): array {

        self::validate_parameters(
            self::deletemodel_parameters(),
            ['id' => $id]
        );
        $context = context_module::instance($id);
        self::validate_context($context);
        require_capability('mod/certifygen:addinstance', $context);
        $result = ['result' => true, 'message' => get_string('ok', 'mod_certifygen')];
        try {
            if (!has_capability('mod/certifygen:manage', $context)) {
                return ['result' => false, 'message' => get_string('nopermissiondeletemodel', 'mod_certifygen')];
            }
            $cemited = mod_certifygen_are_there_any_certificate_emited($id);
            if ($cemited) {
                return ['result' => false, 'message' => get_string('cannotdeletemodelcertemited', 'mod_certifygen')];
            }
            $model = new certifygen_model($id);
            $model->delete();
        } catch (moodle_exception $e) {
            $result['result'] = false;
            $result['message'] = $e->getMessage();
        }

        return $result;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function deletemodel_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'model deleted'),
                'message' => new external_value(PARAM_RAW, 'meesage'),
            ]
        );
    }
}
