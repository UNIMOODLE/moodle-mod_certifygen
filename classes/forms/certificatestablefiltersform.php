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

namespace mod_certifygen\forms;

use coding_exception;
use moodleform;

class certificatestablefiltersform extends moodleform
{

    /**
     * @inheritDoc
     * @throws coding_exception
     */
    protected function definition()
    {
        $mform =& $this->_form;

        // Langs.
        $choices = array();
        $langs = $this->_customdata['langs'];
        $defaultlang = $this->_customdata['defaultlang'];
        $langstrings = get_string_manager()->get_list_of_translations();
        if (!empty($langs)) {
            foreach ($langs as $lang) {
                $choices[$lang] = $langstrings[$lang];
            }
        }
        $mform->addElement('select', 'lang', get_string('chooselang', 'mod_certifygen'), $choices);
        $mform->setType('lang', PARAM_RAW);
        $mform->setDefault('lang', $defaultlang);
        $this->add_action_buttons(false, get_string('filter', 'mod_certifygen'));
    }
}