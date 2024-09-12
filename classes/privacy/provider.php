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
 * Implementation of the privacy subsystem plugin provider for the certifygen activity module.
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen\privacy;
use coding_exception;
use context_course;
use context_module;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use dml_exception;
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/modinfolib.php');
/**
 * Implementation of the privacy subsystem plugin provider for the certifygen activity module.
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * get_metadata
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $validations = [
            'name' => 'privacy:metadata:name',
            'courses' => 'privacy:metadata:courses',
            'code' => 'privacy:metadata:code',
            'certifygenid' => 'privacy:metadata:certifygenid',
            'issueid' => 'privacy:metadata:issueid',
            'userid' => 'privacy:metadata:userid',
            'modelid' => 'privacy:metadata:modelid',
            'lang' => 'privacy:metadata:lang',
            'status' => 'privacy:metadata:status',
            'usermodified' => 'privacy:metadata:usermodified',
            'timecreated' => 'privacy:metadata:timecreated',
            'timemodified' => 'privacy:metadata:timemodified',
        ];
        $collection->add_database_table('certifygen_validations', $validations,
            'privacy:metadata:certifygen_validations');
        return $collection;
    }

    /**
     * get_contexts_for_userid
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        // This plugin involves two main contexts, system for teachers and module for students.
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id";
        $sql .= " FROM {course_modules} cm";
        $sql .= " JOIN {modules} m ON cm.module = m.id AND m.name = :modulename";
        $sql .= " JOIN {certifygen} a ON cm.instance = a.id";
        $sql .= " JOIN {certifygen_validations} cv ON cv.certifygenid = a.id";
        $sql .= " JOIN {context} ctx ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel";
        $sql .= " WHERE cv.userid = :userid";

        $params = [
            'modulename' => 'certifygen',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);

        $sql = "SELECT c.id";
        $sql .= " FROM {context} c ";
        $sql .= " WHERE c.contextlevel = :contextsystem";
        $contextlist->add_from_sql($sql, ['contextsystem' => CONTEXT_SYSTEM]);
        return $contextlist;
    }

    /**
     * export_user_data
     * @param approved_contextlist $contextlist
     * @return mixed
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        $user = $contextlist->get_user();
        $validations = certifygen_validations::get_records(['userid' => $user->id]);
        foreach ($validations as $validation) {
            $context = context_system::instance();
            $code = certifygen_validations::get_certificate_code($validation);
            if (!empty($validation->get('certifygenid'))) {
                [$course, $cm] = get_course_and_cm_from_instance((int)$validation->get('certifygenid'),
                    'certifygen');
                $context = context_module::instance($cm->id);
            }
            $certifygenmodel = new certifygen_model($validation->get('modelid'));
            $repositoryplugin = $certifygenmodel->get('repository');
            $repositorypluginclass = $repositoryplugin . '\\' . $repositoryplugin;
            $url = '';
            if ($validation->get('status') == certifygen_validations::STATUS_FINISHED) {
                $subplugin = new $repositorypluginclass();
                $url = $subplugin->get_file_url($validation);
            }
            $data = [
                'code' => $code,
                'status' => get_string('status_'.$validation->get('status'), 'mod_certifygen'),
                'url' => $url,
                'timecreated' => $validation->get('timecreated'),
            ];
            if (!empty($validation->get('name'))) {
                $data['name'] = $validation->get('name');
            }
            $alldata[] = $data;
            writer::with_context($context)->export_data(
                [get_string('pluginname', 'mod_certifygen')], (object) $alldata);
        }
    }

    /**
     * delete_data_for_all_users_in_context
     * @param \context $context
     * @return mixed
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        $fs = get_file_storage();
        if ($context instanceof context_system) {
            // Delete issue files.
            $fs->delete_area_files($context->id, 'mod_certifygen', 'certifygenrepository');

            // Delete issue records.
            $DB->delete_records('certifygen_validations', ['certifygenid' => 0]);
        }
        if ($context instanceof context_module) {
            $validations = certifygen_validations::get_records(['courses' => '']);
            foreach ($validations as $validation) {
                self::remove_validation_data($validation);
            }
        }
    }

    /**
     * delete_data_for_user
     * @param approved_contextlist $contextlist
     * @return mixed
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        $validations = certifygen_validations::get_records(['userid' => $userid]);
        $fs = get_file_storage();
        foreach ($validations as $validation) {
            self::remove_validation_data($validation);
        }
    }

    /**
     * get_users_in_context
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context instanceof context_module) {
            // Students.
            $params = [
                'instanceid'    => $context->instanceid,
                'modulename'    => 'certifygen',
            ];

            // Certificates issues.
            $sql = "SELECT cv.userid";
            $sql .= " FROM {course_modules} cm";
            $sql .= " JOIN {modules} m ON m.id = cm.module AND m.name = :modulename";
            $sql .= " JOIN {certifygen} a ON a.id = cm.instance";
            $sql .= " JOIN {certifygen_validations} cv ON cv.certifygenid = a.id";
            $sql .= " WHERE cm.id = :instanceid";
            $userlist->add_from_sql('userid', $sql, $params);
        } else if ($context instanceof context_system) {
            // Teachers.
            $params = [
                'certifygenid'    => 0,
            ];

            $sql = "SELECT cv.userid";
            $sql .= " FROM {certifygen_validations} cv";
            $sql .= " WHERE cv.certifygenid = :certifygenid";
            $userlist->add_from_sql('userid', $sql, $params);
        }
    }

    /**
     * delete_data_for_users
     * @param approved_userlist $userlist
     * @return mixed
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof context_system
        && !$context instanceof context_module) {
            return;
        }
        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);

        $validations = $DB->get_records_select('certifygen_validations', ' userid ' . $userinsql,
            $userinparams);
        foreach ($validations as $validation) {
            self::remove_validation_data($validation);
        }
    }

    /**
     * remove_validation_data
     * @param certifygen_validations $validation
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    private static function remove_validation_data(certifygen_validations $validation): void {
        $fs = get_file_storage();
        // Delete issue files.
        $context = context_system::instance();
        if (empty($validation->get('certifygenid'))) {
            [$course, $cm] = get_course_and_cm_from_instance($validation->get('certifygenid'), 'certifygen');
            $context = context_course::instance($course->id);
        }
        $filearea = '';
        $status = $validation->get('status');
        switch ($status){
            case certifygen_validations::STATUS_VALIDATION_OK:
            case certifygen_validations::STATUS_STORAGE_ERROR:
                $filearea = ICertificateValidation::FILE_AREA_VALIDATED;
            break;
            case certifygen_validations::STATUS_FINISHED:
                $filearea = ICertificateRepository::FILE_AREA;
                break;
            case certifygen_validations::STATUS_NOT_STARTED:
                $filearea = '';
                break;
        }
        try {
            if (!empty($filearea)) {
                $fs->delete_area_files($context->id, 'mod_certifygen', $filearea, $validation->get('id'));
            }
            if (empty($validation->get('certifygenid'))) {
                $fs->delete_area_files($context->id, 'mod_certifygen', 'certifygenreport',
                    $validation->get('id'));
            } else {
                $fs->delete_area_files(context_system::instance()->id, 'mod_certifygen', 'issues',
                    $validation->get('id'));

            }
        } catch (moodle_exception $e) {
            // por si no existe el fichero...
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
        }

        // Delete issue records.
        $validation->delete();
    }
}
