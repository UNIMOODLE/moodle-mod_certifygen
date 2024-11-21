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

/**
 * Search category
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen\external;

use coding_exception;
use context_system;
use core_course_category;
use dml_exception;
use invalid_parameter_exception;
use moodle_exception;
use restricted_context_exception;

/**
 * Search category
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class searchcategory_external extends \core_external\external_api {
    /**
     * Describes the external function parameters.
     *
     * @return \core_external\external_function_parameters
     */
    public static function searchcategory_parameters(): \core_external\external_function_parameters {
        return new \core_external\external_function_parameters([
            'query' => new \core_external\external_value(PARAM_RAW, 'The search query', VALUE_REQUIRED),
        ]);
    }

    /**
     * Search category
     * @param string $query
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception|moodle_exception
     */
    public static function searchcategory(string $query): array {
        global $DB, $CFG;

        $params = \core_external\external_api::validate_parameters(self::searchcategory_parameters(), [
            'query' => $query,
        ]);
        $query = clean_param($params['query'], PARAM_TEXT);
        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('mod/certifygen:manage', $context);

        $likename = $DB->sql_like('name', ':name', false, true);
        $sql = "SELECT id, name
                  FROM {course_categories} c
                 WHERE $likename";
        $rs = $DB->get_recordset_sql($sql, ['name' => '%' . $query . '%']);
        $count = 0;
        $list = [];

        foreach ($rs as $record) {
            $category1 = core_course_category::get($record->id);
            $parentids = $category1->get_parents();
            $name = '';
            foreach ($parentids as $parentid) {
                $parent = core_course_category::get($parentid);
                $name .= ' / ' . $parent->get_formatted_name();
            }
            $name .= ' / ' . $category1->get_formatted_name();
            $category = (object)[
                'id' => $record->id,
                'name' => strip_tags(format_text($name)),
            ];

            $count++;

            if ($count <= $CFG->maxusersperpage) {
                $list[$record->id] = $category;
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
     * @return  \core_external\external_description
     */
    public static function searchcategory_returns():  \core_external\external_description {

        return new \core_external\external_single_structure([
            'list' => new \core_external\external_multiple_structure(
                new \core_external\external_single_structure([
                    'id' => new \core_external\external_value(PARAM_INT, 'Category ID'),
                    'name' => new \core_external\external_value(PARAM_RAW, 'Category name'),
                ])
            ),
            'maxusersperpage' => new \core_external\external_value(PARAM_INT, 'Configured maximum categories per page.'),
            'overflow' => new \core_external\external_value(PARAM_BOOL, 'Were there more records than maxusersperpage found?'),
        ]);
    }
}
