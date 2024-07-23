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
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;

class checkfile extends scheduled_task
{

    /**
     * @inheritDoc
     */
    public function get_name()
    {
        return get_string('checkfiletask', 'mod_certifygen');
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $validations = certifygen_validations::get_records(['status' => certifygen_validations::STATUS_VALIDATION_OK]);
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
                /** @var ICertificateValidation $subplugin */
                $subplugin = new $validationpluginclass();
                if (!$subplugin->checkFile()) {
                    continue;
                }
                $certi = new certifygen($validation->get('certifygenid'));
                global $DB;
                $code = $DB->get_field('tool_certificate_issues', 'code',
                    ['userid' => $validation->get('userid'),
                        'id' => $validation->get('issueid')]);
                $newfile = $subplugin->getFile($certi->get('course'), $validation->get('id'), 0, $code);
                if ($newfile) {
                    $status = certifygen_validations::STATUS_FINISHED;
                    $validation->set('status', $status);
                    $validation->save();
                }
            } catch (\moodle_exception $e) {
                error_log(__FUNCTION__ . ' e: '.var_export($e->getMessage(), true));
                continue;
            }
        }
    }
}