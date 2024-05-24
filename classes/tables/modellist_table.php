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

/**
 *
 * @package     XXXX
 * @author      202X Elena Barrios Galán <elena@tresipunt.com>
 * @copyright   3iPunt <https://www.tresipunt.com/>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\tables;

require "$CFG->libdir/tablelib.php";
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\template;
use table_sql;
class modellist_table extends table_sql {

    /**
     * @throws \coding_exception
     */
    function __construct() {

        $uniqueid = 'certifygen-model-list-view';
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('modelname', 'template', 'lastupdate', 'editmodel', 'deletemodel');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array(
            get_string('modelname', 'mod_certifygen'),
            get_string('template', 'mod_certifygen'),
            get_string('lastupdate', 'mod_certifygen'),
            '', '');
        $this->define_headers($headers);

    }

    /**
     * @param $pagesize
     * @param $useinitialsbar
     * @return void
     * @throws \dml_exception
     */
    public final function query_db($pagesize, $useinitialsbar = true) {

        $total = certifygen_model::count_context_models();

        $this->pagesize($pagesize, $total);
        $this->rawdata = certifygen_model::get_context_models($this->get_page_start(), $this->get_page_size(),
            $this->get_sql_sort());

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    /**
     * @param $values
     * @return string
     */
    final function col_modelname($values) : string {
        return $values->name;
    }

    /**
     * @param $values
     * @return string
     */
    final function col_template($values) : string {
        return template::instance($values->templateid)->get_name();
    }

    /**
     * @param $values
     * @return string
     */
    final function col_lastupdate($values) : string {
        return date('d-m-Y', $values->timemodified);
    }

    /**
     * @param $values
     * @return string
     * @throws \coding_exception
     */
    final function col_deletemodel($values) : string {
        return '<span class="likelink" data-id="'. $values->id . '" data-name="'. $values->name . '" data-action="delete-model">'
            . get_string('delete', 'mod_certifygen') . '</span>';
    }

    /**
     * @param $values
     * @return string
     * @throws \coding_exception
     */
    final function col_editmodel($values) : string {
        return '<span class="likelink" data-action="edit-model" data-id="'. $values->id . '">'.get_string('edit', 'mod_certifygen').'</span>';
    }
}