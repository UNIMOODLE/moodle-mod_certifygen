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
use core\oauth2\rest_exception;
use dml_exception;
use mod_certifygen\interfaces\icertificaterepository;
use mod_certifygen\persistents\certifygen_repository;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use core\url;
use stored_file;
/**
 * certifygenrepository_onedrive
 * @package   certifygenrepository_onedrive
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenrepository_onedrive implements icertificaterepository {
    /** @var string $url */
    private string $url = '';
    /**
     * getFileUrl
     * @param certifygen_validations $validation
     * @return string
     * @throws coding_exception
     */
    public function get_file_url(certifygen_validations $validation): string {
        if (empty($this->url)) {
            $certrepository = certifygen_repository::get_record(
                ['validationid' => $validation->get('id'), 'userid' => $validation->get('userid')]
            );
            if ($certrepository) {
                return $certrepository->get('url');
            }
            return '';
        }
        return $this->url;
    }

    /**
     * saveFile
     * @param stored_file $file
     * @return array
     * @throws rest_exception
     */
    public function save_file(stored_file $file): array {
        global $CFG, $USER;
        $result = [
            'result' => true,
            'haserror' => false,
            'message' => get_string('ok', 'mod_certifygen'),
        ];

        try {
            $connection = new onedriveconnection();
            $onedrivepath = 'root';
            $reportname = $file->get_filename();
            // It is needed to have the file in a known path to upload it to onedrive.
            $tempdir = make_temp_directory('certifygen');
            $tempfile = tempnam($tempdir, 'certifygen');
            $file->copy_content_to($tempfile);
            // Upload file to onedrive.
            $id = $connection->upload_file($onedrivepath, $reportname, $tempfile);
            $odata = ['id' => $id];
            $this->url = $connection->get_link();
            // Save on db.
            $validation = new certifygen_validations($file->get_itemid());
            $data = [
                    'validationid' => $file->get_itemid(),
                    'userid' => $validation->get('userid'),
                    'usermodified' => $USER->id,
                    'url' => $this->url,
                    'data' => json_encode($odata),
            ];
            $userfile = new certifygen_repository(0, (object)$data);
            $userfile->create();
            $file->delete();
        } catch (moodle_exception $e) {
            $result['result'] = false;
            $result['haserror'] = true;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }

    /**
     * is_enabled
     * @return bool
     * @throws dml_exception
     */
    public function is_enabled(): bool {
        $enabled = (int) get_config('certifygenrepository_onedrive', 'enabled');
        $connection = new onedriveconnection();
        if ($enabled && $connection->is_enabled()) {
            return true;
        }
        return false;
    }

    /**
     * save_file_url
     * @return bool
     */
    public function save_file_url(): bool {
        return true;
    }

    /**
     * get_consistent_validation_plugins
     * @return array
     */
    public function get_consistent_validation_plugins(): array {
        return [];
    }

    /**
     * Return file content (called by ws)
     * @param certifygen_validations $trequest
     * @return string
     */
    public function get_file_content(certifygen_validations $trequest): string {

        $result = '';
        try {
            $connection = new onedriveconnection();
            $code = certifygen_validations::get_certificate_code($trequest);
            $reportname = $code . '.pdf';
            // Upload file to onedrive.
            $usercert = certifygen_repository::get_record([
                    'validationid' => $trequest->get('id'),
                    'userid' => $trequest->get('userid'),
                    ]);
            $odata = $usercert->get('data');
            $odata = json_decode($odata);
            $id = $odata->id;
            $result = $connection->get_file($id, $reportname);
        } catch (moodle_exception $e) {
            debugging(__FUNCTION__ . ' ERROR: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Get file by code
     * Search for files named by $code
     *
     * @param string $code
     * @return string
     */
    public function get_file_by_code(string $code): string {
        $result = '';
        try {
            // First search on db.
            $result = $this->get_file_by_code_on_moodle_db($code);
            if (empty($result)) {
                // If there is no result, find on onedrive repository.
                $result = $this->get_file_by_code_on_onedrive($code);
            }
        } catch (moodle_exception $e) {
            debugging(__FUNCTION__ . ' ERROR: ' . $e->getMessage());
        }

        return $result;
    }
    /**
     * Get file by code on onedrive repository
     * Search for files named by $code
     *
     * @param string $code
     * @return string
     */
    protected function get_file_by_code_on_onedrive(string $code): string {

        $output = '';
        $connection = new onedriveconnection();
        $result = $connection->search($code);
        if (!array_key_exists('list', $result)) {
            return $output;
        } else if (empty($result['list'])) {
            return $output;
        } else if (array_key_exists('source', $result['list'][0])) {
            $fdata = $result['list'][0]['source'];
            $fdata = json_decode($fdata);
            if (isset($fdata->link)) {
                $output = $fdata->link;
            }
        }

        return $output;
    }

    /**
     * Get file by code on moodle database
     * Search for files named by $code
     *
     * @param string $code
     * @return string
     * @throws dml_exception
     */
    protected function get_file_by_code_on_moodle_db(string $code): string {
        global $DB;

        $url = '';
        $comparecomp = $DB->sql_compare_text('component');
        $comparecompplaceholder = $DB->sql_compare_text(':component');
        $comparefarea = $DB->sql_compare_text('filearea');
        $comparefareaplaceholder = $DB->sql_compare_text(':filearea');
        $comparefname = $DB->sql_compare_text('filename');
        $comparefnameplaceholder = $DB->sql_compare_text(':filename');
        $params = [
                'component' => self::FILE_COMPONENT,
                'filearea' => self::FILE_AREA,
                'filename' => $code . '.pdf',
        ];
        $sql = "SELECT *
                  FROM {files}
                 WHERE {$comparecomp} = {$comparecompplaceholder}
                        AND {$comparefarea} = {$comparefareaplaceholder}
                        AND {$comparefname} = {$comparefnameplaceholder}";
        $result = $DB->get_record_sql($sql, $params);
        if ($result) {
            $url = url::make_pluginfile_url(
                $result->contextid,
                $result->component,
                $result->filearea,
                $result->itemid,
                $result->filepath,
                $result->filename
            )->out();
        }

        return $url;
    }
}
