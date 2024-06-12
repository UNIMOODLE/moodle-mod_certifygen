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
use external_single_structure;
use external_multiple_structure;
use external_value;

class get_courses_as_teacher_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_courses_as_teacher_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
            ]
        );
    }

    /**
     * @param int $userid
     * @return array
     */
    public static function get_courses_as_teacher(int $userid): array {
        /**
         * OLD
         * Devuelve un json con la información necesaria para el anterior servicio para
         * confeccionar el certificado. El objetivo de este servicio es independizar el proceso de
         * obtención de los datos del proceso de generación del documento con la presentación
         * final.
         */
        /**
         * NEW:
         * Devuelve un json con la lista de cursos en los cuales figura como profesor la persona indicada
         * por su identificador (userid).
         * Este servicio permitirá a un sistema externo mostrar los cursos certificables.
         * El servicio devolverá como mínimo los siguientes atributos de cada curso y se valorará que se ofrezca un
         * servicio para configurar otros atributos de los disponibles para el profesor y los cursos en moodle:
         * a. course.shortname
         * b. course.fullname
         * c. course.categoryid.
         * d. reportype asociado al curso: [model type]
 */
        return [
            'courses' => [
                [
                    'id'   => 1,
                    'shortname'   => 'Course test',
                    'fullname'   => 'Course test',
                    'categoryid'   => 1,
                    'completed'   => false,
                    'modellist'   => '1,2',
                    'reporttypes'   => [
                        [
                            'type' => 1,
                            'modelid' => 1,
                        ]
                    ],
                ]
            ],
            'teacher' => [
                'fullname' => 'Nombre fake',
                'id' => 1
            ]
        ];
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_courses_as_teacher_returns(): external_single_structure {
        return new external_single_structure(array(
                'courses' => new external_multiple_structure( new external_single_structure(
                        [
                            'id'   => new external_value(PARAM_RAW, 'Course id'),
                            'shortname'   => new external_value(PARAM_RAW, 'Course shortname'),
                            'fullname' => new external_value(PARAM_RAW, 'Course fullname'),
                            'categoryid' => new external_value(PARAM_INT, 'Course category id'),
                            'completed' => new external_value(PARAM_BOOL, 'student has course completed '),
                            'modellist' => new external_value(PARAM_RAW, 'model id list separated by commas.'),
                            'reporttypes' => new external_multiple_structure(
                                new external_single_structure(
                                    [
                                        'type'   => new external_value(PARAM_INT, 'model type'),
                                        'modelid'   => new external_value(PARAM_INT, 'model id'),
                                    ], 'courses list')
                                ),
                        ], 'courses list')
                ),
                'teacher' => new external_single_structure (
                    [
                        'fullname' => new external_value(PARAM_RAW, 'User fullname'),
                        'id' => new external_value(PARAM_INT, 'User id'),
                    ], 'Student info')
            )
        );
    }
}