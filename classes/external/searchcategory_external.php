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

namespace mod_certifygen\external;

use external_api;
use external_description;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

/**
 * Provides the core_user_search_identity external function.
 *
 * @package     core_user
 * @category    external
 * @copyright   2021 David Mudrák <david@moodle.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class searchcategory_external extends external_api {

    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function searchcategory_parameters(): external_function_parameters {
        return new external_function_parameters([
            'query' => new external_value(PARAM_RAW, 'The search query', VALUE_REQUIRED),
        ]);
    }

    /**
     * Finds users with the identity matching the given query.
     *
     * @param string $query The search request.
     * @return array
     */
    public static function searchcategory(string $query): array {
        global $DB, $CFG;
error_log(__FUNCTION__ . ' query: '.var_export($query, true));
        $params = external_api::validate_parameters(self::searchcategory_parameters(), [
            'query' => $query,
        ]);
        $query = clean_param($params['query'], PARAM_TEXT);
        error_log(__FUNCTION__ . ' 2 query: '.var_export($query, true));
        // Validate context.
        $context = \context_system::instance();
        self::validate_context($context);
//        require_capability('moodle/user:viewalldetails', $context);

//        $hasviewfullnames = has_capability('moodle/site:viewfullnames', $context);
//
//        $fields = \core_user\fields::for_name()->with_identity($context, false);
//        $extrafields = $fields->get_required_fields([\core_user\fields::PURPOSE_IDENTITY]);
//
//        list($searchsql, $searchparams) = users_search_sql($query, '', true, $extrafields);
//        list($sortsql, $sortparams) = users_order_by_sql('', $query, $context);
//        $params = array_merge($searchparams, $sortparams);
//
//        $rs = $DB->get_recordset_select('course_categories', $searchsql, $params, $sortsql,
//            'id' . $fields->get_sql()->selects, 0, $CFG->maxusersperpage + 1);

        $likename = $DB->sql_like('name', ':name');
        $sql = "SELECT *
              FROM {course_categories} c
             WHERE $likename";
        error_log(__FUNCTION__ . ' sql: '.var_export($sql, true));
        $rs = $DB->get_recordset_sql($sql, ['name' => $query]);
        $count = 0;
        $list = [];

        foreach ($rs as $record) {
            $category = (object)[
                'id' => $record->id,
                'name' => $record->name,
            ];

            $count++;

            if ($count <= $CFG->maxusersperpage) {
                $list[$record->id] = $category;
            }
        }

        $rs->close();
        error_log(__FUNCTION__ . ' list: '.var_export($list, true));
        error_log(__FUNCTION__ . ' count: '.var_export($count, true));
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
    public static function searchcategory_returns(): external_description {

        return new external_single_structure([
            'list' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Category ID'),
                    'name' => new external_value(PARAM_RAW, 'Category name'),
                ])
            ),
            'maxusersperpage' => new external_value(PARAM_INT, 'Configured maximum categories per page.'),
            'overflow' => new external_value(PARAM_BOOL, 'Were there more records than maxusersperpage found?'),
        ]);
    }
}
