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

/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\output\views;

use coding_exception;
use dml_exception;
use mod_certifygen\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use renderable;
use stdClass;
use templatable;
use renderer_base;

class student_view implements renderable, templatable {
    /**
     * @var bool|mixed|null
     */
    private bool $hasvalidator;
    private certifygen_model $certificatemodel;
    private int $instance;
    private int $templateid;
    private int $courseid;

    /**
     * @param int $courseid
     * @param int $templateid
     * @param int $instance
     * @throws coding_exception
     */
    public function __construct(int $courseid, int $templateid, int $instance) {
        $this->courseid = $courseid;
        $this->templateid = $templateid;
        $this->instance = $instance;
        $certificate = new \mod_certifygen\persistents\certifygen($instance);
        $this->certificatemodel = new certifygen_model($certificate->get('modelid'));
        $this->hasvalidator = !is_null($this->certificatemodel->get('validation'));

    }

    /**
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    public function export_for_template(renderer_base $output) : stdClass {

        if ($this->hasvalidator) {
            return $this->export_with_validator_data();
        } else {
            return $this->export_no_validator_data();
        }
    }


    /**
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function export_no_validator_data() : stdClass {
        global $USER;

        $list = [];
        $langlist = get_string_manager()->get_list_of_translations();
        // Generamos tantos como idiomas en la plataforma.
        $langs = $this->certificatemodel->get('langs');
        $langs = explode(',', $langs);
        foreach($langs as $lang) {
            $list[] = [
                'modelid' => $this->certificatemodel->get('id'),
                'lang' => $lang,
                'langstring' => $langlist[$lang],
                'courseid' => $this->courseid,
                'userid' => $USER->id,
                'haslink' => true,
                'url' => certifygen::get_user_certificate_file_url($this->certificatemodel->get('templateid'), $USER->id, $this->courseid, $lang),
            ];
        }

        $data = new stdClass();
        $data->list = $list;
        $data->hasvalidator = $this->hasvalidator;

        return $data;
    }
    /**
     * @throws dml_exception
     * @throws coding_exception
     */
    public function export_with_validator_data() : stdClass {
        global $USER;

        $validationrecords = certifygen_validations::get_records(['modelid' => $this->certificatemodel->get('id'), 'userid' => $USER->id]);
        $list = [];
        $langlist = get_string_manager()->get_list_of_translations();
        // Generamos tantos como idiomas en la plataforma.
        $langs = $this->certificatemodel->get('langs');
        $langs = explode(',', $langs);
        if (empty($validationrecords)) {
            $id = 0;
            foreach($langs as $lang) {
                $list[] = [
                    'code' => '',
                    'status' => get_string('status_' . certifygen_validations::STATUS_NOT_STARTED, 'mod_certifygen'),
                    'modelid' => $this->certificatemodel->get('id'),
                    'lang' => $lang,
                    'langstring' => $langlist[$lang],
                    'id' => $id,
                    'courseid' => $this->courseid,
                    'userid' => $USER->id,
                    'canemit' => true,
                ];
            }
        } else {
            $langused = [];
            foreach($validationrecords as $validationrecord) {
                $langused[] = $validationrecord->get('lang');
                $data = [
                    'code' => certifygen::get_user_certificate($USER->id, $this->courseid, $this->certificatemodel->get('templateid'), $validationrecord->get('lang'))->code ?? '',
                    'status' => get_string('status_' . $validationrecord->get('status'), 'mod_certifygen'),
                    'modelid' => $this->certificatemodel->get('id'),
                    'lang' => $validationrecord->get('lang'),
                    'langstring' => $langlist[$validationrecord->get('lang')],
                    'id' =>  $validationrecord->get('id'),
                    'courseid' => $this->courseid,
                    'userid' => $USER->id,
                ];
                if ($validationrecord->get('status')  == certifygen_validations::STATUS_FINISHED_OK) {
                    $data['candownload'] = true;
                }
                if ($validationrecord->get('status')  !== certifygen_validations::STATUS_IN_PROGRESS
                    && $validationrecord->get('status')  !== certifygen_validations::STATUS_FINISHED_OK) {
                    $data['canemit'] = true;
                }
                $list[] = $data;
            }
            if (count($langused) != count($langs)) {
                foreach($langs as $lang) {
                    if (in_array($lang, $langused)) {
                        continue;
                    }
                    $list[] = [
                        'code' => '',
                        'status' => get_string('status_' . certifygen_validations::STATUS_NOT_STARTED, 'mod_certifygen'),
                        'modelid' => $this->certificatemodel->get('id'),
                        'lang' => $lang,
                        'langstring' => $langlist[$lang],
                        'id' => 0,
                        'courseid' => $this->courseid,
                        'userid' => $USER->id,
                        'canemit' => true,
                    ];
                }
            }
        }

        $data = new stdClass();
        $data->list = $list;
        $data->hasvalidator = $this->hasvalidator;

        return $data;
    }
}