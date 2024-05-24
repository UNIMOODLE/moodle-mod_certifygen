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

require_once($CFG->libdir . '/tablelib.php');
use mod_certifygen\template;
use table_sql;
use tool_certificate\certificate;

class activityteacherview_table extends table_sql {
    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     * @param int $courseid course id
     * @param int $courseid template id
     */
    function __construct(int $courseid, int $templateid) {
        $this->courseid = $courseid;
        $this->templateid = $templateid;
        $uniqueid = 'certifygen-activity-teacher-view';
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('fullname', 'code','status', 'link');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array(get_string('fullname'),
            get_string('code', 'mod_certifygen'),
            get_string('status', 'mod_certifygen'), '');
        $this->define_headers($headers);

    }
    /**
     * This function is called for each data row to allow processing of the
     * username value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    function col_fullname($values) {

        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return $values->firstname .  ' ' . $values->lastname;
        } else {
            global $OUTPUT;

            return $OUTPUT->user_picture($values, array('size' => 35, 'courseid' => $this->courseid, 'includefullname' => true));
        }
    }

    /**
     * @param $values
     * @return string
     */
    function col_status($values) {
        return 'PENDIENTE';
    }

    /**
     * @param $values
     * @return mixed
     */
    function col_code($values) {
        return $values->code;
    }

    /**
     * @param $values
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    function col_link($values) {
        global $DB;
        $certificate = template::instance($values->templateid);
        $issueid = $certificate->issue_certificate($values->userid);
        $code = $DB->get_field('tool_certificate_issues', 'code', ['id' => $issueid]);
        $link = new \moodle_url('/mod/certifygen/certificateview.php', ['code' => $code, 'preview' => true, 'templateid' => $values->templateid]);


        if ($this->is_downloading()) {
            return $link->out();
        } else {
            return '<a href='. $link->out().'>'.get_string('download', 'mod_certifygen').'</a>';
        }
    }
    /**
     * Query the reader.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {

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
     * @throws \coding_exception
     */
    public function print_nothing_to_display() {
        global $OUTPUT;
        echo $this->render_reset_button();
        $this->print_initials_bar();
        echo $OUTPUT->heading(get_string('nothingtodisplay'), 4);
    }
}