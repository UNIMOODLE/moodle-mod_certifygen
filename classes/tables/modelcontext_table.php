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

use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\template;
use table_sql;
use tool_certificate\certificate;

class modelcontext_table extends table_sql {
    /**
     * Constructor
     */
    function __construct() {
        $uniqueid = 'certifygen-model-context-view';
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('modelname', 'contexts', 'assignbutton', 'editbutton');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array(
            get_string('model', 'mod_certifygen'),
            get_string('contexts', 'mod_certifygen'), '', '');
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
    final function col_modelname($values) : string {
        return $values->name;
    }

    /**
     * @param $values
     * @return string
     */
    final  function col_contexts($values) : string {
        return '';
    }

    /**
     * @param $values
     * @return string
     * @throws \coding_exception
     */
    final function col_assignbutton($values) : string {
        if (empty($values->contexts)) {
            return '<span class="likelink" data-action="assign-context">'.get_string('assigncontext', 'mod_certifygen').'</span>';
        }
        return '';
    }

    /**
     * @param $values
     * @return string
     * @throws \coding_exception
     */
    final function col_editbutton($values) : string {
        if (!empty($values->contexts)) {
            return '<span class="likelink" data-action="edit-context">'.get_string('editassigncontext', 'mod_certifygen').'</span>';
        }
        return '';
    }
    /**
     * Query the reader.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
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
     * @return void
     * @throws \coding_exception
     */
    public final function print_nothing_to_display() {
        global $OUTPUT;
        echo $this->render_reset_button();
        $this->print_initials_bar();
        echo $OUTPUT->heading(get_string('nothingtodisplay'), 4);
    }
}