<?php

namespace mod_certifygen\output\views;

use coding_exception;
use dml_exception;
use mod_certifygen\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use renderable;
use renderer_base;
use stdClass;
use templatable;
class context_certificate_view  implements renderable, templatable {
    private certifygen_model $model;
    private int $courseid;
    private bool $hasvalidator;

    /**
     * @throws coding_exception
     */
    public function __construct(certifygen_model $model, int $courseid) {
        $this->model = $model;
        $this->courseid = $courseid;
        $this->hasvalidator = !is_null($model->get('validation'));
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
                $langused[] = $validationrecord->get('lang');
                $data = [
                    'code' => certifygen::get_user_certificate($USER->id, $this->courseid, $this->model->get('templateid'), $validationrecord->get('lang'))->code,
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
}