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
global $CFG;
require_once("$CFG->dirroot/mod/certifygen/lib.php");

use coding_exception;
use context;
use context_system;
use core\invalid_persistent_exception;
use core_form\dynamic_form;
use dml_exception;
use html_writer;
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use moodle_exception;
use moodle_url;
use tool_certificate\certificate;
use MoodleQuickForm;
use tool_certificate\permission;
use function get_string_manager;

class modelform extends dynamic_form {

    /**
     * @throws coding_exception|dml_exception
     */
    protected function definition()
    {
        global $OUTPUT;
        $mform =& $this->_form;
        $modelid = is_null($this->_ajaxformdata) ? 0 : (int)$this->_ajaxformdata['id'];
        if ($modelid) {
            $model = new certifygen_model($modelid);
        }
        // Model Name.
        $mform->addElement('text', 'modelname',
            get_string('modelname', 'mod_certifygen'), ['size' => '70']);
        $mform->setType('modelname', PARAM_RAW);
        $mform->addRule('modelname', get_string('required'), 'required');

        // Model idnumber.
        $mform->addElement('text', 'modelidnumber',
            get_string('modelidnumber', 'mod_certifygen'), ['size' => '70']);
        $mform->setType('modelidnumber', PARAM_RAW);

        // Model mode.
        $mform->addElement('select', 'mode',
            get_string('mode', 'mod_certifygen'), mod_certifygen_get_modes());
        $mform->setType('mode', PARAM_INT);
        $mform->addHelpButton('mode', 'mode', 'mod_certifygen');
        $mform->addRule('mode', get_string('required'), 'required');

        // Timeondemmand.
        $mform->addElement('duration', 'timeondemmand',
            get_string('timeondemmand', 'mod_certifygen'));
        $mform->setType('timeondemmand', PARAM_INT);
        $mform->addHelpButton('timeondemmand', 'timeondemmand', 'mod_certifygen');
        $mform->hideIf('timeondemmand', 'mode', 'eq', certifygen_model::MODE_UNIQUE);
        $mform->setDefault('timeondemmand', 30);

        // Model type.
        $cemited = mod_certifygen_are_there_any_certificate_emited($modelid);
        if (!$cemited) {
            $mform->addElement('select', 'type',
                get_string('type', 'mod_certifygen'), mod_certifygen_get_types());
            $mform->setType('type', PARAM_INT);
            $mform->addHelpButton('type', 'type', 'mod_certifygen');
            $mform->addRule('type', get_string('required'), 'required');
        } else {
            $typestring = get_string('type_' . $model->get('type'), 'mod_certifygen');
            $html = get_string('type', 'mod_certifygen') . ' : ' . $typestring;
            $html = html_writer::div($html, 'w-100');
            $mform->addElement('html', $html);
        }

        // End Model Form Part.

        // Templateid
        $templates = mod_certifygen_get_templates();
        if (!$cemited) {
            $canmanagetemplates = permission::can_manage_anywhere();
            $templateoptions = ['' => get_string('chooseatemplate', 'mod_certifygen')] + $templates;
            $manageurl = new moodle_url('/admin/tool/certificate/manage_templates.php');
            $elements = [$mform->createElement('select', 'templateid', get_string('template', 'mod_certifygen'), $templateoptions)];
            $mform->setType('templateid', PARAM_INT);

            // Adding "Manage templates" link if user has capabilities to manage templates.
            if ($canmanagetemplates && !empty($templates)) {
                $elements[] = $mform->createElement('static', 'managetemplates', '',
                    $OUTPUT->action_link($manageurl, get_string('managetemplates', 'mod_certifygen')));
            }
            $mform->addGroup($elements, 'template_group', get_string('template', 'mod_certifygen'),
                html_writer::div('', 'w-100'), false);
            $mform->hideIf('template_group', 'type', 'noteq', certifygen_model::TYPE_ACTIVITY);

        } else if ($model->get('templateid')) {
            $html = get_string('template', 'mod_certifygen') . ' : ' . $templates[(int)$model->get('templateid')];
            $html = html_writer::div($html, 'w-100');
            $mform->addElement('html', $html);
        }

        // Langs
        $langs = get_string_manager()->get_list_of_translations();
        if (!$cemited) {
            $mform->addElement('select', 'langs', get_string('langs', 'mod_certifygen'), $langs);
            $mform->getElement('langs')->setMultiple(true);
            //$mform->setType('langs', PARAM_RAW);
            $mform->addRule('langs', get_string('required'), 'required');
        } else {
            $langstrings = $model->get('langs');
            $langstrings = explode(',', $langstrings);
            $typestring = '';
            foreach ($langstrings as $langstring) {
                $typestring .= $langs[$langstring];
            }
            $html = get_string('validation', 'mod_certifygen') . ' : ' . $typestring;
            $html = html_writer::div($html, 'w-100');
            $mform->addElement('html', $html);
        }

        // Validation.
        $types = mod_certifygen_get_validation();
        if (!$cemited) {
            $mform->addElement('select', 'validation', get_string('validation', 'mod_certifygen'), $types);
            $mform->setType('validation', PARAM_RAW);
            $mform->addRule('validation', get_string('required'), 'required');
        } else {
            $typestring = $types[$model->get('validation')];
            $html = get_string('validation', 'mod_certifygen') . ' : ' . $typestring;
            $html = html_writer::div($html, 'w-100');
            $mform->addElement('html', $html);
        }
        // Report (only for teacher model type).
        $types = mod_certifygen_get_report();
        if (!$cemited) {
            $mform->addElement('select', 'report', get_string('report', 'mod_certifygen'), $types);
            $mform->setType('report', PARAM_RAW);
            $mform->hideIf('report', 'type', 'eq', certifygen_model::TYPE_ACTIVITY);
        } else {
            $typestring = $types[$model->get('report')];
            $html = get_string('report', 'mod_certifygen') . ' : ' . $typestring;
            $html = html_writer::div($html, 'w-100');
            $mform->addElement('html', $html);
        }

        // Repository
        $types = mod_certifygen_get_repositories();

        if (!$cemited) {
            $mform->addElement('select', 'repository', get_string('repository', 'mod_certifygen'), $types);
            $mform->setType('repository', PARAM_RAW);
            $mform->addRule('repository', get_string('required'), 'required');
        } else {
            $typestring = $types[$model->get('repository')];
            $html = get_string('repository', 'mod_certifygen') . ' : ' . $typestring;
            $html = html_writer::div($html, 'w-100');
            $mform->addElement('html', $html);
        }

        // Hidden elements.
        $mform->addElement('hidden', 'modelid', 0);
        $mform->setType('modelid', PARAM_INT);
    }

