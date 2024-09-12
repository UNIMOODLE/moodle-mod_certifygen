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

use coding_exception;
use dml_exception;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use stdClass;
use table_sql;
/**
 * profile_my_certificates_table
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_my_certificates_table extends table_sql {
    /** @var int $userid */
    private int $userid;
    /** @var array $langstrings */
    private array $langstrings;
    /**
     * Constructor
     * @param int $courseid template id
     * @param int $templateid
     * @param int $instance
     * @throws coding_exception|moodle_exception
     */
    public function __construct(int $userid) {
        $this->userid = $userid;
        $this->langstrings = get_string_manager()->get_list_of_translations();
        $uniqueid = 'profile-my-certificates-view';
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = ['name', 'status', 'date',  'lang', 'seecourses', 'emit', 'download', 'delete'];
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = [
            get_string('name', 'mod_certifygen'),
            get_string('status', 'mod_certifygen'),
            get_string('date'),
            get_string('language'),
            '',
            '',
            '',
            '',
        ];
        $this->define_headers($headers);
    }

    /**
     * Name
     * @param $row
     * @return string
     */
    final function col_name(stdClass $row): string {
        return $row->name;
    }

    /**
     * Date
     * @param $row
     * @return string
     */
    final function col_date(stdClass $row): string {
        if (empty($row->timecreated)) {
            return '';
        }
        return date('d/m/y', $row->timecreated);
    }

    /**
     * Status
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     */
    final function col_status(stdClass $row): string {

        $status = get_string('status_' . $row->status, 'mod_certifygen');
        if (empty($row->status)) {
            return $status;
        }
        $validationplugin = $row->validation;
        $validationpluginclass = $validationplugin . '\\' . $validationplugin;
        /** @var ICertificateValidation $subplugin */
        $subplugin = new $validationpluginclass();
        $statusmessages = $subplugin->get_status_messages();
        if (!empty($statusmessages) && array_key_exists($row->status, $statusmessages)) {
            $tooltip = $statusmessages[$row->status];
            $status = '<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" 
            title="'.$tooltip.'"> ' . get_string('status_' . $row->status, 'mod_certifygen')
                . '</button>';
        }
        return $status;
    }

    /**
     * Language
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     */
    final function col_lang(stdClass $row): string {
        return $this->langstrings[$row->lang];
    }
    /**
     * See courses
     * @param $row
     * @return mixed
     * @throws moodle_exception
     */
    final function col_seecourses(stdClass $row): string {
        return '<span class="likelink" data-name="' . $row->name . '" data-action="see-courses" data-courses="' . $row->courses . '">'
            . get_string('seecourses', 'mod_certifygen') . '</span>';
    }

    /**
     * Issue certificate
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     */
    final function col_emit(stdClass $row): string {
        // Emit.
        if ($row->status == certifygen_validations::STATUS_NOT_STARTED) {
            return '<span class="likelink" data-userid="' . $row->userid . '" data-id="' . $row->id . '" data-action="emit">' .
                get_string('emit', 'mod_certifygen') . '</span>';
        }
        // Re-emit.
        if ($row->status == certifygen_validations::STATUS_FINISHED
        && $row->mode == certifygen_model::MODE_PERIODIC) {
            $today = time();
            $dif = $today - $row->timecreated;
            $difindays = abs($dif / (60 * 60 * 24));
            if ($difindays > (int)$row->timeondemmand) {
                return '<span class="likelink" data-userid="' . $row->userid . '" data-id="' . $row->id . '" data-action="reemit">' .
                    get_string('reemit', 'mod_certifygen') . '</span>';
            }
        }
        return '';
    }

    /**
     * Download
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     */
    final function col_download(stdClass $row): string {
        $status = $row->status;
        if (is_null($status)) {
            $status = certifygen_validations::STATUS_NOT_STARTED;
        }
        if ($status == certifygen_validations::STATUS_FINISHED) {
            return '<span data-id="'. $row->id . '" data-action="download-certificate" data-name="' . $row->name . '"
            data-userid="'. $row->userid .'" class="btn btn-primary">' . get_string('download') . '</span>';
        }
        return '';
    }

    /**
     * Delete
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     */
    final function col_delete(stdClass $row): string {
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
    public function query_db($pagesize, $useinitialsbar = true): void {
        $total = certifygen_validations::count_my_requests_as_teachers($this->userid);
        $this->pagesize($pagesize, $total);
        $this->rawdata = certifygen_validations::get_my_requests_as_teacher($this->userid, $this->get_page_start(),
            $this->get_page_size());

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
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
