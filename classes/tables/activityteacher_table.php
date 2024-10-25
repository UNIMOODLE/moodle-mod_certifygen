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
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\tables;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

use cm_info;
use coding_exception;
use context_course;
use context_module;
use dml_exception;
use mod_certifygen\certifygen;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use stdClass;
use table_sql;
/**
 * activityteacher_table
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activityteacher_table extends table_sql {
    /** @var int $courseid */
    private int $courseid;
    /** @var context_module $context */
    private context_module $context;
    /** @var int $templateid */
    private int $templateid;
    /** @var int $cmid */
    private int $cmid;
    /** @var int $instanceid */
    private int $instanceid;
    /** @var int|bool|mixed|null $modelid */
    private int $modelid;
    /** @var string $lang */
    private string $lang;
    /** @var string $langstring */
    private string $langstring;
    /** @var bool $canrevoke */
    private bool $canrevoke;
    /** @var array $statusmessages */
    private array $statusmessages;

    /**
     * Constructor
     * @param int $courseid template id
     * @param int $templateid
     * @param int $instance
     * @throws coding_exception|moodle_exception
     */
    function __construct(int $courseid, int $templateid, int $instance) {
        $this->instanceid = $instance;
        $this->courseid = $courseid;
        $this->templateid = $templateid;
        $certifygen = new \mod_certifygen\persistents\certifygen($instance);
        /** @var cm_info $cm**/
        [$course, $cm] = get_course_and_cm_from_instance($instance, 'certifygen', $courseid);
        $this->cmid = $cm->id;
        $this->modelid = \mod_certifygen\persistents\certifygen::get_modelid_from_certifygenid($instance);
        $this->model = new certifygen_model($this->modelid);
        $uniqueid = 'certifygen-activity-teacher-view';
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = ['fullname', 'status', 'dateissued', 'emit', 'download', 'revoke' ];
        $this->define_columns($columns);
        $validationplugin = $this->model->get('validation');
        $this->canrevoke = false;
        $context = context_course::instance($courseid);
        $contextmodule = context_module::instance($cm->id);
        $this->context = $contextmodule;
        $validationpluginclass = $validationplugin . '\\' . $validationplugin;
        /** @var ICertificateValidation $subplugin */
        $subplugin = new $validationpluginclass();
        if (has_capability('moodle/course:managegroups', $context) && !empty($validationplugin)) {
            $this->canrevoke = $subplugin->can_revoke($course->id);
        }
        // Messages.
        $this->statusmessages = $subplugin->get_status_messages();
        // Define the titles of columns to show in header.
        $headers = [
            get_string('fullname'),
            get_string('status', 'mod_certifygen'),
            get_string('date'),
            '',
            '',
            '',
        ];

        $this->define_headers($headers);
    }

    /**
     * This function is called for each data row to allow processing of the
     * username value.
     *
     * @param object $row Contains object with all the values of record.
     * @return string $string Return username with link to profile or username only
     *     when downloading.
     */
    public function col_fullname($row): string {
        global $OUTPUT;
        $data = [
            'id' => $row->id,
            'picture' => $row->picture,
            'firstname' => $row->firstname,
            'lastname' => $row->lastname,
            'firstnamephonetic' => $row->firstnamephonetic,
            'lastnamephonetic' => $row->lastnamephonetic,
            'middlename' => $row->middlename,
            'alternatename' => $row->alternatename,
            'imagealt' => $row->imagealt,
            'email' => $row->email,
        ];

        return $OUTPUT->user_picture((object) $data, ['size' => 35, 'courseid' => $this->courseid,
            'includefullname' => true]);
    }

    /**
     * Revoke
     * @param $row
     * @return string
     * @throws coding_exception
     */
    public function col_revoke($row): string {
        global $USER;
        if (
            $USER->id != $row->id
            && !has_capability('mod/certifygen:canmanagecertificates', $this->context, $USER->id)
        ) {
            return '';
        }
        if (!$this->canrevoke) {
            return '';
        }
        $status = $row->cstatus;
        if (is_null($row->cstatus)) {
            $status = certifygen_validations::STATUS_NOT_STARTED;
        }
        if ($status == certifygen_validations::STATUS_FINISHED) {
            return '<span class="likelink" data-action="revoke-certificate" data-username="' . $row->firstname . ' '
                . $row->lastname . '" data-issueid="' . $row->issueid . '" data-modelid="' . $this->modelid
                . '" data-courseid="' . $this->courseid . '" data-userid="' . $row->id . '" data-cmid="' . $this->cmid . '"
                data-lang="' . $this->lang . '" data-langstring="' . $this->langstring . '"  >' .
                get_string('revoke', 'tool_certificate') . '</span>';
        }
        return '';
    }

    /**
     * Status
     * @param $row
     * @return string
     * @throws coding_exception
     */
    public function col_status($row): string {
        if (empty($row->cstatus)) {
            return get_string('status_1', 'mod_certifygen');
        }
        $status = get_string('status_' . $row->cstatus, 'mod_certifygen');
        if (!empty($this->statusmessages) && array_key_exists($row->cstatus, $this->statusmessages)) {
            $tooltip = $this->statusmessages[$row->cstatus];
            $status = get_string('status_' . $row->cstatus, 'mod_certifygen') . '</button>';
            $status .= ' ';
            $status .= '<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="'
                    . $tooltip . '">'
                    . '<i class="icon fa fa-exclamation-triangle"></i>'
                    . '</button>';
        }
        return $status;
    }

    /**
     * Date issued
     * @param $row
     * @return string
     */
    public function col_dateissued($row): string {
        if (empty($row->ctimecreated)) {
            return '';
        }
        return date('d/m/y H:i:s', $row->ctimecreated);
    }

    /**
     * Download
     * @param $row
     * @return string
     * @throws coding_exception
     */
    public function col_download($row): string {
        $status = $row->cstatus;
        if (is_null($row->cstatus)) {
            $status = certifygen_validations::STATUS_NOT_STARTED;
        }
        if ($status == certifygen_validations::STATUS_FINISHED) {
            return '<span data-courseid="' . $row->courseid . '" data-instanceid="' . $this->instanceid
                . '" data-modelid="' . $this->modelid . '" data-id="' . $row->validationid
                . '" data-action="download-certificate" data-userid="' . $row->id . '" data-code="'
                . $row->code . '" data-lang="' . $this->lang . '" data-langstring="' . $this->langstring
                . '"  data-cmid="' . $this->cmid . '" class="btn btn-primary">'
                . get_string('download') . '</span>';
        }
        return '';
    }
    /**
     * Issue
     * @param $row
     * @return string
     * @throws moodle_exception
     * @throws coding_exception
     */
    public function col_emit($row): string {
        global $USER;

        if (
            $USER->id != $row->id
            && !has_capability('mod/certifygen:canmanagecertificates', $this->context, $USER->id)
        ) {
            return '';
        }

        $status = $row->cstatus;
        $id = $row->validationid;
        if (is_null($row->cstatus)) {
            $status = certifygen_validations::STATUS_NOT_STARTED;
            $id = 0;
        }

        if ($status == certifygen_validations::STATUS_NOT_STARTED) {
            return '<span data-courseid="' . $row->courseid . '" data-modelid="' . $this->modelid . '" data-id="' . $id .
                '" data-action="emit-certificate" data-userid="' . $row->userid . '" data-lang="' . $this->lang . '"
                data-langstring="' . $this->langstring . '"  data-cmid="' . $this->cmid . '" data-instanceid="'
                . $this->instanceid . '" class="btn btn-primary">'
                . get_string('emit', 'mod_certifygen') . '</span>';
        }
        // Re-emit.
        if (
            $row->cstatus == certifygen_validations::STATUS_FINISHED
            && $this->model->get('mode') == certifygen_model::MODE_PERIODIC
            && has_capability('mod/certifygen:reemitcertificates', $this->context)
        ) {
            $today = time();
            $dif = $today - $row->timecreated;
            $difindays = abs($dif / (60 * 60 * 24));
            if ($difindays > (int)$this->model->get('timeondemmand')) {
                return '<span class="likelink" data-userid="' . $row->userid . '" data-id="' . $id
                    . '" data-modelid="' . $this->modelid . '" data-lang="' . $this->lang
                    . '" data-courseid="' . $this->courseid . '" data-cmid="' . $this->cmid
                    . '" data-action="reemit-certificate">' .
                    get_string('reemit', 'mod_certifygen') . '</span>';
            }
        }
        return '';
    }

    /**
     * Query the reader.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar?
     * @throws dml_exception
     * @throws coding_exception
     */
    public function query_db($pagesize, $useinitialsbar = true): void {

        $userid = 0;
        $tifirst = '';
        $tilast = '';
        if ($this->filterset->has_filter('userid')) {
            $userid = $this->filterset->get_filter('userid')->current();
        }
        if ($this->filterset->has_filter('tifirst')) {
            $tifirst = $this->filterset->get_filter('tifirst')->current();
        }
        if ($this->filterset->has_filter('tilast')) {
            $tilast = $this->filterset->get_filter('tilast')->current();
        }
        $this->lang = $this->filterset->get_filter('lang')->current();
        $langs = get_string_manager()->get_list_of_translations();
        if (!mod_certifygen_lang_is_installed($this->lang)) {
            $a = new stdClass();
            $a->lang = $this->lang;
            throw new moodle_exception('lang_not_exists', 'mod_certifygen', '', $a);
        }
        $this->langstring = $langs[$this->lang];
        $params['lang'] = $this->filterset->get_filter('lang')->current();
        $students = certifygen::get_students($this->cmid, $this->courseid);

        if ($userid && !in_array($userid, $students)) {
            throw new moodle_exception('notastudent');
        } else if ($userid && in_array($userid, $students)) {
            $students = [$userid];
        }

        $total = certifygen::count_issues_for_course_by_lang(
            $params['lang'],
            $this->instanceid,
            $this->templateid,
            $this->courseid,
            'mod_certifygen',
            $students,
            $tifirst,
            $tilast,
        );
        $this->pagesize($pagesize, $total);

        $this->rawdata = certifygen::get_issues_for_course_by_lang(
            $params['lang'],
            $this->instanceid,
            $this->templateid,
            $this->courseid,
            'mod_certifygen',
            $students,
            $tifirst,
            $tilast,
            $this->get_page_start(),
            $this->get_page_size(),
        );
        // Set initial bars.
        $this->initialbars($total > $pagesize);
    }

    /**
     * print_nothing_to_display
     * @return void
     * @throws coding_exception
     */
    public function print_nothing_to_display(): void {
        global $OUTPUT;
        echo $this->render_reset_button();
        $this->print_initials_bar();
        echo $OUTPUT->heading(get_string('nothingtodisplay'), 4);
    }
}
