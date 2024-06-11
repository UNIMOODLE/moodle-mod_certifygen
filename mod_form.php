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
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_certifygen\forms\modelform;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 *  This class adds extra methods to form wrapper specific to be used for module add / update forms
 */
class mod_certifygen_mod_form extends moodleform_mod {

    /**
     * @inheritDoc
     */
    protected function definition()
    {
        global $OUTPUT;
        $mform =& $this->_form;
        $mform->addElement('text', 'name', get_string('name', 'mod_certifygen'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');

        $this->standard_intro_elements(get_string('introduction', 'mod_certifygen'));

        //TODO: capability.
        $canmanagemodels = true;
        $activitymodels =  mod_certifygen_get_activity_models();
        $templateoptions = ['' => get_string('chooseamodel', 'mod_certifygen')] + $activitymodels;
        $manageurl = new moodle_url('/mod/certifygen/modelmanager.php');
        $elements = [$mform->createElement('select', 'modelid', get_string('model', 'mod_certifygen'), $templateoptions)];
        $mform->setType('modelid', PARAM_INT);
        // Adding "Manage templates" link if user has capabilities to manage templates.
        if ( $canmanagemodels ) {
            $elements[] = $mform->createElement('static', 'managemodels', '',
                $OUTPUT->action_link($manageurl, get_string('modelsmanager', 'mod_certifygen')));
        }
        $mform->addGroup($elements, 'models_group', get_string('model', 'mod_certifygen'),
            html_writer::div('', 'w-100'), false);
//
        $modelid = 0;
        if (!is_null($this->get_instance())) {
            $certifygen = new certifygen($this->get_instance());
            $modelid = $certifygen->get('modelid');
            $mform->setDefault('modelid', $modelid);
        }
//        $hasissues = $this->has_issues();
//        if ($hasissues) {
//            // If coursecertificate has issues, just add the current template to the selector.
//            $templates = $this->get_current_template($modelid);
//        } else {
//            // Get all available templates for the user.
//            $templates = mod_certifygen_get_templates();
//        }

//        $modelform = new modelform();
//        $mform = $modelform->get_common_elements($mform, $hasissues, $templates);

        // Course module elements.
        $this->standard_coursemodule_elements();

//        if ($modelid) {
//            $mform = $modelform->set_default_values($modelid, $mform);
//        }
//        $mform->addElement('hidden', 'modelid', $modelid);
//        $mform->setType('modelid', PARAM_INT);
        $mform->addElement('hidden', 'type', certifygen_model::TYPE_ACTIVITY);
        $mform->setType('type', PARAM_INT);
        $this->add_action_buttons();
    }


}