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
 * Checkerror task
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\task;

use coding_exception;
use core\task\scheduled_task;
use dml_exception;
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;

/**
 * There is a task, checkerror. It is responsible for searching for error states in the validation processes and returning them
 * to the not started state, so that the user can start the process again.
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class checkerror extends scheduled_task {
    /**
     * get_name
     * @return string
     * @throws coding_exception
     */
    public function get_name(): string {
        return get_string('checkerrortask', 'mod_certifygen');
    }

    /**
     * execute
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function execute() {
        global $DB;
        [$insql, $inparams] = $DB->get_in_or_equal(certifygen_validations::get_status_error(), SQL_PARAMS_NAMED);
        $errors = $DB->get_records_select('certifygen_validations', ' status ' . $insql, $inparams);
        if (count($errors) == 0) {
            return;
        }
        foreach ($errors as $error) {
            try {
                // All status except STATUS_STORAGE_ERROR - we can set STATUS_NOT_STARTED.
                // For STATUS_STORAGE_ERROR, try to storage again.
                $validation = new certifygen_validations($error->id);
                if ((int)$error->status === certifygen_validations::STATUS_STORAGE_ERROR) {
                    $model = new certifygen_model($validation->get('modelid'));
                    // Validation plugin.
                    $validationplugin = $model->get('validation');
                    $validationpluginclass = $validationplugin . '\\' . $validationplugin;
                    if (get_config($validationplugin, 'enabled') === '1') {
                        /** @var ICertificateValidation $subplugin */
                        $subplugin = new $validationpluginclass();
                        $courseid = 0;
                        if ($model->get('type') == certifygen_model::TYPE_ACTIVITY) {
                            $certifygen = new certifygen($error->certifygenid);
                            $courseid = $certifygen->get('course');
                        }
                        $response = $subplugin->get_file($courseid, $error->id);
                        if (!array_key_exists('file', $response)) {
                            $validation->set('status', certifygen_validations::STATUS_NOT_STARTED);
                            $validation->save();
                            continue;
                        }
                        $repositoryplugin = $model->get('repository');
                        if (get_config($validationplugin, 'enabled') === '1') {
                            $repositorypluginclass = $repositoryplugin . '\\' . $repositoryplugin;
                            /** @var ICertificateRepository $subplugin */
                            $subplugin = new $repositorypluginclass();
                            $response = $subplugin->save_file($response['file']);
                            if (!$response['haserror']) {
                                $validation->set('status', certifygen_validations::STATUS_FINISHED);
                                $validation->save();
                                // Send notification.
                                \mod_certifygen\certifygen::send_notification($validation);
                            } else {
                                $validation->set('status', certifygen_validations::STATUS_NOT_STARTED);
                                $validation->save();
                            }
                        }
                    } else {
                        continue;
                    }
                } else {
                    $validation->set('status', certifygen_validations::STATUS_NOT_STARTED);
                    $validation->save();
                }
            } catch (moodle_exception $e) {
                debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
                continue;
            }
        }
    }
}
