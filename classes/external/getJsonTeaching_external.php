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

/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\external;


use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

class getJsonTeaching_external extends external_api {
    public static function getJsonTeaching_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'dni' => new external_value(PARAM_RAW, 'user dni'),
                'courseid' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }
    public static function getJsonTeaching(string $dni, int $courseid): array {
        /**
         * Devuelve un json con la información necesaria para el anterior servicio para
         * confeccionar el certificado. El objetivo de este servicio es independizar el proceso de
         * obtención de los datos del proceso de generación del documento con la presentación
         * final.
         */
        return ['json' => '{"elemento1":"imagen"}'];
    }
    public static function getJsonTeaching_returns(): external_single_structure {
        return new external_single_structure(array(
                'json' => new external_value(PARAM_RAW, 'Certificate elements in a json'),
            )
        );
    }
}