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
use mod_certifygen\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use mod_certifygen\template;
use moodle_exception;
use moodle_url;
use table_sql;
use tool_certificate\certificate;

class activityteacherview_table extends table_sql {
    private int $courseid;
    private int $templateid;
    private int $instance;
    /**
     * @var certifygen_model[]
     */
    private certifygen_model $model;

    /**
     * Constructor
     * @param int $courseid template id
     * @param int $templateid
     * @throws coding_exception
     */
    function __construct(int $courseid, int $templateid, int $instance) {
        $this->courseid = $courseid;
        $this->templateid = $templateid;
        $certifygen = new \mod_certifygen\persistents\certifygen($instance);
        $this->modelid = $certifygen->get('modelid');
        $uniqueid = 'certifygen-activity-teacher-view';
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = ['fullname', 'code', 'status', 'lang', 'link'];
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = [
            get_string('fullname'),
            get_string('code', 'mod_certifygen'),
            get_string('status', 'mod_certifygen'),
            get_string('language'),
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
    function col_fullname($row): string
    {

        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return $row->firstname .  ' ' . $row->lastname;
        } else {
            global $OUTPUT;

            return $OUTPUT->user_picture($row, array('size' => 35, 'courseid' => $this->courseid, 'includefullname' => true));
        }
    }

    /**
     * @param $row
     * @return string
     */
    function col_lang($row): string
    {
        if (isset($row->issueid)) {
            $validation = certifygen_validations::get_record(['userid' => $row->userid, 'issuesid' => $row->issueid]);
            if ($validation) {
                $validation->get('lang');
            }
        }

        return '-';
    }

    /**
     * @param $row
     * @return string
     */
    function col_status($row): string
    {
        if (isset($row->issueid)) {
            $validation = certifygen_validations::get_record(['userid' => $row->userid, 'issuesid' => $row->issueid]);
            if ($validation) {
                return get_string('status_'.$validation->get('status'), 'mod_certifygen');
            }
        }

        return get_string('status_1', 'mod_certifygen');
    }

    /**
     * @param $row
     * @return mixed
     */
    function col_code($row): string
    {
        return $row->code;
    }

    /**
     * @param $row
     * @return string
     * @throws dml_exception
     * @throws moodle_exception
     * @throws coding_exception
     */
    function col_link($row): string
    {
        global $DB;
        //TODO! $lang
        $lang = 'en';
        $certificate = template::instance($row->templateid, (object) ['lang' => $lang]);
        $issueid = $certificate->issue_certificate($row->userid);
        $code = $DB->get_field('tool_certificate_issues', 'code', ['id' => $issueid]);
        $link = new moodle_url('/mod/certifygen/certificateview.php', ['code' => $code, 'preview' => true, 'templateid' => $row->templateid]);
        $status = certifygen_validations::STATUS_NOT_STARTED;
        $id = 0;
        if (isset($row->issueid)) {
            $validation = certifygen_validations::get_record(['userid' => $row->userid, 'issuesid' => $row->issueid]);
            if ($validation) {
                $id = $validation->get('id');
                $status = $validation->get('status');
            }
        }

        if ($this->is_downloading()) {
            return $link->out();
        } else if ($status != certifygen_validations::STATUS_IN_PROGRESS) {
            return '<span data-courseid="' . $row->courseid . '" data-modelid="' . $this->modelid . '" data-id="'. $id .
                '" data-action="emit-certificate" data-userid="'. $row->userid .'" class="btn btn-primary"
                href='. $link->out().'>'.get_string('emit', 'mod_certifygen').'</span>';
        } else if ($status == certifygen_validations::STATUS_FINISHED_OK) {
            return 'Pendiente desacargar funcionalidad';
        } else {
            return '';
        }
    }
    /**
     * Query the reader.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar?
     */
    public function query_db($pagesize, $useinitialsbar = true): void
    {

        $total = certificate::count_issues_for_course($this->templateid, $this->courseid, 'mod_certifygen', 0, 0);

        $this->pagesize($pagesize, $total);

        $this->rawdata = certificate::get_issues_for_course($this->templateid, $this->courseid, 'mod_certifygen', 0 , 0, $this->get_page_start(),
            $this->get_page_size(), $this->get_sql_sort());

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