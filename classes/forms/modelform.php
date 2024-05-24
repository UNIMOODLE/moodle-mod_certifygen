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

require_once("$CFG->dirroot/mod/certifygen/lib.php");
use context;
use context_system;
use core\output\language_menu;
use html_writer;
use mod_certifygen\persistents\certifygen_model;
use moodle_url;

use tool_certificate\permission;
use function get_string_manager;

class modelform extends \core_form\dynamic_form {

    protected function definition()
    {
        $mform =& $this->_form;
        $modelid = is_null($this->_customdata) ? 0 : $this->_customdata['id'];
        $templates = mod_certifygen_get_templates();

        // Model Name.
        $mform->addElement('text', 'modelname',
            get_string('modelname', 'mod_certifygen'), ['size' => '70']);
        $mform->setType('modelname', PARAM_RAW);
        $mform->addRule('modelname', get_string('required'), 'required');

        $mform = $this->get_common_elements($mform, false, $templates);
        $mform->addElement('hidden', 'modelid', $modelid);
        $mform->setType('modelid', PARAM_INT);
        $mform->addElement('hidden', 'type', certifygen_model::TYPE_TEACHER);
        $mform->setType('type', PARAM_INT);

    }
    public function get_common_elements(\MoodleQuickForm $mform,  bool $hasissues, $templates) {
        global $OUTPUT;


        // Model mode.
        $mform->addElement('select', 'mode',
            get_string('mode', 'mod_certifygen'), mod_certifygen_get_modes());
        $mform->setType('mode', PARAM_INT);
        $mform->addHelpButton('mode', 'mode', 'mod_certifygen');
        $mform->addRule('mode', get_string('required'), 'required');
        // End Model Form Part.

        // Templateid
        // Adding the template selector.
        $canmanagetemplates = permission::can_manage_anywhere();
        $templateoptions = ['' => get_string('chooseatemplate', 'coursecertificate')] + $templates;
        $manageurl = new \moodle_url('/admin/tool/certificate/manage_templates.php');
        $elements = [$mform->createElement('select', 'templateid', get_string('template', 'coursecertificate'), $templateoptions)];
        // Adding "Manage templates" link if user has capabilities to manage templates.
        if ($canmanagetemplates && !empty($templates)) {
            $elements[] = $mform->createElement('static', 'managetemplates', '',
                $OUTPUT->action_link($manageurl, get_string('managetemplates', 'coursecertificate')));
        }
        $mform->addGroup($elements, 'template_group', get_string('template', 'coursecertificate'),
            html_writer::div('', 'w-100'), false);
//        print_object($mform->getElement('template_group'));
//        die();
        //TODO: me da error esta regla con el debug activado
//        $mform->addRule('template_group_templateid', get_string('required'), 'required');

        if (empty($templates)) {
            // Adding warning text if there are not templates available.
            if ($canmanagetemplates) {
                $warningstr = get_string('notemplateswarningwithlink', 'coursecertificate', $manageurl->out());
            } else {
                $warningstr = get_string('notemplateswarning', 'coursecertificate');
            }
            $html = html_writer::tag('div', $warningstr, ['class' => 'alert alert-warning']);
            $mform->addElement('static', 'notemplateswarning', '', $html);
        } else {
            $warningstr = get_string('selecttemplatewarning', 'mod_coursecertificate');
            $html = html_writer::tag('div', $warningstr, ['class' => 'alert alert-warning']);
            $mform->addElement('static', 'selecttemplatewarning', '', $html);
        }
        if (!$hasissues) {
            $rules = [];
            $rules['templateid'][] = [null, 'required', null, 'client'];
            $mform->addGroupRule('template_group', $rules);
        }
        // If Certificate has issues it's not possible to change the template.
        $mform->addElement('hidden', 'hasissues', (int) $hasissues);
        $mform->setType('hasissues', PARAM_INT);
        $mform->disabledIf('templateid', 'hasissues', 'eq', 1);

        // Submitoptions.
//        $mform->addElement('select', 'submitoptions',
//            get_string('submitoptions', 'mod_certifygen'), mod_certifygen_get_submitoptions());
//        $mform->setType('submitoptions', PARAM_INT);
//        $mform->addHelpButton('submitoptions', 'submitoptions', 'mod_certifygen');


        // Timeondemmand.
        $mform->addElement('text', 'timeondemmand',
            get_string('timeondemmand', 'mod_certifygen'));
        $mform->setType('timeondemmand', PARAM_INT);
        $mform->addHelpButton('timeondemmand', 'timeondemmand', 'mod_certifygen');
        $mform->hideIf('timeondemmand', 'mode', 'eq', certifygen_model::MODE_UNIQUE);

        // Langs
        $langs = get_string_manager()->get_list_of_translations();
        $mform->addElement('select', 'langs', get_string('langs', 'mod_certifygen'), $langs);
        $mform->getElement('langs')->setMultiple(true);
        $mform->setType('langs', PARAM_INT);
        $mform->addRule('langs', get_string('required'), 'required');

        // generationtype
        $types = mod_certifygen_get_generationtype();
        if (!empty($types)) {
            $mform->addElement('select', 'generationtype', get_string('generationtype', 'mod_certifygen'), $types);
            $mform->setType('generationtype', PARAM_INT);
        }

        return $mform;
    }
    public function set_default_values( int $modelid, $mform) {
        $certifygenmodel = new certifygen_model($modelid);
        $mform->setDefault('mode', $certifygenmodel->get('mode'));
        $mform->setDefault('templateid', $certifygenmodel->get('templateid'));
        $mform->setDefault('modelname', $certifygenmodel->get('name'));
        $mform->setDefault('timeondemmand', $certifygenmodel->get('timeondemmand'));
        $mform->setDefault('langs', $certifygenmodel->get('langs'));
        return $mform;
    }

    protected function get_context_for_dynamic_submission(): context
    {
        return context_system::instance();
    }

    protected function check_access_for_dynamic_submission(): void
    {
        if (!has_capability('mod/certifygen:manage', $this->get_context_for_dynamic_submission())) {
            throw new \moodle_exception('nopermissions', 'error', '', 'manage models');
        }
    }

    public function process_dynamic_submission()
    {
        // TODO: Implement process_dynamic_submission() method.
        $formdata = $this->get_data();
        certifygen_model::save_model_object($formdata);
    }

    public function set_data_for_dynamic_submission(): void
    {
        if (!empty($this->_ajaxformdata['id'])) {
            $model = new certifygen_model($this->_ajaxformdata['id']);
            $this->set_data([
                    'modelid' => $this->_ajaxformdata['id'],
                    'mode' => $model->get('mode'),
                    'templateid' => $model->get('templateid'),
                    'modelname' => $model->get('name'),
                    'timeondemmand' => $model->get('timeondemmand'),
                    'langs' => $model->get('langs'),
            ]
            );
        }
    }

    protected function get_page_url_for_dynamic_submission(): moodle_url
    {
        return new moodle_url('/mod/certifygen/modelmanager.php');
    }
}