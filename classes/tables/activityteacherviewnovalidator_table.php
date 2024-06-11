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
require_once($CFG->libdir . '/modinfolib.php');

use cm_info;
use coding_exception;
use dml_exception;
use mod_certifygen\certifygen;
use mod_certifygen\template;
use moodle_exception;
use moodle_url;
use table_sql;

class activityteacherviewnovalidator_table extends table_sql {
    private int $courseid;
    private int $templateid;
    private int $cmid;
    private int $modelid;

    /**
     * Constructor
     * @param int $courseid template id
     * @param int $templateid
     * @param int $modelid
     * @throws coding_exception|moodle_exception
     */
    function __construct(int $courseid, int $templateid, int $modelid) {
        $this->courseid = $courseid;
        $this->templateid = $templateid;
        $this->modelid = $modelid;
        $certifygen = \mod_certifygen\persistents\certifygen::get_record(['modelid' => $modelid]);
        /** @var cm_info $cm**/
        [$course, $cm] = get_course_and_cm_from_instance($certifygen->get('id'), 'certifygen', $courseid);
        $this->cmid = $cm->id;
        $uniqueid = 'certifygen-activity-novalidator-teacher-view';
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = ['fullname', 'code', 'status', 'dateissued', 'download', 'revoke' ];
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = [
            get_string('fullname'),
            get_string('code', 'mod_certifygen'),
            get_string('status', 'mod_certifygen'),
            get_string('date'),
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
    function col_fullname($row): string
    {
        global $OUTPUT;

        return $OUTPUT->user_picture($row, array('size' => 35, 'courseid' => $this->courseid, 'includefullname' => true));
    }

    /**
     * @param $row
     * @return string
     */
    function col_dateissued($row): string
    {
        return date('d/m/y', $row->timecreated);
    }

    /**
     * @param $row
     * @return string
     * @throws coding_exception
     */
    function col_status($row): string
    {

        return $row->status == 0 ? get_string('expired', 'tool_certificate')
            : get_string('valid', 'tool_certificate');
    }

    /**
     * @param $row
     * @return mixed
     * @throws moodle_exception
     */
    function col_code($row): string
    {
        $url = new moodle_url('/admin/tool/certificate/index.php', ['code' => $row->code]);
        return '<a href="' . $url->out() . '">' . $row->code . '</a>';
    }

    /**
     * @param $row
     * @return string
     * @throws coding_exception
     */
    function col_revoke($row): string
    {
        $code = explode('_', $row->code);
        $lang = $code[1];
        return '<span class="likelink" data-action="revoke-certificate" data-username="'. $row->firstname. ' '
            . $row->lastname .'" data-issueid="'. $row->issueid.'" data-modelid="'. $this->modelid
            .'" data-courseid="'. $this->courseid.'" data-userid="'. $row->userid.'" data-cmid="'.  $this->cmid .'">' .
            get_string('revoke', 'tool_certificate') . '</span>';
    }

    /**
     * @param $row
     * @return string
     * @throws dml_exception
     * @throws moodle_exception
     * @throws coding_exception
     */
    function col_download($row): string
    {
        global $DB;
        $code = $DB->get_field('tool_certificate_issues', 'code', ['id' => $row->issueid]);
        $link = new moodle_url('/mod/certifygen/certificateview.php', ['code' => $code, 'templateid' => $row->templateid]);

        return '<a href='. $link->out().'>'.get_string('download', 'mod_certifygen').'</a>';
    }

    /**
     * Query the reader.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar?
     * @throws dml_exception
     * @throws coding_exception
     */
    public function query_db($pagesize, $useinitialsbar = true): void
    {

        $userid = 0;
        $groupmode = 0;
        $groupid = 0;
        if ($this->filterset->has_filter('userid')) {
            $userid = $this->filterset->get_filter('userid')->current();
        }
        $params['lang'] = $this->filterset->get_filter('lang')->current();
        $total = certifygen::count_issues_for_course_by_lang($params['lang'], $this->templateid, $this->courseid,
            'mod_certifygen', $userid, $groupmode, $groupid);

        $this->pagesize($pagesize, $total);

        $this->rawdata = certifygen::get_issues_for_course_by_lang($params['lang'], $this->templateid, $this->courseid,
            'mod_certifygen', $userid, $groupmode, $groupid, $this->get_page_start(),
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