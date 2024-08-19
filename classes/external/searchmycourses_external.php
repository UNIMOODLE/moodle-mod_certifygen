<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.
namespace mod_certifygen\external;

use coding_exception;
use context_system;
use dml_exception;
use external_api;
use external_description;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use restricted_context_exception;

/**
 * @package    mod_certifygen
 * * @copyright  2024 Proyecto UNIMOODLE
 * * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * * @author     3IPUNT <contacte@tresipunt.com>
 * * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class searchmycourses_external extends external_api {

    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function searchmycourses_parameters(): external_function_parameters {
        return new external_function_parameters([
            'query' => new external_value(PARAM_RAW, 'The search query', VALUE_REQUIRED),
            'userid' => new external_value(PARAM_INT, 'The search query', VALUE_REQUIRED),
        ]);
    }

    /**
     * @param string $query
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function searchmycourses(string $query, int $userid): array {
        global $DB, $CFG;

        $params = external_api::validate_parameters(self::searchmycourses_parameters(), [
            'query' => $query,
            'userid' => $userid,
        ]);
        $query = clean_param($params['query'], PARAM_TEXT);
        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);

        $likename = $DB->sql_like('c.fullname', ':fullname', false, true);
        $sql = "SELECT c.id, c.fullname
                    FROM  {user_enrolments} ue 
                    INNER JOIN {enrol} e ON e.id = ue.enrolid
                    INNER JOIN {course} c ON c.id = e.courseid
                    WHERE ue.userid = :userid 
                    AND $likename";

        $params = [
            'fullname' => '%' . $query . '%',
            'userid' => $userid
        ];

        $rs = $DB->get_recordset_sql($sql, $params);
        $count = 0;
        $list = [];
        foreach ($rs as $record) {
            $course = (object)[
                'id' => $record->id,
                'name' => strip_tags(format_text($record->fullname)),
            ];
            $count++;

            if ($count <= $CFG->maxusersperpage) {
                $list[$record->id] = $course;
            }
        }

        $rs->close();

        return [
            'list' => $list,
            'maxusersperpage' => $CFG->maxusersperpage,
            'overflow' => ($count > $CFG->maxusersperpage),
        ];
    }

    /**
     * Describes the external function result value.
     *
     * @return external_description
     */
    public static function searchmycourses_returns(): external_description {

        return new external_single_structure([
            'list' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Course ID'),
                    'name' => new external_value(PARAM_RAW, 'Course name'),
                ])
            ),
            'maxusersperpage' => new external_value(PARAM_INT, 'Configured maximum categories per page.'),
            'overflow' => new external_value(PARAM_BOOL, 'Were there more records than maxusersperpage found?'),
        ]);
    }
}
