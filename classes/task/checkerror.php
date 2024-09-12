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

namespace mod_certifygen\task;

use core\task\scheduled_task;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
/**
 * checkerror
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class checkerror extends scheduled_task {

    /**
     * get_name
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('checkerrortask', 'mod_certifygen');
    }

    /**
     * execute
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function execute() {
        global $DB;
        list($insql, $inparams) = $DB->get_in_or_equal(certifygen_validations::get_status_error(), SQL_PARAMS_NAMED);
        $errors = $DB->get_records_select('certifygen_validations', ' status ' . $insql, $inparams);
        if (count($errors) == 0) {
            return;
        }
        foreach ($errors as $error) {
            try {
                // All status except STATUS_STORAGE_ERROR - we can set STATUS_NOT_STARTED.
                // For STATUS_STORAGE_ERROR, try to storage again.
                $validation = new certifygen_validations($error->validationid);
                if ($error->status == certifygen_validations::STATUS_STORAGE_ERROR) {
                    $model = new certifygen_model($validation->modelid);
                } else {
                    $validation->set('status', certifygen_validations::STATUS_NOT_STARTED);
                }
            } catch (\moodle_exception $e) {

                continue;
            }
        }
    }
}
