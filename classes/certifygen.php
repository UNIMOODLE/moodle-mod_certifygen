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
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen;

use coding_exception;
use context_course;
use core\lock\lock_config;
use core_course\customfield\course_handler;
use dml_exception;
use moodle_exception;
use stdClass;
use stored_file;
use tool_certificate\certificate;
use tool_certificate\permission;

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/gradelib.php');

class certifygen {
//    /**
//     * Gets users who meet access restrictionss and had not been issued.
//     *
//     * @param stdClass $coursecertificate
//     * @param \cm_info $cm
//     * @return array
//     */
//    public static function get_users_to_issue(stdClass $coursecertificate, \cm_info $cm): array {
//        global $DB;
//        return [];
//        $context = \context_course::instance($coursecertificate->course);
//        // Get users already issued subquery.
//        [$usersissuedsql, $usersissuedparams] = self::get_users_issued_select($coursecertificate->course,
//            $coursecertificate->template);
//        // Get users enrolled with receive capabilities subquery.
//        [$enrolledsql, $enrolledparams] = get_enrolled_sql($context, 'mod/coursecertificate:receive', 0, true);
//        $sql  = "SELECT eu.id FROM ($enrolledsql) eu WHERE eu.id NOT IN ($usersissuedsql)";
//        $params = array_merge($enrolledparams, $usersissuedparams);
//        $potentialusers = $DB->get_records_sql($sql, $params);
//
//        // Filter only users with access to the activity {@see info_module::filter_user_list}.
//        $info = new info_module($cm);
//        $filteredusers = $info->filter_user_list($potentialusers);
//
//        // Filter only users without 'viewall' capabilities and with access to the activity.
//        $users = [];
//        foreach ($filteredusers as $filtereduser) {
//            $modinfouser = get_fast_modinfo($cm->get_course(), $filtereduser->id);
//            $cmuser = $modinfouser->get_cms()[$cm->id] ?? null;
//            // Property 'cm_info::uservisible' checks if user has access to the activity - it is visible, in the
//            // correct group, user has capability to view it, is available. However, for teachers it
//            // can return true even if they do not satisfy availability criteria,
//            // therefore we need to additionally check property 'cm_info::available'.
//            if ($cmuser && $cmuser->uservisible && $cmuser->available) {
//                $users[] = $filtereduser;
//            }
//        }
//        return $users;
//    }

    /**
     * Returns the record for the certificate user has in a given course
     *
     * In rare situations (race conditions) there can be more than one certificate, in which case return the last record.
     *
     * @param int $userid
     * @param int $courseid
     * @param int $templateid
     * @param string $lang
     * @return stdClass|null
     * @throws dml_exception
     */
    public static function get_user_certificate(int $userid, int $courseid, int $templateid, string $lang): ?stdClass {

        global $DB;
        $likecode = $DB->sql_like('ci.code', ':code');
        $sql = "SELECT * FROM {tool_certificate_issues} ci
                WHERE component = :component AND courseid = :courseid AND templateid = :templateid AND userid = :userid
                      AND archived = 0 AND $likecode
                ORDER BY id DESC";
        $params = [
            'component' => 'mod_certifygen',
            'courseid' => $courseid,
            'templateid' => $templateid,
            'userid' => $userid,
            'code' => '%_' . $lang,
        ];
        $records = $DB->get_records_sql($sql, $params);

        return $records ? reset($records) : null;
    }

