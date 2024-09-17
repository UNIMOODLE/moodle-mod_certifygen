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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->libdir/tablelib.php");
require_once("$CFG->libdir/moodlelib.php");

use coding_exception;
use dml_exception;
use html_writer;
use mod_certifygen\certifygen;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_model;
use moodle_url;
use table_sql;
/**
 * showerrors_table
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class showerrors_table extends table_sql {
    /**
     * Construct
     * @throws coding_exception
     */
    function __construct() {

        $uniqueid = 'certifygen-showerrors-view';
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = ['user', 'status', 'message', 'model', 'type', 'validation', 'repository', 'report', 'name',
            'validationid', 'timecreated', 'download'];
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = [
            get_string('user'),
            get_string('status', 'mod_certifygen'),
            get_string('message'),
            get_string('model', 'mod_certifygen'),
            get_string('type', 'mod_certifygen'),
            get_string('validation', 'mod_certifygen'),
            get_string('repository', 'mod_certifygen'),
            get_string('report', 'mod_certifygen'),
            get_string('name', 'mod_certifygen'),
            get_string('idrequest', 'mod_certifygen'),
            get_string('date'),
            get_string('download'),
            ];
        $this->define_headers($headers);
    }

    /**
     * query_db
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @return void
     * @throws dml_exception
     */
    final public function query_db($pagesize, $useinitialsbar = true): void {

        $userfullname = $modelname = '';
        if ($this->filterset->has_filter('userfullname')) {
            $userfullname = $this->filterset->get_filter('userfullname')->current();
        }
        if ($this->filterset->has_filter('modelname')) {
            $modelname = $this->filterset->get_filter('modelname')->current();
        }

        $total = certifygen::count_errors($userfullname, $modelname);
        $this->pagesize($pagesize, $total);
        $this->rawdata = certifygen::get_errors(
            $userfullname,
            $modelname,
            $this->get_page_start(),
            $this->get_page_size()
        );
    }

    /**
     * User
     * @param $row
     * @return string
     */
    final public function col_user($row): string {
        global $OUTPUT;
        $data = [
            'id' => $row->userid,
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

        return $OUTPUT->user_picture((object) $data, ['size' => 35, 'includefullname' => true]);
    }
    /**
     * Download certificate
     * @param $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    final public function col_download($row): string {

        // Validation plugin.
        $validationplugin = $row->modelvalidation;
        $validationpluginclass = $validationplugin . '\\' . $validationplugin;
        if (get_config($validationplugin, 'enabled') === '1') {
            /** @var ICertificateValidation $subplugin */
            $subplugin = new $validationpluginclass();
            $courseid = 0;
            if ($row->modeltype == certifygen_model::TYPE_ACTIVITY) {
                $certifygen = new \mod_certifygen\persistents\certifygen($row->certifygenid);
                $courseid = $certifygen->get('course');
            }
            $response = $subplugin->get_file($courseid, $row->validationid);
            if (array_key_exists('file', $response)) {
                $file = $response['file'];
                $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                )->out();
                return html_writer::link($url, get_string('download'));
            }
        }
        return '';
    }
    /**
     * Type
     * @param $values
     * @return string
     * @throws coding_exception
     */
    final public function col_type($values): string {
        return get_string('type_' . $values->modeltype, 'mod_certifygen');
    }
    /**
     * Validation
     * @param $values
     * @return string
     * @throws coding_exception
     */
    final public function col_validation($values): string {
        return get_string('pluginname', $values->modelvalidation);
    }
    /**
     * Report
     * @param $values
     * @return string
     * @throws coding_exception
     */
    final public function col_report($values): string {
        if (empty($values->modelreport)) {
            return '';
        }
        return get_string('pluginname', $values->modelreport);
    }
    /**
     * Repository
     * @param $values
     * @return string
     * @throws coding_exception
     */
    final public function col_repository($values): string {
        if (empty($values->modelrepository)) {
            return '';
        }
        return get_string('pluginname', $values->modelrepository);
    }

    /**
     * Name
     * @param $values
     * @return string
     */
    final public function col_name($values): string {
        if (!empty($values->teacherreportname)) {
            return $values->teacherreportname;
        } else {
            return $values->activityname;
        }
    }
    /**
     * Status
     * @param $values
     * @return string
     * @throws coding_exception
     */
    final public function col_status($values): string {
        return get_string('status_' . $values->status, 'mod_certifygen');
    }

    /**
     * Message
     * @param $values
     * @return string
     */
    final public function col_message($values): string {
        return $values->errormessage;
    }
    /**
     * Model
     * @param $values
     * @return string
     */
    final public function col_model($values): string {
        return $values->modelname;
    }
    /**
     * Validation id
     * @param $values
     * @return string
     */
    final public function col_validationid($values): string {
        return $values->validationid;
    }
    /**
     * Timecreated
     * @param $values
     * @return string
     */
    final public function col_timecreated($values): string {
        return userdate($values->timecreated);
    }
}
