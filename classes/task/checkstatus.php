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

use core\exception\coding_exception;
use core\task\scheduled_task;
use mod_certifygen\interfaces\icertificatevalidation;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use core\exception\moodle_exception;

/**
 * There is a task, checkstatus, that is recomended to enable it when the validation subplugin used does not validate inmediately
 * the certificate.
 * This task verify the status of the certificate on the external aplication used by the validation subplugin.
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class checkstatus extends scheduled_task {
    /**
     * Name
     * @return string
     * @throws coding_exception
     */
    public function get_name(): string {
        return get_string('checkstatustask', 'mod_certifygen');
    }

    /**
     * Execute
     * @return void
     */
    public function execute() {
        $validations = certifygen_validations::get_records(['status' => certifygen_validations::STATUS_IN_PROGRESS]);
        foreach ($validations as $validation) {
            try {
                $model = new certifygen_model($validation->get('modelid'));
                $validationplugin = $model->get('validation');
                if (empty($validationplugin)) {
                    continue;
                }
                $validationpluginclass = $validationplugin . '\\' . $validationplugin;
                if (get_config($validationplugin, 'enabled') === '0') {
                    continue;
                }
                /** @var icertificatevalidation $subplugin */
                $subplugin = new $validationpluginclass();
                if (!$subplugin->check_status()) {
                    continue;
                }
                $code = certifygen_validations::get_certificate_code($validation);
                $newstatus = $subplugin->get_status($validation->get('id'), $code);
                if ($newstatus != $validation->get('status')) {
                    $validation->set('status', $newstatus);
                    $validation->save();
                }
            } catch (moodle_exception $e) {
                continue;
            }
        }
    }
}
