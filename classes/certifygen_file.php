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
namespace mod_certifygen;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
use coding_exception;
use mod_certifygen\persistents\certifygen_model;
use core\url;
use stdClass;
use stored_file;
/**
 * certifygen_file
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygen_file {
    /** @var stored_file $file */
    private stored_file $file;
    /** @var int $userid */
    private int $userid;
    /** @var int $modelid */
    private int $modelid;
    /** @var int $validationid */
    private int $validationid;
    /** @var string  $lang */
    private string $lang;
    /** @var array  $metadata */
    private array $metadata;

    /**
     * certifygen_file
     */
    public function __construct(stored_file $file, int $userid, string $lang, int $modelid, int $validationid) {
        $this->file = $file;
        $this->userid = $userid;
        $this->lang = $lang;
        $this->modelid = $modelid;
        $this->validationid = $validationid;
    }

    /**
     * get_validationid
     * @return int
     */
    public function get_validationid(): int {
        return $this->validationid;
    }
    /**
     * get_file
     * @return stored_file
     */
    public function get_file(): stored_file {
        return $this->file;
    }

    /**
     * get_user
     * @return stdClass
     */
    public function get_user(): stdClass {
        $users = user_get_users_by_id([$this->userid]);
        return reset($users);
    }

    /**
     * set_metadata
     * @param array $data
     * @return void
     */
    public function set_metadata(array $data): void {
        $this->metadata = $data;
    }
    /**
     * get_metadata
     * @return array
     */
    public function get_metadata(): array {
        return $this->metadata;
    }

    /**
     * get_file_url
     * @return string
     */
    public function get_file_url(): string {
        $file = $this->get_file();
        $name = $file->get_filename();
        return url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $name
        );
    }

    /**
     * get_model_type
     * @return int
     * @throws coding_exception
     */
    public function get_model_type(): int {
        $model = new certifygen_model($this->modelid);
        return $model->get('type');
    }
}
