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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos..

/**
 * WS Get courses names
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\external;
use \core_external\external_api;
use \core_external\external_function_parameters;
use external_multiple_structure;
use \core_external\external_single_structure;
use \core_external\external_value;
use invalid_parameter_exception;
use moodle_exception;
use core\url;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
/**
 * Get courses names
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class getcoursesnames_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function getcoursesnames_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'coursesids' => new external_value(PARAM_RAW, 'courses ids list'),
            ]
        );
    }

    /**
     * Get courses names
     * @param string $coursesids
     * @return array[]
     * @throws invalid_parameter_exception
     */
    public static function getcoursesnames(string $coursesids): array {
        self::validate_parameters(
            self::getcoursesnames_parameters(),
            ['coursesids' => $coursesids]
        );
        $list = [];
        $coursesarray = explode(',', $coursesids);
        foreach ($coursesarray as $courseid) {
            try {
                $course = get_course($courseid);
                $url = new url('/course/view.php', ['id' => $courseid]);
                $list[] = [
                    'id' => $courseid,
                    'shortname' => $course->shortname,
                    'fullname' => $course->fullname,
                    'link' => $url->out(),
                ];
            } catch (moodle_exception $e) {
                $list[] = [
                        'id' => $courseid,
                        'shortname' => '-',
                        'fullname' => get_string('coursenotexists', 'mod_certifygen'),
                        'link' => '-',
                ];
                continue;
            }
        }

        return ['list' => $list];
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function getcoursesnames_returns(): external_single_structure {
        return new external_single_structure([
                'list' => new external_multiple_structure(new external_single_structure(
                    [
                            'id' => new external_value(PARAM_INT, 'Course id'),
                            'shortname' => new external_value(PARAM_RAW, 'Course shortname'),
                            'fullname' => new external_value(PARAM_RAW, 'Course fullname'),
                            'link' => new external_value(PARAM_RAW, 'Course link url'),
                    ],
                    'Course list'
                )),
            ]);
    }
}
