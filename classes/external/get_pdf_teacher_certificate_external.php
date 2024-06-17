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
use mod_certifygen\persistents\certifygen_model;

class get_pdf_teacher_certificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_pdf_teacher_certificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
                'courses' => new external_value(PARAM_RAW, 'course list'), // TODO: array de ids.
                'reporttype' => new external_value(PARAM_INT, 'certificate model type'),
                'lang' => new external_value(PARAM_RAW, 'certificate model type'),
            ]
        );
    }
    public static function get_pdf_teacher_certificate(int $userid, string $courses, int $type, string $lang): array {
        /**
         * Devuelve el PDF del certificado de que el profesor ha impartido docencia en el curso
         * indicado con el detalle del uso que ha realizado de la herramienta que aparecerá en el
         * certificado. Este servicio web llamará a getJsonTeaching para obtener la información a
         * maquetar
         */
        $list = [];
        $list[] = [
            'courseid' => 1,
            'files' => [
                [
                    'file' => 'asdasd',
                    'reporttype' => certifygen_model::TYPE_TEACHER_ALL_COURSES_USED
                ]
            ],
        ];
        return ['certificates' => $list];
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_pdf_teacher_certificate_returns(): external_single_structure {
        return new external_single_structure([
                'certificates' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'courseid'   => new external_value(PARAM_INT, 'Course id'),
                            'files'   => new external_multiple_structure(
                                new external_single_structure(
                                    [
                                        'file' => new external_value(PARAM_RAW, 'certificate'),
                                        'reporttype' => new external_value(PARAM_INT, 'model type'),
                                    ]
                                ),
                            )
                        ], 'Certificates list by course')
                ),
        ]);
    }
}