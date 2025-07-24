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
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 *
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
use core_collator;
use core_filetypes;
use curl;
use dml_exception;
use dml_missing_record_exception;
use moodle_exception;
use core\url;
use oauth2_client;
use repository_onedrive\rest;
/**
 *
 * @package   certifygenrepository_onedrive
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class onedriveconnection {
    /**
     * OAuth 2 Issuer
     * @var issuer
     */
    private $issuer = null;
    /** @var null $client */
    private $client = null;
    /** @var null $url */
    private $url = null;
    /** @var bool $enabled */
    private $enabled;
    /** @var string SCOPES */
    const SCOPES = 'files.readwrite.all';

    /**
     * Construct
     * @throws coding_exception
     * @throws dml_exception
     */
    public function __construct() {

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
     * is_enabled
     * @return bool
     */
    public function is_enabled(): bool {
        return $this->enabled;
    }
    /**
     * Get a cached user authenticated oauth client.
     *
     * @param url $overrideurl - Use this url instead of the repo callback.
     * @return client
     */
    protected function get_user_oauth_client($overrideurl = false): ?client {
        if ($this->client) {
            return $this->client;
        }
        if ($overrideurl) {
            $returnurl = $overrideurl;
        } else {
            $returnurl = new url('/');
        }

        $this->client = api::get_user_oauth_client($this->issuer, $returnurl, self::SCOPES);

        return $this->client;
    }
    /**
     * upload_file
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
    public function upload_file(
        string $onedrivepath,
        string $reportname,
        string $completefilepath,
        bool $replacefile = true
    ): string {

        // Get a system and a user oauth client.
        /** @var oauth2_client $systemauth */
        $systemauth = api::get_system_oauth_client($this->issuer);
        if ($systemauth === false) {
            $details = get_string('cannot_connect_as_system_user', 'mod_certifygen');
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }
        $userauth = $this->get_user_oauth_client();
        if ($userauth === false) {
            $details = get_string('cannot_connect_as_current_user', 'mod_certifygen');
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }

        $systemservice = new rest($systemauth);
        $parentid = 'root';
        // Save the file in a specific onedrive folder.
        $parent = get_config('certifygenrepository_onedrive', 'folder');
        $onedrivepath = $onedrivepath . '/' . $parent;
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
        $filename = urlencode(clean_param($reportname, PARAM_PATH));
        $path = $fullpath . $filename;
        // Delete if it is necesary.
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
        $fileid = $this->upload_file_to_onedrive(
            $systemservice,
            $curl,
            $systemauth,
            $completefilepath,
            $mimetype,
            $parentid,
            $safefilename
        );
        // Read with link.
        $this->link = $this->set_file_sharing_anyone_with_link_can_read($systemservice, $fileid);

        return $fileid;
    }

    /**
     * get_link
     * @return string
     */
    public function get_link(): string {
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
            'scope' => 'anonymous',
        ];
        $params = ['fileid' => $fileid];
        $response = $client->call('create_link', $params, json_encode($updateread));
        if (empty($response->link)) {
            $details = get_string('cannot_update_link_sharing_for_document', 'mod_certifygen') . $fileid;
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }
        $this->url = $response->link->webUrl;
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
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     * @throws rest_exception
     */
    protected function upload_file_to_onedrive(
        rest $service,
        curl $curl,
        curl $authcurl,
        string $filepath,
        string $mimetype,
        string $parentid,
        string $filename
    ): string {

        // Start an upload session.
        // Docs https://developer.microsoft.com/en-us/graph/docs/api-reference/v1.0/api/item_createuploadsession link.
        $params = ['parentid' => $parentid, 'filename' => urlencode($filename)];
        $behaviour = [ 'item' => [ "@microsoft.graph.conflictBehavior" => "rename" ] ];
        $created = $service->call('create_upload', $params, json_encode($behaviour));
        if (empty($created->uploadUrl)) {
            $details = get_string('cannot_begin_upload_session', 'mod_certifygen') . $parentid;
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }
        $options = ['file' => $filepath];
        // Try each curl class in turn until we succeed.
        // First attempt an upload with no auth headers (will work for personal onedrive accounts).
        // If that fails, try an upload with the auth headers (will work for work onedrive accounts).
        $curls = [$curl, $authcurl];
        $id = '';
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
                $id = $response->id;
                // We can stop now - there is a valid file returned.
                break;
            }
        }
        if (empty($id)) {
            $details = 'File not created';
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }

        return $id;
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
            return false;
        }
        return $response->id;
    }

    /**
     * delete_file_by_path
     * @param rest $client
     * @param $fullpath
     * @return false|void
     * @throws coding_exception
     */
    protected function delete_file_by_path(rest $client, $fullpath) {

        try {
            $client->call('delete_file_by_path', ['fullpath' => $fullpath]);
        } catch (rest_exception $re) {
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
        $created = $client->call('create_folder', $params, json_encode($folder));
        if (empty($created->id)) {
            $details = get_string('cannot_create_folder', 'mod_certifygen') . $foldername;
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $details);
        }
        return $created->id;
    }

    /**
     * Get a file.
     * @param $id
     * @param $filename
     * @return bool|string
     * @throws moodle_exception
     */
    public function get_file($id, $filename = '') {

        $client = api::get_system_oauth_client($this->issuer);
        $base = 'https://graph.microsoft.com/v1.0/';

        $sourceurl = new url($base . 'me/drive/items/' . $id . '/content');
        $source = $sourceurl->out(false);

        // We use download_one and not the rest API because it has special timeouts etc.
        $path = sprintf('%s/%s', make_request_directory(), $filename);
        $options = [
                'filepath' => $path,
                'timeout' => 15,
                'followlocation' => true,
                'maxredirs' => 5,
        ];
        $result = $client->get($source, null, $options);
        if ($result) {
            return $result;
        }
        return '';
    }


    /**
     * Query OneDrive for files and folders using a search query.
     *
     * Documentation about the query format can be found here:
     *   https://developer.microsoft.com/en-us/graph/docs/api-reference/v1.0/resources/driveitem
     *   https://developer.microsoft.com/en-us/graph/docs/overview/query_parameters
     *
     * This returns a list of files and folders with their details as they should be
     * formatted and returned by functions such as get_listing() or search().
     *
     * @param string $q search query as expected by the Graph API.
     * @param string $path parent path of the current files, will not be used for the query.
     * @param string $parent Parent id.
     * @param int $page page.
     * @return array of files and folders.
     * @throws Exception
     */
    protected function query($q, $path = null, $parent = null, $page = 0) {

        $files = [];
        $folders = [];
        $fields = "folder,id,lastModifiedDateTime,name,size,webUrl";
        $params = ['$select' => $fields, 'parent' => $parent];

        // Retrieving files and folders.
        $systemauth = api::get_system_oauth_client($this->issuer);
        $service = new rest($systemauth);
        if (!empty($q)) {
            $params['search'] = urlencode($q);

            // MS does not return thumbnails on a search.
            unset($params['$expand']);
            $response = $service->call('search', $params);
        } else {
            $response = $service->call('list', $params);
        }

        $remotefiles = isset($response->value) ? $response->value : [];
        foreach ($remotefiles as $remotefile) {
            if (!empty($remotefile->folder)) {
                // This is a folder.
                $folders[$remotefile->id] = [
                        'title' => $remotefile->name,
                        'path' => $this->build_node_path($remotefile->id, $remotefile->name, $path),
                        'date' => strtotime($remotefile->lastModifiedDateTime),
                        'children' => [],
                ];
            } else {
                // We can download all other file types.
                $title = $remotefile->name;
                $source = json_encode([
                        'id' => $remotefile->id,
                        'name' => $remotefile->name,
                        'link' => $remotefile->webUrl,
                ]);
                $files[$remotefile->id] = [
                        'title' => $title,
                        'source' => $source,
                        'date' => strtotime($remotefile->lastModifiedDateTime),
                        'size' => isset($remotefile->size) ? $remotefile->size : null,
                ];
            }
        }

        $files = array_filter($files);
        core_collator::ksort($files, core_collator::SORT_NATURAL);
        core_collator::ksort($folders, core_collator::SORT_NATURAL);
        return array_merge(array_values($folders), array_values($files));
    }

    /**
     * Search throughout the OneDrive
     *
     * @param string $searchtext text to search for.
     * @param int $page search page.
     * @return array of results.
     * @throws coding_exception
     */
    public function search($searchtext, $page = 0) {
        $path = $this->build_node_path('root', get_string('pluginname', 'repository_onedrive'));
        $str = get_string('searchfor', 'repository_onedrive', $searchtext);
        $path = $this->build_node_path('search', $str, $path);

        // Query the Drive.
        $parent = 'root';
        $results = $this->query($searchtext, $path, 'root');

        $ret = [];
        $ret['dynload'] = true;
        $ret['path'] = $this->build_breadcrumb($path);
        $ret['list'] = $results;
        $ret['manage'] = 'https://www.office.com/';
        return $ret;
    }


    /**
     * Build the breadcrumb from a path.
     *
     * @param string $path to create a breadcrumb from.
     * @return array containing name and path of each crumb.
     */
    protected function build_breadcrumb($path) {
        $bread = explode('/', $path);
        $crumbtrail = '';
        foreach ($bread as $crumb) {
            [$id, $name] = $this->explode_node_path($crumb);
            $name = empty($name) ? $id : $name;
            $breadcrumb[] = [
                'name' => $name,
                'path' => $this->build_node_path($id, $name, $crumbtrail),
            ];
            $tmp = end($breadcrumb);
            $crumbtrail = $tmp['path'];
        }
        return $breadcrumb;
    }
    /**
     * Returns information about a node in a path.
     *
     * @see self::build_node_path()
     * @param string $node to extrat information from.
     * @return array about the node.
     */
    protected function explode_node_path($node) {
        if (strpos($node, '|') !== false) {
            [$id, $name] = explode('|', $node, 2);
            $name = urldecode($name);
        } else {
            $id = $node;
            $name = '';
        }
        $id = urldecode($id);
        return [
                0 => $id,
                1 => $name,
                'id' => $id,
                'name' => $name,
        ];
    }
    /**
     * Generates a safe path to a node.
     *
     * Typically, a node will be id|Name of the node.
     *
     * @param string $id of the node.
     * @param string $name of the node, will be URL encoded.
     * @param string $root to append the node on, must be a result of this function.
     * @return string path to the node.
     */
    protected function build_node_path($id, $name = '', $root = '') {
        $path = $id;
        if (!empty($name)) {
            $path .= '|' . urlencode($name);
        }
        if (!empty($root)) {
            $path = trim($root, '/') . '/' . $path;
        }
        return $path;
    }
}
