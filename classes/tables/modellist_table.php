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
require_once("$CFG->libdir/tablelib.php");
use coding_exception;
use dml_exception;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\template;
use table_sql;
/**
 * Model list table
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class modellist_table extends table_sql {
    /**
     * Construct
     * @throws coding_exception
     */
    public function __construct() {

        $uniqueid = 'certifygen-model-list-view';
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = ['modelname', 'template', 'lastupdate', 'type', 'editmodel', 'deletemodel', 'associatecontexts'];
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = [
            get_string('modelname', 'mod_certifygen'),
            get_string('templatereport', 'mod_certifygen'),
            get_string('lastupdate', 'mod_certifygen'),
            get_string('type', 'mod_certifygen'),
            '', '', ''];
        $this->define_headers($headers);
    }

    /**
     * QUery db
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @return void
     */
    final public function query_db($pagesize, $useinitialsbar = true): void {

        $total = certifygen_model::count_records();

        $this->pagesize($pagesize, $total);
        $this->rawdata = certifygen_model::get_records(
            [],
            $this->get_sql_sort(),
            'ASC',
            $this->get_page_start(),
            $this->get_page_size()
        );

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    /**
     * Type
     * @param $values
     * @return string
     * @throws coding_exception
     */
    final public function col_type($values): string {
        return get_string('type_' . $values->get('type'), 'mod_certifygen');
    }

    /**
     * Model name
     * @param $values
     * @return string
     */
    final public function col_modelname($values): string {
        return $values->get('name');
    }


    /**
     * Template
     * @param $values
     * @return string
     * @throws coding_exception
     */
    final public function col_template($values): string {
        if (empty($values->get('templateid'))) {
            return get_string('pluginname', $values->get('report'));
        }
        try {
            return template::instance($values->get('templateid'))->get_name();
        } catch (\moodle_exception $e) {
            return '-';
        }
    }

    /**
     * Last update date
     * @param $values
     * @return string
     */
    final public function col_lastupdate($values): string {
        return date('d-m-Y', $values->get('timemodified'));
    }

    /**
     * Delete model
     * @param $values
     * @return string
     * @throws coding_exception
     */
    final public function col_deletemodel($values): string {
        return '<span class="likelink" data-id="' . $values->get('id') . '" data-name="' . $values->get('name')
            . '" data-action="delete-model">'
            . get_string('delete', 'mod_certifygen') . '</span>';
    }

    /**
     * Edit
     * @param $values
     * @return string
     * @throws coding_exception
     */
    final public function col_editmodel($values): string {
        return '<span class="likelink" data-action="edit-model" data-id="' . $values->get('id') . '">'
            . get_string('edit', 'mod_certifygen') . '</span>';
    }

    /**
     * Associate context
     * @param $values
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    final public function col_associatecontexts($values): string {
        global $DB;

        $contextid = $DB->get_field('certifygen_context', 'id', ['modelid' => $values->get('id')]);

        if (empty($contextid)) {
            $contextid = 0;
        }
        return '<span class="likelink" data-action="assign-context" data-id="' . $contextid . '" data-modelid="'
            . $values->get('id') . '" data-name="' . $values->get('name') . '">'
            . get_string('assigncontext', 'mod_certifygen') . '</span>';
    }
}
