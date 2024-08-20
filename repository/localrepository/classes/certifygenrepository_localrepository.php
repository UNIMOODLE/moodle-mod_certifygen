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
/**
 * @package   certifygenrepository_localrepository
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenrepository_localrepository;

use context_system;
use mod_certifygen\certifygen;
use mod_certifygen\interfaces\ICertificateReport;
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use mod_certifygen\persistents\certifygen as certifygenpersistent;
use moodle_url;
use stored_file;

class certifygenrepository_localrepository implements ICertificateRepository
{
    /**
     * @param certifygen_validations $validation
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function getFileUrl(certifygen_validations $validation): string
    {
        if (!empty($validation->get('certifygenid'))) {
            $certifygen = new certifygenpersistent($validation->get('certifygenid'));
            $model = new certifygen_model($validation->get('modelid'));
            $existingcertificate = certifygen::get_user_certificate($validation->get('certifygenid'),
                $validation->get('userid'), $certifygen->get('course'), $model->get('templateid'),
                $validation->get('lang'));
            $code = $existingcertificate->code . '.pdf';
            $itemid = (int) $validation->get('issueid');
        } else {
            $code = $validation->get('code') . '.pdf';
            $itemid = (int) $validation->get('id');
        }

        $fs = get_file_storage();
        $contextid = context_system::instance()->id;
        $file = $fs->get_file($contextid, ICertificateReport::FILE_COMPONENT,
            ICertificateRepository::FILE_AREA, $itemid,  ICertificateRepository::FILE_PATH, $code);
        if (empty($file)) {
            return '';
        }
        $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
            $file->get_itemid(), $file->get_filepath(), $file->get_filename())->out();
        return $url;
    }

    /**
     * @param stored_file $file
     * @return array
     */
    public function saveFile(stored_file $file): array
    {
        $result = [
            'result' => true,
            'message' => 'ok',
        ];

        try {
            $fs = get_file_storage();
            $filerecord = [
                'contextid' => $file->get_contextid(),
                'component' => self::FILE_COMPONENT,
                'filearea' => self::FILE_AREA,
                'itemid' => $file->get_itemid(),
                'filepath' => self::FILE_PATH,
                'filename' => $file->get_filename()
            ];
            $fs->create_file_from_storedfile($filerecord, $file);
            $file->delete();
        } catch (\moodle_exception $e) {
            $result['result'] = false;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }

    public function is_enabled(): bool
    {
        $enabled = (int) get_config('certifygenrepository_localrepository', 'enabled');
        if ($enabled) {
            return true;
        }
        return false;
    }
}