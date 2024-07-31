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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos..
/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\external;


use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

class notify_certification_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function notify_certification_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
                'idinstance' => new external_value(PARAM_INT, 'instance id'),
                'datos' => new external_value(PARAM_RAW, 'datos'),
            ]
        );
    }

    /**
     * @param int $userid
     * @param int $idinstance
     * @param string $datos
     * @return string[]
     */
    public static function notify_certification(int $userid, int $issueid, string $datos): array {
        /**
         * El sistema externo notificará a Moodle (en concreto a la instancia de subplugin repositorio que esté asociado
         * al idInstance), proporcionando la información del certificado en el parámetro “datos”
         * (url de almacenaje, tamaño fichero, etc).
         * El plugin de repositorio utilizará esta información para gestionar el resultado del certificado y mostrar 
         * la información visible por el usuario al acceder a dicha instancia de certificado dentro de su curso de Moodle.
 */
        // NO mirar las restricciones de la actividad.
        return ['status' => 'OK'];
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function notify_certification_returns(): external_single_structure {
        return new external_single_structure([
                'status' => new external_value(PARAM_RAW, 'status'),
            ]);
    }
}