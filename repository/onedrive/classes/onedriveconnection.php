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
use core\oauth2\api;
use core\oauth2\client;
use core\oauth2\issuer;
use core\oauth2\rest_exception;
use dml_exception;
use dml_missing_record_exception;
use moodle_exception;
use repository_onedrive\rest;

class onedriveconnection
{
    /**
     * OAuth 2 Issuer
     * @var issuer
     */
    private $issuer = null;
    private $client = null;
    private $url = null;
    private $enabled ;
    const SCOPES = 'files.readwrite.all';

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function  __construct() {

        try {
            $this->issuer = api::get_issuer(get_config('onedrive', 'issuerid'));
            $this->enabled = true;
        } catch (dml_missing_record_exception $e) {
            $this->enabled = false;
        }

        if ($this->issuer && !$this->issuer->get('enabled')) {
            $this->enabled = false;
        }
    }

    /**
     * @return bool
     */
    public function is_enabled() : bool {
        return $this->enabled;
    }
    public function get_file_url() : string {
        // TODO.
        return '';
    }
    /**
     * Get a cached user authenticated oauth client.
     *
     * @param moodle_url $overrideurl - Use this url instead of the repo callback.
     * @return client
     */
    protected function get_user_oauth_client($overrideurl = false): ?client
    {
        if ($this->client) {
            return $this->client;
        }
        if ($overrideurl) {
            $returnurl = $overrideurl;
        } else {
            $returnurl = new moodle_url('/');
        }

        $this->client = api::get_user_oauth_client($this->issuer, $returnurl, self::SCOPES);

        return $this->client;
    }
    /**
     * @param string $onedrivepath
     * @param string $reportname
     * @param string $completefilepath
     * @param boolean $replacefile
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     * @throws rest_exception|moodle_exception
     */
    public function upload_file(string $onedrivepath, string $reportname, string $completefilepath, bool $replacefile = true) : void {

        // Get a system and a user oauth client.
        /** @var \oauth2_client $systemauth */
        $systemauth = api::get_system_oauth_client($this->issuer);
        if ($systemauth === false) {
            $details = 'Cannot connect as system user';
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }
        $userauth = $this->get_user_oauth_client();
        if ($userauth === false) {
            $details = 'Cannot connect as current user';
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }

        $systemservice = new rest($systemauth);
        $parentid = 'root';
        // Save the file in a specific onedrive folder
        $parent = get_config('certifygenrepository_onedrive', 'folder');
        $onedrivepath = $parent . '/' . $onedrivepath;
        $allfolders = explode('/', $onedrivepath);
        $fullpath = '';
        // Variable $allfolders now has the complete path we want to store the file in.
        // Create each folder in $allfolders under the system account.
        foreach ($allfolders as $foldername) {
            $fullpath .= urlencode(clean_param($foldername, PARAM_PATH));
            $folderid = $this->get_file_id_by_path($systemservice, $fullpath);
            if ($folderid !== false) {
                $parentid = $folderid;
            } else {
                // Create it.
                $parentid = $this->create_folder_in_folder($systemservice, $foldername, $parentid);
            }
            $fullpath .= '/';
        }
        $filename = urlencode(clean_param($reportname, PARAM_PATH)) . '.csv';
        $path = $fullpath . $filename;

        // Delete if it is necesary
        if ($replacefile) {
            $fileid = $this->get_file_id_by_path($systemservice, $path);
            if ($fileid) {
                $this->delete_file_by_path($systemservice, $path);
            }
        }
        // Upload the file.
        $safefilename = clean_param($filename, PARAM_PATH);
        $mimetype = $this->get_mimetype_from_filename($safefilename);
        // We cannot send authorization headers in the upload or personal microsoft accounts will fail (what a joke!).
        $curl = new curl();
        $this->upload_file_to_onedrive($systemservice, $curl, $systemauth, $completefilepath, $mimetype, $parentid, $safefilename);

        // Read with link.
        $this->link = $this->set_file_sharing_anyone_with_link_can_read($systemservice, $fileid);
    }

    /**
     * @return string
     */
    public function get_link() : string {
        return $this->url;
    }

