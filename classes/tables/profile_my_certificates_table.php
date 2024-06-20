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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\tables;
global $CFG;
require_once($CFG->libdir . '/tablelib.php');

use coding_exception;
use dml_exception;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_teacherrequests;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use moodle_url;
use stdClass;
use table_sql;

class profile_my_certificates_table extends table_sql {
    private int $userid;
    private array $langstrings;
    /**
     * Constructor
     * @param int $courseid template id
     * @param int $templateid
     * @param int $instance
     * @throws coding_exception|moodle_exception
     */
    function __construct(int $userid) {
        $this->userid = $userid;
        $this->langstrings = get_string_manager()->get_list_of_translations();
        $uniqueid = 'profile-my-certificates-view';
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = ['id', 'status', 'lang', 'seecourses', 'emit', 'download', 'delete'];
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = [
            get_string('requestid', 'mod_certifygen'),
            get_string('status', 'mod_certifygen'),
            get_string('language'),
            '',
            '',
            '',
            '',
        ];
        $this->define_headers($headers);
    }


    /**
     * @param $row
     * @return string
     */
    function col_id(stdClass $row): string
    {
        return $row->id;
    }

    /**
     * @param $row
     * @return string
     * @throws coding_exception
     */
    function col_status(stdClass $row): string
    {
        return get_string('status_' . $row->status, 'mod_certifygen');
    }

    /**
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     */
    function col_lang(stdClass $row): string
    {
        return $this->langstrings[$row->lang];
    }
    /**
     * @param $row
     * @return mixed
     * @throws moodle_exception
     */
    function col_seecourses(stdClass $row): string
    {
        return '<span class="likelink" data-id="' . $row->id . '" data-action="see-courses" data-courses="' . $row->courses . '">'
            . get_string('seecourses', 'mod_certifygen') . '</span>';
    }
    /**
     * @param $row
     * @return string
     */
    function col_emit(stdClass $row): string
    {
        if (empty($row->validation)) {
            return '';
        }
        if ($row->status == certifygen_validations::STATUS_NOT_STARTED) {
            return '<span class="likelink" data-userid="' . $row->userid . '" data-id="' . $row->id . '" data-action="emit">' .
                get_string('emit', 'mod_certifygen') . '</span>';
        }
        return '';
    }

    /**
     * @param $row
     * @return string
     * @throws dml_exception
     * @throws moodle_exception
     * @throws coding_exception
     */
    function col_download(stdClass $row): string
    {
        if ($row->status != certifygen_teacherrequests::STATUS_FINISHED_OK) {
            return '';
        }
        return 'TODO segun report plugin';
        // No validation plugin associated.
        $link = new moodle_url('/mod/certifygen/certificateview.php',
            ['code' => $row->code, 'templateid' => $row->templateid]);
        if (empty($row->validation)) {
            return '<a href="' . $link->out() . '">' . get_string('download') . '</a>';
        }
        // Validation plugin associated.
        if ($row->status == certifygen_validations::STATUS_FINISHED_OK) {
            $validationpluginclass = $row->validation . '\\' . $row->validation;
            if (get_config($row->validation, 'enable') === '1') {
                /** @var ICertificateValidation $subplugin */
                $subplugin = new $validationpluginclass();
                $url = $subplugin->getFileUrl($this->courseid, $row->validationid, $row->code.'.pdf');
                if (!empty($url)) {
                    return '<a href="' . $url . '">' . get_string('download') . '</a>';
                }
            }
        }
        return '-';
        global $DB;

        $lang = explode('_', $row->code);
        $lang = $lang[1];
        $code = $DB->get_field('tool_certificate_issues', 'code', ['id' => $row->issueid]);
        $link = new moodle_url('/mod/certifygen/certificateview.php', ['code' => $code, 'preview' => true, 'templateid' => $row->templateid]);
        $status = certifygen_validations::STATUS_NOT_STARTED;
        $id = 0;
        if (isset($row->issueid)) {
            $validation = certifygen_validations::get_record(['userid' => $row->userid, 'issueid' => $row->issueid]);
            if ($validation) {
                $id = $validation->get('id');
                $status = $validation->get('status');
            }
        }

        if ($this->is_downloading()) {
            return $link->out();
        } else if ($status == certifygen_validations::STATUS_NOT_STARTED
            || $status == certifygen_validations::STATUS_FINISHED_ERROR) {
            return '<span data-courseid="' . $row->courseid . '" data-modelid="' . $this->modelid . '" data-id="'. $id .
                '" data-action="emit-certificate" data-userid="'. $row->userid .'" class="btn btn-primary"
                href='. $link->out().'>'.get_string('emit', 'mod_certifygen').'</span>';
        } else if ($status == certifygen_validations::STATUS_FINISHED_OK) {
            $validationplugin = $this->model->get('validation');
            $validationrecords = certifygen_validations::get_records(['modelid' => $this->model->get('id'), 'userid' => $row->userid]);
            $validationrecord = null;
            foreach ($validationrecords as $record) {
                if ($record->get('lang') != $lang) {
                    continue;
                }
                $validationrecord = $record;
            }
            $validationpluginclass = $validationplugin . '\\' . $validationplugin;
            if (get_config($validationplugin, 'enable') === '1') {
                /** @var ICertificateValidation $subplugin */
                $subplugin = new $validationpluginclass();
                $url = $subplugin->getFileUrl($this->courseid, $validationrecord->get('id'), $code.'.pdf');
                if (!empty($url)) {
                    return '<a href="' . $url . '">' . get_string('download') . '</a>';
                }
            }
        }
        return '';
    }

    function col_delete(stdClass $row): string {
        return '<span class="likelink" data-action="delete-request" data-id="' . $row->id . '">'.
            get_string('delete', 'mod_certifygen').'</span>';
    }
    /**
     * Query the reader.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar?
     * @throws dml_exception
     */
    public function query_db($pagesize, $useinitialsbar = true): void
    {
        $total = certifygen_teacherrequests::count_my_requests($this->userid);
        $this->pagesize($pagesize, $total);
        $this->rawdata = certifygen_teacherrequests::get_my_requests($this->userid, $this->get_page_start(),
            $this->get_page_size());

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function print_nothing_to_display(): void
    {
        global $OUTPUT;
        echo $this->render_reset_button();
        $this->print_initials_bar();
        echo $OUTPUT->heading(get_string('nothingtodisplay'), 4);
    }
}