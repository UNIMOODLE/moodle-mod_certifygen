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
namespace mod_certifygen\forms;

use coding_exception;
use moodle_url;
use moodleform;
/**
 * Error filters form
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class errorfiltersform extends moodleform {
    /**
     * Definition
     * @throws coding_exception
     */
    protected function definition() {
        $mform =& $this->_form;
        $customdata = $this->_customdata;

        // User fullname.
        $mform->addElement('text', 'userfullname', get_string('fullnameuser'));
        $mform->setType('userfullname', PARAM_RAW);
        $mform->setDefault('userfullname', $customdata['userfullname']);

        // Model name.
        $mform->addElement('text', 'modelname', get_string('model', 'mod_certifygen'));
        $mform->setType('modelname', PARAM_RAW);
        $mform->setDefault('userfmodelnameullname', $customdata['modelname']);

        $this->add_action_buttons(false, get_string('filter', 'mod_certifygen'));
        $html = '<div class="form-group row fitem femptylabel">';
        $html .= '<div class="col-md-3 col-form-label d-flex pb-0 pr-md-0">';
        $html .= '<div class="form-label-addon d-flex align-items-center align-self-start">';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="col-md-9 form-inline align-items-start felement">';
        $url = new moodle_url('/mod/certifygen/showerrors.php');
        $html .= '<a href="' . $url->out() . '" class="btn btn-secondary form-control " >'
            . get_string('removefilters', 'mod_certifygen') . '</a> ';
        $html .= '</div>';
        $html .= '</div>';
        $mform->addElement('html', $html);
    }
}
