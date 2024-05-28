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
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

namespace mod_certifygen\persistents;
use core\persistent;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\plugininfo\certifygenvalidation;

/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygen_model extends persistent {

    /**
     * @var string table
     */
    public const TABLE = 'certifygen_model';
    public const TYPE_ACTIVITY = 1;
    public const TYPE_TEACHER = 2;
    public const MODE_UNIQUE = 1;
    public const MODE_PERIODIC = 2;
    /**
     * Define properties
     *
     * @return array[]
     */
    protected static function define_properties(): array {
        return [
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'type' => [
                'type' => PARAM_INT,
            ],
            'mode' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'templateid' => [
                'type' => PARAM_INT,
            ],
            'timeondemmand' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'langs' => [
                'type' => PARAM_TEXT,
                'default' => NULL,
                'null' => NULL_ALLOWED,
            ],
            'validation' => [
                'type' => PARAM_TEXT,
                'default' => NULL,
                'null' => NULL_ALLOWED,
            ],
            'usermodified' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    /**
     * @param object $data
     * @return self
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    public static function save_model_object( object $data) : self {
        global $USER, $CFG;
        $modeldata = [
            'name' => $data->modelname,
            'type' => $data->type,
            'mode' => $data->mode,
            'templateid' => $data->templateid,
            'timeondemmand' => $data->timeondemmand ?? 0,
            'langs' => empty($data->langs) ? NULL : $data->langs,
            'validation' => empty($data->validation) ? NULL : $data->validation,
            'usermodified' => $USER->id,
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        $id = $data->modelid ?? 0;
        $model = new self($id, (object)$modeldata);
        if ($id > 0) {
            $model->update();
            return $model;
        }

        return $model->create();
    }

    /**
     * @param $limitfrom
     * @param $limitnum
     * @return int
     * @throws \dml_exception
     */
    public static function count_context_models($limitfrom = 0, $limitnum = 0) : int {
        global $DB;

        $num = $DB->count_records('certifygen_model', ['type' => self::TYPE_TEACHER]);

        return $num;
    }

    /**
     * @param int $limitfrom
     * @param int $limitnum
     * @param string $sort
     * @return array
     */
    public static function get_context_models(int $limitfrom = 0, int $limitnum = 0, string $sort = '') : array {
        global $DB;

        return $DB->get_records('certifygen_model', ['type' => self::TYPE_TEACHER]);
    }
}