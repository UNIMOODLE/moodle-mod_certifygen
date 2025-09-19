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
namespace mod_certifygen\output\views;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');

use core\exception\coding_exception;
use dml_exception;
use mod_certifygen\persistents\certifygen_context;
use mod_certifygen\tables\profile_my_certificates_table;
use core\exception\moodle_exception;
use core\url;
use renderable;
use stdClass;
use templatable;
use renderer_base;
/**
 * profile_my_certificates_view
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_my_certificates_view implements renderable, templatable {
    /** @var int $userid */
    private int $userid;
    /** @var int $pagesize */
    private int $pagesize;

    /**
     * __construct
     * @param int $userid
     * @param int $pagesize
     */
    public function __construct(int $userid = 0, int $pagesize = 10) {
        global $USER;
        $this->userid = $userid;
        if (!$userid) {
            $this->userid = $USER->id;
        }
        $this->pagesize = $pagesize;
    }

    /**
     * export_for_template
     * @param renderer_base $output
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $USER;
        $data = new stdClass();

        $tablelist = new profile_my_certificates_table($this->userid);
        $tablelist->baseurl = new url('/mod/certifygen/mycertificates.php');
        ob_start();
        // TODO: optional_params 10 and true.
        $tablelist->out($this->pagesize, false);
        $out1 = ob_get_contents();
        ob_end_clean();
        $data->table = $out1;
        $data->userid = $this->userid;
        [$modelids, $langs] = certifygen_context::get_system_context_modelids_and_langs();
        if (count($modelids) > 0) {
            $data->cancreaterequest = true;
        }
        if ($this->userid == $USER->id) {
            $data->mycertificates = true;
        } else {
            $user = user_get_users_by_id([$this->userid]);
            $user = reset($user);
            $data->title = get_string('othercertificates', 'mod_certifygen', fullname($user));
        }
        return $data;
    }
}