    /**
     * Issue a course certificate to the user if they don't already have one
     * @param stdClass $user
     * @param int $templateid
     * @param stdClass $course
     * @param string $lang
     * @return int
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function issue_certificate(stdClass $user, int $templateid, stdClass $course, string $lang): int {

//        $lockfactory = lock_config::get_lock_factory('mod_certifygen');

//        $lock = $lockfactory->get_lock("i_{$user->id}_{$templateid}_{$course->id}_{$lang}", MINSECS);

//        if (!$lock) {
//            error_log(__FUNCTION__ . ' lock timeout ' . __LINE__);
//            throw new moodle_exception('locktimeout');
//        }

//        if (self::get_user_certificate($user->id, $course->id, $templateid, $lang)) {
//            error_log(__FUNCTION__ . ' loc released ' . __LINE__);
//            // If user already has a certificate - do not issue a new one.
//            $lock->release();
//            return 0;
//        }

        try {
            $template = template::instance($templateid, (object) ['lang' => $lang]);

            $issuedata = self::get_issue_data($course, $user);

            $expirydatetype = $expirydateoffset = 0;

            $expirydate = certificate::calculate_expirydate(
                $expirydatetype,
                $expirydateoffset,
                $expirydateoffset
            );
            return $template->issue_certificate($user->id, $expirydate, $issuedata, 'mod_certifygen', $course->id);
        } catch(moodle_exception $e) {
            error_log(__FUNCTION__ . ' ' . __LINE__. ' ERROR: '. var_export($e->getMessage(), true));
        }
        return 0;
    }

    /**
     * Returns select for the users that have been already issued
     *
     * @param int $courseid
     * @param int $templateid
     * @return array
     */
    private static function get_users_issued_select(int $courseid, int $templateid): array {
        $sql = "SELECT DISTINCT ci.userid FROM {tool_certificate_issues} ci
                WHERE component = :component AND courseid = :courseid AND templateid = :templateid
                      AND archived = 0";
        $params = ['component' => 'mod_certifygen', 'courseid' => $courseid,
            'templateid' => $templateid, ];
        return [$sql, $params];
    }

    /**
     * Get data for the issue. Important course fields (id, shortname, fullname and URL) and course customfields.
     * @param stdClass $course
     * @param stdClass $user
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_issue_data(stdClass $course, stdClass $user): array {
        global $DB;

        // Get user course completion date.
        $result = $DB->get_field('course_completions', 'timecompleted',
            ['course' => $course->id, 'userid' => $user->id]);
        $completiondate = $result ? userdate($result, get_string('strftimedatefullshort')) : '';

        // Get user course grade.
        $grade = grade_get_course_grade($user->id, $course->id);
        if ($grade && $grade->grade) {
            $gradestr = $grade->str_grade;
        }

        $issuedata = [
            'courseid' => $course->id,
            'courseshortname' => $course->shortname,
            'coursefullname' => $course->fullname,
            'courseurl' => course_get_url($course)->out(),
            'coursecompletiondate' => $completiondate,
            'coursegrade' => $gradestr ?? '',
        ];
        // Add course custom fields data.
        $handler = course_handler::create();
        foreach ($handler->get_instance_data($course->id, true) as $data) {
            $issuedata['coursecustomfield_' . $data->get_field()->get('shortname')] = $data->export_value();
        }

        return $issuedata;
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_user_certificate_file_url(string $templateid, int $userid, int $courseid, string $lang) : string {
        $users = user_get_users_by_id([$userid]);
        $user = reset($users);
        $course = get_course($courseid);
        certifygen::issue_certificate($user, $templateid, $course, $lang);
        $url = "";
        if ($existingcertificate = self::get_user_certificate($userid, $course->id, $templateid, $lang)) {

            $issue = template::get_issue_from_code($existingcertificate->code);
            $context = context_course::instance($issue->courseid, IGNORE_MISSING) ?: null;

            $template = $issue ? template::instance($issue->templateid, (object) ['lang' => $lang]) : null;
            if ($template && (permission::can_verify() ||
                    permission::can_view_issue($template, $issue, $context))) {
                $url = $template->get_issue_file_url($issue);
            } else {
                throw new moodle_exception('certificatenotfound', 'mod_certifygen');
            }
        }
        return $url;
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_user_certificate_file(string $templateid, int $userid, int $courseid, string $lang) {
        $users = user_get_users_by_id([$userid]);
        $user = reset($users);
        $course = get_course($courseid);
        certifygen::issue_certificate($user, $templateid, $course, $lang);
        if ($existingcertificate = self::get_user_certificate($userid, $course->id, $templateid, $lang)) {

            $issue = template::get_issue_from_code($existingcertificate->code);
            $context = context_course::instance($issue->courseid, IGNORE_MISSING) ?: null;

            $template = $issue ? template::instance($issue->templateid, (object) ['lang' => $lang]) : null;
            if ($template && (permission::can_verify() ||
                    permission::can_view_issue($template, $issue, $context))) {
                return $template->get_issue_file($issue);
            } else {
                throw new moodle_exception('certificatenotfound', 'mod_certifygen');
            }
        }
        return null;
    }
}
