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
 * Implementation of the privacy subsystem plugin provider for the certifygen certifygenrepository_onedrive subplugin.
 * @package    certifygenrepository_onedrive
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenrepository_onedrive\privacy;

use coding_exception;
use context;
use context_module;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use dml_exception;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;

/**
 * Implementation of the privacy subsystem plugin provider for the certifygen certifygenrepository_onedrive subplugin.
 *
 * @package    certifygenrepository_onedrive
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    core_userlist_provider,
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Get reason
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        // This plugin involves two main contexts, system for teachers and module for students.
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {course_modules} cm
                  JOIN {modules} m ON cm.module = m.id AND m.name = :modulename
                  JOIN {certifygen} a ON cm.instance = a.id
                  JOIN {certifygen_validations} cv ON cv.certifygenid = a.id
                  JOIN {certifygen_repository} cr ON cr.validationid = cv.id
                  JOIN {context} ctx ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                 WHERE cv.userid = :userid";

        $params = [
                'modulename' => 'certifygen',
                'contextlevel' => CONTEXT_MODULE,
                'userid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);

        $sql = "SELECT c.id
                  FROM {context} c
                 WHERE c.contextlevel = :contextsystem";
        $contextlist->add_from_sql($sql, ['contextsystem' => CONTEXT_SYSTEM]);
        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     * @throws moodle_exception
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        $user = $contextlist->get_user();
        global $DB;
        $userlinks = $DB->get_records('certifygen_repository', ['userid' => $user->id]);
        foreach ($userlinks as $userlink) {
            $context = context_system::instance();
            $validation = new certifygen_validations($userlinks->validationid);
            if (!empty($validation->get('certifygenid'))) {
                [$course, $cm] = get_course_and_cm_from_instance(
                    (int)$validation->get('certifygenid'),
                    'certifygen'
                );
                $context = context_module::instance($cm->id);
            }
            $data = [
                    'url' => $userlink->url,
            ];
            $alldata[] = $data;
            writer::with_context($context)->export_data(
                [get_string('pluginname', 'certifygenrepository_onedrive')],
                (object) $alldata
            );
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context $context The specific context to delete data for.
     * @throws dml_exception
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;
        $DB->delete_records('certifygen_repository');
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     * @throws dml_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        $DB->delete_records_list('certifygen_repository', 'userid', $userid);
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;
        [$userinsql, $userinparams] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);

        $sql = "SELECT cr.userid
                  FROM {certifygen_repository} cr
                 WHERE userid" . $userinsql;
        $userlist->add_from_sql('userid', $sql, $userinparams);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     * @throws dml_exception
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $DB->delete_records_list('certifygen_repository', 'userid', $userlist->get_userids());
    }

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $validations = [
                'validationid' => 'privacy:metadata:validationid',
                'userid' => 'privacy:metadata:userid',
                'url' => 'privacy:metadata:url',
                'usermodified' => 'privacy:metadata:usermodified',
                'timecreated' => 'privacy:metadata:timecreated',
                'timemodified' => 'privacy:metadata:timemodified',
        ];
        $collection->add_database_table(
            'certifygen_validations',
            $validations,
            'privacy:metadata:certifygen_validations'
        );
        return $collection;
    }
}
