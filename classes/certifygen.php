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
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen;

use coding_exception;
use context_course;
use core\invalid_persistent_exception;
use core_course\customfield\course_handler;
use dml_exception;
use file_exception;
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_error;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use stdClass;
use stored_file_creation_exception;
use tool_certificate\certificate;
use tool_certificate\permission;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/gradelib.php');

/**
 * Certifygen
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygen {

    /**
     * Returns the record for the certificate user has in a given course
     *
     * In rare situations (race conditions) there can be more than one certificate, in which case return the last record.
     *
     * @param int $instaceid
     * @param int $userid
     * @param int $courseid
     * @param int $templateid
     * @param string $lang
     * @return stdClass|null
     * @throws dml_exception
     */
    public static function get_user_certificate(int $instaceid, int $userid, int $courseid, int $templateid,
                                                string $lang): ?stdClass {

        global $DB;

        $comparelang = $DB->sql_compare_text('cv.lang');
        $comparelangplaceholder = $DB->sql_compare_text(':lang');
        $comparecomp = $DB->sql_compare_text('ci.component');
        $comparecompplaceholder = $DB->sql_compare_text(':component');
        $sql = "SELECT ci.*";
        $sql .= " FROM {tool_certificate_issues} ci";
        $sql .= " INNER JOIN {certifygen_validations} cv ON (cv.issueid = ci.id AND cv.userid = ci.userid)";
        $sql .= " WHERE {$comparecomp} = {$comparecompplaceholder}";
        $sql .= " AND ci.courseid = :courseid";
        $sql .= " AND ci.templateid = :templateid";
        $sql .= " AND ci.userid = :userid";
        $sql .= " AND ci.archived = 0";
        $sql .= " AND cv.certifygenid = :instanceid";
        $sql .= " AND {$comparelang} = {$comparelangplaceholder}";
        $sql .= " ORDER BY ci.id DESC";
        $params = [
            'component' => 'mod_certifygen',
            'instanceid' => $instaceid,
            'courseid' => $courseid,
            'templateid' => $templateid,
            'userid' => $userid,
            'lang' => $lang,
        ];
        $records = $DB->get_records_sql($sql, $params);

        return $records ? reset($records) : null;
    }

    /**
     * Issue a course certificate to the user if they don't already have one
     * @param int $instanceid
     * @param stdClass $user
     * @param int $templateid
     * @param stdClass $course
     * @param string $lang
     * @return int
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function issue_certificate(int $instanceid, stdClass $user, int $templateid, stdClass $course,
                                             string $lang): int {
        if (self::get_user_certificate($instanceid, $user->id, $course->id, $templateid, $lang)) {
            return 0;
        }
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
        } catch (moodle_exception $e) {
            debugging(__FUNCTION__ . ' ' . __LINE__. ' ERROR: '. $e->getMessage());
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
        $sql = "SELECT DISTINCT ci.userid FROM {tool_certificate_issues} ci";
        $sql .= " WHERE component = :component AND courseid = :courseid AND templateid = :templateid";
        $sql .= " AND archived = 0";
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
     * Get user certificate file url
     * @param int $instanceid
     * @param string $templateid
     * @param int $userid
     * @param int $courseid
     * @param string $lang
     * @return string
     * @throws file_exception
     * @throws stored_file_creation_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_user_certificate_file_url(int $instanceid, string $templateid, int $userid,
                                                         int $courseid, string $lang): string {
        // Get user.
        $users = user_get_users_by_id([$userid]);
        $user = reset($users);
        $course = get_course($courseid);
        self::issue_certificate($instanceid, $user, $templateid, $course, $lang);
        $url = "";
        // Get user certificate.
        if ($existingcertificate = self::get_user_certificate($instanceid, $userid, $course->id, $templateid, $lang)) {
            $issue = template::get_issue_from_code($existingcertificate->code);
            $context = context_course::instance($issue->courseid, IGNORE_MISSING) ?: null;
            $template = $issue ? template::instance($issue->templateid, (object) ['lang' => $lang]) : null;
            if ($template && (permission::can_verify() ||
                    permission::can_view_issue($template, $issue, $context))) {
                $url = $template->get_issue_file_url($issue);
                $url = $url->out();
            } else {
                throw new moodle_exception('certificatenotfound', 'mod_certifygen');
            }
        }
        return $url;
    }

    /**
     * get_user_certificate_file
     * @param int $instanceid
     * @param string $templateid
     * @param int $userid
     * @param int $courseid
     * @param string $lang
     * @return \stored_file|null
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws moodle_exception
     * @throws stored_file_creation_exception
     */
    public static function get_user_certificate_file(int $instanceid, string $templateid, int $userid, int $courseid,
                                                     string $lang) {
        $users = user_get_users_by_id([$userid]);
        $user = reset($users);
        $course = get_course($courseid);
        self::issue_certificate($instanceid, $user, $templateid, $course, $lang);
        if ($existingcertificate = self::get_user_certificate($instanceid, $userid, $course->id, $templateid, $lang)) {

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

    /**
     * Get groupmode subquery
     *
     * @param int $groupmode
     * @param int $groupid
     * @return array
     * @throws coding_exception
     */
    private static function get_groupmode_subquery(int $groupmode, int $groupid) {
        if (($groupmode != NOGROUPS) && $groupid) {
            [$sql, $params] = groups_get_members_ids_sql($groupid);
            $groupmodequery = "AND u.id IN ($sql)";
            return [$groupmodequery, $params];
        }
        return ['', []];
    }

    /**
     * get_issues_for_course_by_lang
     * @param string $lang
     * @param int $certifygenid
     * @param int $templateid
     * @param int $courseid
     * @param string $component
     * @param int $userid
     * @param string $tifirst
     * @param string $tilast
     * @param int $limitfrom
     * @param int $limitnum
     * @param string $sort
     * @return array
     * @throws dml_exception
     */
    public static function get_issues_for_course_by_lang(string $lang, int $certifygenid, int $templateid, int $courseid,
                                                         string $component, int $userid, string $tifirst,
                                                         string $tilast, int $limitfrom, int $limitnum,
                                                         string $sort = ''): array {
        global $DB;

        if (empty($sort)) {
            $sort = 'ci.timecreated DESC';
        }

        $params = [
            'now' => time(),
            'templateid' => $templateid,
            'courseid' => $courseid,
            'component' => $component,
            'lang' => $lang,
            'certifygenid' => $certifygenid,
            ];
        $where = "";
        if (!empty($tifirst)) {
            $params['tifirst'] = $tifirst . '%';
            $where .= ' AND ' . $DB->sql_like('u.firstname', ':tifirst');

        }
        if (!empty($tilast)) {
            $params['tilast'] = $tilast . '%';
            $where .= ' AND ' . $DB->sql_like('u.lastname', ':tilast');
        }
        if ($userid) {
            $params['userid'] = $userid;
            $where = ' AND u.id = :userid';
        }

        $sql = "SELECT us.userid, ci.id as issueid, ci.code, ci.emailed, ci.timecreated as ctimecreated, ci.userid,";
        $sql .= " ci.templateid, ci.expires, ci.courseid, ci.archived, cv.lang as clang, cv.status as cstatus,";
        $sql .= " cv.id as validationid, us.*, us.courseid, ci.courseid, ci.archived, cv.lang as clang,";
        $sql .= " cv.status as cstatus, cv.id as validationid, us.*, us.courseid";
        $sql .= " FROM (SELECT u.id AS userid, u.*, c.id as courseid";
        $sql .= "         FROM {user} u";
        $sql .= "         INNER JOIN {user_enrolments} ue ON ue.userid = u.id";
        $sql .= "         INNER JOIN {enrol} e ON e.id = ue.enrolid";
        $sql .= "         INNER JOIN {course} c ON c.id = e.courseid";
        $sql .= "         INNER JOIN {context} cont ON (cont.instanceid = c.id AND cont.contextlevel = 50)";
        $sql .= "         INNER JOIN {role_assignments} ra ON ( ra.contextid = cont.id AND  ra.userid = u.id)";
        $sql .= "         INNER JOIN {role} r ON r.id = ra.roleid";
        $sql .= "         WHERE r.shortname = 'student' AND c.id = :courseid $where ) AS us";
        $sql .= " LEFT JOIN {certifygen_validations} cv ON (cv.userid = us.userid AND cv.lang = :lang  ";
        $sql .= "         AND cv.certifygenid = :certifygenid)";
        $sql .= " LEFT JOIN {tool_certificate_issues} ci ON (ci.userid = us.userid AND cv.issueid = ci.id ";
        $sql .= "         AND ci.courseid = us.courseid AND ci.templateid = :templateid AND ci.component = :component)";

        return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
    }

    /**
     * Get extra fields for select query of certificates.
     *
     * @param \context $context
     * @return string
     * @throws coding_exception
     */
    public static function get_extra_user_fields(\context $context): string {
        global $CFG;

        if ($CFG->version < 2021050700) {
            // Moodle 3.9-3.10.
            $extrafields = get_extra_user_fields($context);
            $userfields = \user_picture::fields('u', $extrafields);

        } else {
            // Moodle 3.11 and above.
            $extrafields = \core_user\fields::for_identity($context, false)->get_required_fields();
            $userfields = \core_user\fields::for_userpic()->including(...$extrafields)
                ->get_sql('u', false, '', '', false)->selects;
        }

        return str_replace(' ', '', $userfields);
    }
    /**
     * Helps to build SQL to retrieve users that can be displayed to the current user
     *
     * If tool_tenant is installed - adds a tenant filter
     *
     * @uses \tool_tenant\tenancy::get_users_subquery()
     *
     * @param string $usertablealias
     * @param bool $canseeall do not add tenant check if user has capability 'tool/tenant:manage'
     * @return string
     */
    public static function get_users_subquery(string $usertablealias = 'u', bool $canseeall = true): string {
        return component_class_callback('tool_tenant\\tenancy', 'get_users_subquery',
            [$canseeall, false, $usertablealias.'.id'], '1=1');
    }

    /**
     * count_issues_for_course_by_lang
     * @param int $courseid
     * @param string $tifirst
     * @param string $tilast
     * @param int $userid
     * @return int
     * @throws dml_exception
     */
    public static function count_issues_for_course_by_lang(int $courseid, string $tifirst, string $tilast, int $userid = 0) {
        global $DB;

        $where = '';
        $params = [
            'courseid' => $courseid,
        ];
        if (!empty($tifirst)) {
            $where .= " AND u.firstname LIKE '$tifirst%'";

        }
        if (!empty($tilast)) {
            $where .= " AND u.firstname LIKE '%$tilast'";
        }

        if ($userid) {
            $params['userid'] = $userid;
            $where = ' AND u.id = :userid';
        }

        $sql = "SELECT COUNT(u.id) as count";
        $sql .= " FROM {user} u";
        $sql .= " INNER JOIN {user_enrolments} ue ON ue.userid = u.id";
        $sql .= " INNER JOIN {enrol} e ON e.id = ue.enrolid";
        $sql .= " INNER JOIN {course} c ON c.id = e.courseid";
        $sql .= " INNER JOIN {context} cont ON (cont.instanceid = c.id AND cont.contextlevel = 50)";
        $sql .= " INNER JOIN {role_assignments} ra ON ( ra.contextid = cont.id AND  ra.userid = u.id)";
        $sql .= " INNER JOIN {role} r ON r.id = ra.roleid";
        $sql .= " WHERE r.shortname = 'student'";
        $sql .= " AND c.id = :courseid $where";
        return $DB->count_records_sql($sql, $params);
    }

    /**
     * start_emit_certificate_proccess
     * @param certifygen_validations $validation
     * @param certifygen_file $certifygenfile
     * @param certifygen_model $certifygenmodel
     * @return array
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function start_emit_certificate_proccess(certifygen_validations $validation,
                                                           certifygen_file $certifygenfile,
                                                           certifygen_model $certifygenmodel): array {
        global $USER;
        $result = ['result' => true, 'message' => get_string('ok', 'mod_certifygen')];

        // Step 4: Call to validation plugin.
        $validationplugin = $certifygenmodel->get('validation');
        $validationpluginclass = $validationplugin . '\\' . $validationplugin;
        if (get_config($validationplugin, 'enabled') === '1') {
            /** @var ICertificateValidation $subplugin */
            $subplugin = new $validationpluginclass();
            $response = $subplugin->send_file($certifygenfile);
            if ($response['haserror']) {
                if (!array_key_exists('message', $response)) {
                    $result['message'] = 'validation_plugin_send_file_error';
                }
                $validation->set('status', certifygen_validations::STATUS_VALIDATION_ERROR);
                $validation->save();
                $data = [
                    'validationid' => $validation->get('id'),
                    'status' => $validation->get('status'),
                    'code' => 'validation_plugin_send_file_error',
                    'message' => 'validation_plugin_send_file_error',
                    'usermodified' => $USER->id,
                ];
                certifygen_error::manage_certifygen_error(0, (object)$data);
            } else if (!$subplugin->check_status()) {
                $validation->set('status', certifygen_validations::STATUS_VALIDATION_OK);
                $validation->save();
            }
        } else {
            $result['result'] = false;
            $result['message'] = 'validationplugin_not_enabled';
            $validation->set('status', certifygen_validations::STATUS_VALIDATION_ERROR);
            $validation->save();
            $data = [
                'validationid' => $validation->get('id'),
                'status' => $validation->get('status'),
                'code' => 'validationplugin_not_enabled',
                'message' => 'validationplugin_not_enabled',
                'usermodified' => $USER->id,
            ];
            certifygen_error::manage_certifygen_error(0, (object)$data);
        }
        // Step 5: Call to repository plugin.
        if ($validation->get('status') === certifygen_validations::STATUS_VALIDATION_OK) {
            // Save on repository plugin.
            $repositoryplugin = $certifygenmodel->get('repository');
            $repositorypluginclass = $repositoryplugin . '\\' . $repositoryplugin;
            /** @var ICertificateRepository $subplugin */
            $subplugin = new $repositorypluginclass();
            $response = $subplugin->save_file($response['newfile']);
            if (!$response['haserror']) {
                $validation->set('status', certifygen_validations::STATUS_FINISHED);
                $validation->save();
            } else {
                $validation->set('status', certifygen_validations::STATUS_STORAGE_ERROR);
                $validation->save();
                $result['result'] = false;
                $result['message'] = $response['message'];
                $data = [
                    'validationid' => $validation->get('id'),
                    'status' => $validation->get('status'),
                    'code' => $response['message'],
                    'message' => $response['message'],
                    'usermodified' => $USER->id,
                ];
                certifygen_error::manage_certifygen_error(0, (object)$data);
            }
        }
        return $result;
    }

    /**
     * count_errors
     * @param string $userfullname
     * @param string $modelname
     * @return int
     * @throws dml_exception
     */
    public static function count_errors(string $userfullname = '', string $modelname = '') {
        global $DB;
        $params = [];
        $where = "";
        if (!empty($modelname)) {
            $params['modelname'] = '%' . $modelname . '%';
            $where .= $DB->sql_like('cm.name', ':modelname');
        }
        if (!empty($userfullname)) {
            $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
            $params['search'] = '%' . $userfullname . '%';
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $params['search'] = '%' . $DB->sql_like_escape($userfullname) . '%';
            $where .= $DB->sql_like($fullname, ':search', false);
        }
        $wheresql = '';
        if (!empty($where)) {
            $wheresql = ' WHERE ' . $where;
        }
        $sql = "SELECT COUNT(*)";
        $sql .= " FROM  {certifygen_error} ce";
        $sql .= " INNER JOIN {certifygen_validations} cv ON cv.id = ce.validationid";
        $sql .= " INNER JOIN {certifygen_model} cm ON cm.id = cv.modelid";
        $sql .= " INNER JOIN {user} u ON u.id = cv.userid";
        $sql .= " LEFT JOIN {certifygen} c ON c.id = cv.certifygenid";
        $sql .= $wheresql;

        return $DB->count_records_sql($sql, $params);
    }

    /**
     * get_errors
     * @param string $userfullname
     * @param string $modelname
     * @return array
     * @throws dml_exception
     */
    public static function get_errors(string $userfullname = '', string $modelname = '',
                                      int $limitfrom, int $limitnum): array {
        global $DB;

        $params = [];
        $where = "";
        if (!empty($modelname)) {
            $params['modelname'] = '%' . $modelname . '%';
            $where .= $DB->sql_like('cm.name', ':modelname');
        }
        if (!empty($userfullname)) {
            $fullname = $DB->sql_fullname();
            $params['search'] = '%' . $userfullname . '%';
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $params['search'] = '%' . $DB->sql_like_escape($userfullname) . '%';
            $where .= $DB->sql_like($fullname, ':search', false);
        }
        $wheresql = '';
        if (!empty($where)) {
            $wheresql = ' WHERE ' . $where;
        }

        $sql = "SELECT ce.id, ce.`status`, ce.code AS errorcode, ce.message AS errormessage, ce.timecreated,";
        $sql .= " ce.validationid, cv.name AS teacherreportname, c.name AS activityname,cm.validation AS modelvalidation,";
        $sql .= " cm.report AS modelreport, cm.repository AS modelrepository,cm.type AS modeltype, cm.name as modelname,";
        $sql .= " u.id as userid, u.picture, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,";
        $sql .= " u.middlename, u.alternatename, u.imagealt, u.email, cv.certifygenid";
        $sql .= " FROM  {certifygen_error} ce";
        $sql .= " INNER JOIN {certifygen_validations} cv ON cv.id = ce.validationid";
        $sql .= " INNER JOIN {certifygen_model} cm ON cm.id = cv.modelid";
        $sql .= " INNER JOIN {user} u ON u.id = cv.userid";
        $sql .= " LEFT JOIN {certifygen} c ON c.id = cv.certifygenid ";
        $sql .= $wheresql;
        $sql .= "ORDER BY ce.timecreated DESC";

        return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
    }
}