    /**
     * Allow anyone with the link to read the file.
     * @param rest $client
     * @param $fileid
     * @return mixed
     * @throws coding_exception
     * @throws moodle_exception
     * @throws rest_exception
     */
    protected function set_file_sharing_anyone_with_link_can_read(rest $client, $fileid) {

        $type = (isset($this->options['embed']) && $this->options['embed'] == true) ? 'embed' : 'view';
        $updateread = [
            'type' => $type,
            'scope' => 'anonymous'
        ];
        $params = ['fileid' => $fileid];
        $response = $client->call('create_link', $params, json_encode($updateread));
        if (empty($response->link)) {
            $details = 'Cannot update link sharing for the document: ' . $fileid;
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }
        return $response->link->webUrl;
    }
    /**
     * Upload a file to onedrive.
     * @param rest $service
     * @param curl $curl
     * @param curl $authcurl
     * @param string $filepath
     * @param string $mimetype
     * @param string $parentid
     * @param string $filename
     * @return void
     * @throws coding_exception
     * @throws moodle_exception
     * @throws rest_exception
     */
    protected function upload_file_to_onedrive(rest $service, curl $curl, curl $authcurl,
                                               string $filepath, string $mimetype, string $parentid, string $filename) : void {
        // Start an upload session.
        // Docs https://developer.microsoft.com/en-us/graph/docs/api-reference/v1.0/api/item_createuploadsession link.
        $params = ['parentid' => $parentid, 'filename' => urlencode($filename)];
        $behaviour = [ 'item' => [ "@microsoft.graph.conflictBehavior" => "rename" ] ];
        $created = $service->call('create_upload', $params, json_encode($behaviour));
        if (empty($created->uploadUrl)) {
            $details = 'Cannot begin upload session:' . $parentid;
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }
        $options = ['file' => $filepath];

        // Try each curl class in turn until we succeed.
        // First attempt an upload with no auth headers (will work for personal onedrive accounts).
        // If that fails, try an upload with the auth headers (will work for work onedrive accounts).
        $curls = [$curl, $authcurl];
        $response = null;
        foreach ($curls as $curlinstance) {
            $curlinstance->setHeader('Content-type: ' . $mimetype);
            $size = filesize($filepath);
            if (!$size) {
                continue;
            }
            $curlinstance->setHeader('Content-Range: bytes 0-' . ($size - 1) . '/' . $size);
            $response = $curlinstance->put($created->uploadUrl, $options);
            if ($curlinstance->errno == 0 && !is_null($response)) {
                $response = json_decode($response);
            }
            if (!empty($response->id)) {
                // We can stop now - there is a valid file returned.
                break;
            }
        }

        // Delete filepath.
        unlink($filepath);
        if (empty($response->id)) {
            $details = 'File not created';
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }
    }
    /**
     * Given a filename, use the core_filetypes registered types to guess a mimetype.
     *
     * If no mimetype is known, return 'application/unknown';
     *
     * @param string $filename
     * @return string $mimetype
     */
    protected function get_mimetype_from_filename($filename) {
        $mimetype = 'application/unknown';
        $types = core_filetypes::get_types();
        $fileextension = '.bin';
        if (strpos($filename, '.') !== false) {
            $fileextension = substr($filename, strrpos($filename, '.') + 1);
        }

        if (isset($types[$fileextension])) {
            $mimetype = $types[$fileextension]['type'];
        }
        return $mimetype;
    }

    /**
     * See if a folder exists within a folder
     *
     * @param rest $client Authenticated client.
     * @param string $fullpath
     * @return string|boolean The file id if it exists or false.
     * @throws coding_exception
     */
    protected function get_file_id_by_path(rest $client, $fullpath) {
        $fields = "id";
        try {
            $response = $client->call('get_file_by_path', ['fullpath' => $fullpath, '$select' => $fields]);
        } catch (rest_exception $re) {
            error_log(__FUNCTION__ . ' ERROR: '. $re->getMessage());
            return false;
        }
        return $response->id;
    }

    /**
     * @param rest $client
     * @param $fullpath
     * @return false|void
     * @throws coding_exception
     */
    protected function delete_file_by_path(rest $client, $fullpath) {

        try {
//            $fields = "id";
//            $client->call('delete_file_by_path', ['fullpath' => $fullpath, '$select' => $fields]);
            $client->call('delete_file_by_path', ['fullpath' => $fullpath]);
        } catch (rest_exception $re) {
            error_log(__FUNCTION__ . ' ERROR: '. $re->getMessage());
            return false;
        }
    }

    /**
     * Create a folder within a folder
     *
     * @param rest $client Authenticated client.
     * @param string $foldername The folder we are creating.
     * @param string $parentid The parent folder we are creating in.
     *
     * @return string The file id of the new folder.
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function create_folder_in_folder(rest $client, $foldername, $parentid) {
        $params = ['parentid' => $parentid];
        $folder = [ 'name' => $foldername, 'folder' => [ 'childCount' => 0 ]];
        try {
            $created = $client->call('create_folder', $params, json_encode($folder));
        } catch (rest_exception $re) {
            error_log(__FUNCTION__ . ' ERROR: '. $re->getMessage());
        }

        if (empty($created->id)) {
            $details = 'Cannot create folder:' . $foldername;
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }
        return $created->id;
    }
}