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


use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

class get_id_instance_certificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_id_instance_certificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
            ]
        );
    }
    public static function get_id_instance_certificate(int $userid): array {
        /**
         * Devuelve una lista de aquellas instancias de mod_certificate visibles,
         * con restricciones verificadas a las que el usuario puede acceder y generar el certificado de acuerdo
         * con la configuración de la instancia.
         */
        $instances = [];
        return ['instances' => $instances];
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_id_instance_certificate_returns(): external_single_structure {
        return new external_single_structure(array(
                'instances' => new external_multiple_structure( new external_single_structure(
                        [
                            'course'   => new external_single_structure([
                                'shortname' => new external_value(PARAM_RAW, 'Course shortname'),
                                'fullname' => new external_value(PARAM_RAW, 'Course fullname'),
                                'categoryid' => new external_value(PARAM_RAW, 'Category id'),
                            ], 'Course information'),
                            'instance'   => new external_single_structure([
                                'name' => new external_value(PARAM_RAW, 'Instance name'),
                                'modelname' => new external_value(PARAM_RAW, 'Model name'),
                                'modelmode' => new external_value(PARAM_INT, 'Model mode'),
                                'modeltimeondemmand' => new external_value(PARAM_INT, 'Model timeondemmand'),
                                'modeltype' => new external_value(PARAM_INT, 'Model type'),
                                'modeltemplateid' => new external_value(PARAM_INT, 'Model template id'),
                                'modellangs' => new external_value(PARAM_RAW, 'Model langs'),
                                'modelvalidation' => new external_value(PARAM_RAW, 'Model validation'),
                            ], 'Module Instance information'),
                        ], 'Module Instances list')
                ),
            )
        );
    }
}