    /**
     * @throws dml_exception
     */
    protected function get_context_for_dynamic_submission(): context
    {
        return context_system::instance();
    }

    /**
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    protected function check_access_for_dynamic_submission(): void
    {
        if (!has_capability('mod/certifygen:manage', $this->get_context_for_dynamic_submission())) {
            throw new moodle_exception('nopermissions', 'error', '', 'manage models');
        }
    }

    /**
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
    public function process_dynamic_submission()
    {
        $formdata = $this->get_data();
        certifygen_model::save_model_object($formdata);
    }

    /**
     * @throws coding_exception
     */
    public function set_data_for_dynamic_submission(): void
    {
        if (!empty($this->_ajaxformdata['id'])) {
            $model = new certifygen_model($this->_ajaxformdata['id']);
            $this->set_data([
                    'modelid' => $this->_ajaxformdata['id'],
                    'modelidnumber' => $model->get('idnumber'),
                    'mode' => $model->get('mode'),
                    'type' => $model->get('type'),
                    'templateid' => $model->get('templateid'),
                    'report' => $model->get('report'),
                    'repository' => $model->get('repository'),
                    'modelname' => $model->get('name'),
                    'timeondemmand' => $model->get('timeondemmand'),
                    'langs' => $model->get('langs'),
                    'validation' => $model->get('validation'),
            ]
            );
        }
    }

    /**
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url
    {
        return new moodle_url('/mod/certifygen/modelmanager.php');
    }
    function validation($data, $files) {
        $errors = [];

        if (!array_key_exists('langs', $data)
        || (array_key_exists('langs', $data) && empty($data['langs']))) {
            $errors['langs'] = get_string('required');
        }
        // Confirm validation-repository plugins selected
        $repositoryplugin = $data['repository'];
        $repositorypluginclass = $repositoryplugin . '\\' . $repositoryplugin;
        /** @var ICertificateRepository $subplugin */
        $subplugin = new $repositorypluginclass();
        $validplugins = $subplugin->get_consistent_validation_plugins();
        if (!empty($validplugins) && !in_array($data['validation'], $validplugins)) {
            $a = new \stdClass();
            $a->validation = get_string('pluginname', $data['validation']);
            $a->repository = get_string('pluginname', $repositoryplugin);
            $errors['repository'] = get_string('repositorynotvalidwithvalidationplugin', 'mod_certifygen', $a);
        }
        return $errors;
    }
}