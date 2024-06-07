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
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use moodle_url;
use renderable;
use stdClass;
use templatable;
use renderer_base;

class mycertificates_view implements renderable, templatable {
    private certifygen_model $model;
    private int $courseid;
    private bool $hasvalidator;
    private moodle_url $url;
    private string $lang;

    /**
     * @param certifygen_model $model
     * @param int $courseid
     * @param moodle_url $url
     * @throws coding_exception
     */
    public function __construct(certifygen_model $model, int $courseid, moodle_url $url) {
        $this->model = $model;
        $this->courseid = $courseid;
        $this->hasvalidator = !is_null($model->get('validation'));
        $this->url = $url;
        $this->lang = mod_certifygen_get_lang_selected($this->model);
    }

    /**
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    public function export_for_template(renderer_base $output) : stdClass {

        if ($this->hasvalidator) {
            $data = $this->export_with_validator_data();
        } else {
            $data = $this->export_no_validator_data();
        }
        $data->form = mod_certifygen_get_certificates_table_form($this->model, $this->url);
        return $data;
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
        $langs = $this->model->get('langs');
        $langs = explode(',', $langs);
        foreach($langs as $lang) {
            if ($lang != $this->lang) {
                continue;
            }
            $list[] = [
                'modelid' => $this->model->get('id'),
                'lang' => $lang,
                'langstring' => $langlist[$lang],
                'courseid' => $this->courseid,
                'userid' => $USER->id,
                'haslink' => true,
                'url' => certifygen::get_user_certificate_file_url($this->model->get('templateid'), $USER->id, $this->courseid, $lang),
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

        $validationrecords = certifygen_validations::get_records(['modelid' => $this->model->get('id'), 'userid' => $USER->id]);
        $list = [];
        $langlist = get_string_manager()->get_list_of_translations();
        // Generamos tantos como idiomas en la plataforma.
        $langs = $this->model->get('langs');
        $langs = explode(',', $langs);

        if (empty($validationrecords)) {
            $id = 0;
            foreach($langs as $lang) {
                if ($lang != $this->lang) {
                    continue;
                }
                $list[] = [
                    'code' => '',
                    'status' => get_string('status_' . certifygen_validations::STATUS_NOT_STARTED, 'mod_certifygen'),
                    'modelid' => $this->model->get('id'),
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
                if ($validationrecord->get('lang') != $this->lang) {
                    continue;
                }
                $langused[] = $validationrecord->get('lang');
                $code = certifygen::get_user_certificate($USER->id, $this->courseid, $this->model->get('templateid'), $validationrecord->get('lang'))->code ?? '';
                $data = [
                    'code' => $code,
                    'status' => get_string('status_' . $validationrecord->get('status'), 'mod_certifygen'),
                    'modelid' => $this->model->get('id'),
                    'lang' => $validationrecord->get('lang'),
                    'langstring' => $langlist[$validationrecord->get('lang')],
                    'id' =>  $validationrecord->get('id'),
                    'courseid' => $this->courseid,
                    'userid' => $USER->id,
                ];
                if ($validationrecord->get('status')  == certifygen_validations::STATUS_FINISHED_OK) {
                    $data['candownload'] = true;
                    $validationplugin = $this->model->get('validation');
                    $validationpluginclass = $validationplugin . '\\' . $validationplugin;
                    if (get_config($validationplugin, 'enable') === '1') {
                        /** @var ICertificateValidation $subplugin */
                        $subplugin = new $validationpluginclass();
                        $url = $subplugin->getFileUrl($this->courseid, $validationrecord->get('id'), $code.'.pdf');
                        if (!empty($url)) {
                            $data['downloadurl'] = $url;
                        }
                    }
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
                        'modelid' => $this->model->get('id'),
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