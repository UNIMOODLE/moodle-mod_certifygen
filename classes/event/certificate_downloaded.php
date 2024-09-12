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
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\event;

use coding_exception;
use context_module;
use context_system;
use core\event\base;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/modinfolib.php');
/**
 * Certificate downloaded event
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certificate_downloaded extends base {

    /**
     * init
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'certifygen_validations';
    }
    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has downloaded a certificate";
    }

    /**
     * Create event form validation object
     * @throws coding_exception
     * @throws moodle_exception
     */
    public static function create_from_validation(certifygen_validations $validation) {
        $context = context_system::instance();
        if (!empty($validation->get('certifygenid'))) {
            [$course, $cm] = get_course_and_cm_from_instance((int)$validation->get('certifygenid'), 'certifygen');
            $context = context_module::instance($cm->id);
        }
        $model = new certifygen_model($validation->get('modelid'));
        $data = [
            'context' => $context,
            'objectid' => $validation->get('id'),
            'other' => [
                'validation' => $model->get('validation'),
                'repository' => $model->get('repository'),
                'report' => $model->get('report'),
            ],
        ];
        /** @var certificate_issued $event */
        $event = self::create($data);
        $event->add_record_snapshot('certifygen_validations', $validation->to_record());
        return $event;
    }
}
