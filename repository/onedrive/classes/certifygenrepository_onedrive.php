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
 * @package   certifygenrepository_onedrive
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenrepository_onedrive;

use coding_exception;
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\persistents\certifygen_repository;
use mod_certifygen\persistents\certifygen_validations;
use stored_file;

class certifygenrepository_onedrive implements ICertificateRepository
{
    private $url = '';
    /**
     * @param certifygen_validations $validation
     * @return string
     * @throws coding_exception
     */
    public function getFileUrl(certifygen_validations $validation): string
    {
        if (empty($this->url)) {
            $certrepository = certifygen_repository::get_record(
                ['validationid' => $validation->get('id'), 'userid' => $validation->get('userid')]);
            if ($certrepository) {
                return $certrepository->get('url');
            }
            return '';
        }
        return $this->url;
        // TODO igual es mejor no tener que guardar la url en db ...:
//        $code = certifygen_validations::get_certificate_code($validation);
//        if (empty($this->url)) {
//            global $CFG, $USER;
//            $result = [
//                'result' => true,
//                'haserror' => false,
//                'message' => 'ok',
//            ];
//
//            try {
//                $connection = new onedriveconnection();
//                $this->url = $connection->get_file_url();
//            } catch (\moodle_exception $e) {
//                $result['result'] = false;
//                $result['haserror'] = true;
//                $result['message'] = $e->getMessage();
//            }
//            return $result;
//        }
//        return $this->url;
    }

    /**
     * @param stored_file $file
     * @return array
     */
    public function saveFile(stored_file $file): array
    {
        global $CFG, $USER;
        $result = [
            'result' => true,
            'haserror' => false,
            'message' => 'ok',
        ];

        try {
            $connection = new onedriveconnection();
            $onedrivepath = 'root';
            $reportname = $file->get_filename();
            // It is needed to have the file in a known path to upload it to onedrive.
            $completefilepath = $CFG->dirroot . '/mod/certifygen/repository/onedrive/tempfiles/' . $reportname . '.pdf';
            $file->copy_content_to($completefilepath);
            // Upload file to onedrive.
            $connection->upload_file($onedrivepath, $reportname, $completefilepath);
            $this->url = $connection->get_link();
            // Delete temp file.
            unlink($completefilepath);
            //$file->delete(); //TODO: uncomment.
        } catch (\moodle_exception $e) {
            $result['result'] = false;
            $result['haserror'] = true;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @return bool
     * @throws \dml_exception
     */
    public function is_enabled(): bool
    {
//        $enabled = (int) get_config('certifygenrepository_onedrive', 'enabled');
//        $connection = new onedriveconnection();
//        if ($enabled && $connection->is_enabled()) {
//            return true;
//        }
        return false;
    }

    /**
     * @return bool
     */
    public function saveFileUrl(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function get_consistent_validation_plugins(): array
    {
        return [];
    }
}