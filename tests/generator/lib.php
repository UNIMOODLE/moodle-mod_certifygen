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
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.
use mod_certifygen\persistents\certifygen_context;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;

/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mod_certifygen_generator extends testing_module_generator {


    /**
     * @param int $type
     * @param int $mode
     * @param int $templateid
     * @param string $validation
     * @param string $report
     * @return certifygen_model
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_model(int $type, int $mode, int $templateid, string $validation, string $report) {

        $this->install_language_package('es');
        $data = [
            'name' => 'Modelo 1',
            'idnumber' => '',
            'type' => $type,
            'mode' => $mode,
            'templateid' => $templateid,
            'timeondemmand' => 0,
            'langs' => 'en,es',
            'validation' => $validation,
            'report' => $report,

        ];
        $model = new certifygen_model(0,  (object)$data);
        return $model->create();
    }

    /**
     * @param string $name
     * @param int $templateid
     * @return certifygen_model
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_model_by_name(string $name, int $templateid, int $type) {

        $this->install_language_package('es');
        $data = [
            'name' => $name,
            'idnumber' => '',
            'type' => $type,
            'mode' => certifygen_model::MODE_UNIQUE,
            'templateid' => $templateid,
            'timeondemmand' => 0,
            'langs' => 'en,es',
            'validation' => '',
            'report' => $type == certifygen_model::TYPE_ACTIVITY ? '' : 'certifygenreport_basic',

        ];
        $model = new certifygen_model(0, (object)$data);
        return $model->create();
    }

    /**
     * @param int $modelid
     * @param string $courses
     * @param int $userid
     * @return certifygen_validations
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_teacher_request(int $modelid, string $courses, int $userid) {

        $this->install_language_package('es');
        $data = [
            'name' => 'test1',
            'modelid' => $modelid,
            'status' => certifygen_validations::STATUS_NOT_STARTED,
            'lang' => 'es',
            'courses' => $courses,
            'langs' => 'en,es',
            'userid' => $userid,
            'certifygenid' => 0,
        ];
        $trequest = new certifygen_validations(0, (object)$data);
        return $trequest->create();
    }

    /**
     * @param string $langcode
     * @return void
     * @throws moodle_exception
     */
    public function install_language_package(string $langcode) {
        $controller = new \tool_langimport\controller();
        $controller->install_languagepacks($langcode);
    }

    /**
     * @param int $modelid
     * @return certifygen_context
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function assign_model_systemcontext(int $modelid) {
        $data = [
            'modelid' => $modelid,
            'contextids' => '',
            'type' => certifygen_context::CONTEXT_TYPE_SYSTEM,
        ];
        $context = new certifygen_context(0, (object)$data);
        return $context->create();
    }
    /**
     * @param int $modelid
     * @return certifygen_context
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function assign_model_coursecontext(int $modelid, string $contextids) {
        $data = [
            'modelid' => $modelid,
            'contextids' => $contextids,
            'type' => certifygen_context::CONTEXT_TYPE_COURSE,
        ];
        $context = new certifygen_context(0, (object)$data);
        return $context->create();
    }
}
