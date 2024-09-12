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
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\task;

use core\task\scheduled_task;
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_error;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_repository;
use mod_certifygen\persistents\certifygen_validations;
/**
 * checkfile
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class checkfile extends scheduled_task {
    /**
     * get_name
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('checkfiletask', 'mod_certifygen');
    }

    /**
     * Execute
     * @return void
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    public function execute() {
        global $USER;
        $validations = certifygen_validations::get_records(['status' => certifygen_validations::STATUS_VALIDATION_OK]);
        foreach ($validations as $validation) {
            try {
                $model = new certifygen_model($validation->get('modelid'));
                $validationplugin = $model->get('validation');
                $validationpluginclass = $validationplugin . '\\' . $validationplugin;
                if (get_config($validationplugin, 'enabled') === '0') {
                    continue;
                }
                /** @var ICertificateValidation $subplugin */
                $subplugin = new $validationpluginclass();
                if (!$subplugin->checkFile()) {
                    continue;
                }
                $code = certifygen_validations::get_certificate_code($validation);
                $course = 0;
                if (!empty($validation->get('certifygenid'))) {
                    $certi = new certifygen($validation->get('certifygenid'));
                    $course = $certi->get('course');
                }
                $newfile = $subplugin->get_file($course, $validation->get('id'), $code);
                if (array_key_exists('file', $newfile)) {
                    // Save on repository plugin.
                    $repositoryplugin = $model->get('repository');
                    if (get_config($repositoryplugin, 'enabled') === '1') {
                        $repositorypluginclass = $repositoryplugin . '\\' . $repositoryplugin;
                        /** @var ICertificateRepository $subplugin */
                        $subplugin = new $repositorypluginclass();
                        $response = $subplugin->save_file($newfile['file']);
                        if (!$response['haserror']) {
                            $validation->set('status', certifygen_validations::STATUS_VALIDATION_OK);
                            $validation->save();
                            $status = certifygen_validations::STATUS_FINISHED;
                            if ($subplugin->save_file_url()) {
                                $url = $subplugin->get_file_url($validation);
                                if (!empty($url)) {
                                    $data = [
                                        'validationid' => $validation->get('id'),
                                        'userid' => $validation->get('userid'),
                                        'url' => $url,
                                        'usermodified' => $validation->get('userid'),
                                    ];
                                    // Save url.
                                    $repository = new certifygen_repository(0, (object) $data);
                                    $repository->save();
                                } else {
                                    $status = certifygen_validations::STATUS_STORAGE_ERROR;
                                    $data = [
                                        'validationid' => $validation->get('id'),
                                        'status' => certifygen_validations::STATUS_STORAGE_ERROR,
                                        'code' => 'empty_repository_url',
                                        'message' => 'getFileUrl returns empty url',
                                        'usermodified' => $USER->id,
                                    ];
                                    certifygen_error::manage_certifygen_error(0, (object)$data);
                                }
                            }
                            // Save status.
                            $validation->set('status', $status);
                            $validation->save();
                        } else {
                            $validation->set('status', certifygen_validations::STATUS_STORAGE_ERROR);
                            $validation->save();
                            $data = [
                                'validationid' => $validation->get('id'),
                                'status' => certifygen_validations::STATUS_STORAGE_ERROR,
                                'code' => 'saveFile_returns_error',
                                'message' => 'saveFile returns error',
                                'usermodified' => $USER->id,
                            ];
                            certifygen_error::manage_certifygen_error(0, (object)$data);
                        }
                    } else {
                        $validation->set('status', certifygen_validations::STATUS_STORAGE_ERROR);
                        $validation->save();
                        $data = [
                            'validationid' => $validation->get('id'),
                            'status' => certifygen_validations::STATUS_STORAGE_ERROR,
                            'code' => 'validation_plugin_not_enabled',
                            'message' => 'Certificate repository plugin is not enabled',
                            'usermodified' => $USER->id,
                        ];
                        certifygen_error::manage_certifygen_error(0, (object)$data);
                    }
                } else {
                    $validation->set('status', certifygen_validations::STATUS_STORAGE_ERROR);
                    $validation->save();
                    $data = [
                        'validationid' => $validation->get('id'),
                        'status' => certifygen_validations::STATUS_STORAGE_ERROR,
                        'code' => 'getFile_not_file_parameter',
                        'message' => 'getFile does not return file parameter',
                        'usermodified' => $USER->id,
                    ];
                    certifygen_error::manage_certifygen_error(0, (object)$data);
                }
            } catch (\moodle_exception $e) {
                debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
                $validation->set('status', certifygen_validations::STATUS_STORAGE_ERROR);
                $validation->save();
                $data = [
                    'validationid' => $validation->get('id'),
                    'status' => certifygen_validations::STATUS_STORAGE_ERROR,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'usermodified' => $USER->id,
                ];
                certifygen_error::manage_certifygen_error(0, (object)$data);
                continue;
            }
        }
    }
}
