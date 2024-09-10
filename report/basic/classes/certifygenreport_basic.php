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
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.
/**
 * @package    certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace certifygenreport_basic;
global $CFG;
//require_once($CFG->dirroot . '/lib/pdflib.php');

use certifygenreport_basic\output\report_view;
use context_system;
use mod_certifygen\interfaces\ICertificateReport;
use mod_certifygen\persistents\certifygen_validations;
//use pdf;

class certifygenreport_basic implements ICertificateReport
{

    /**
     * @return bool
     */
    public function is_enabled(): bool
    {
        $enabled = (int) get_config('certifygenreport_basic', 'enabled');
        if ($enabled) {
            return true;
        }
        return false;
    }

    /**
     * @param certifygen_validations $teacherrequest
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function createFile(certifygen_validations $teacherrequest): array
    {
        global $PAGE;
        $courselist = [];
        $courses = $teacherrequest->get('courses');
        $courses = explode(',', $courses);
        foreach ($courses as $courseid) {
            //$courselist[] = ['course' => strip_tags(format_text(get_course($courseid)->fullname))];
            $courselist[] = ['courseid' => $courseid];
        }
        try {
            // Step 2: Create pdf.
            //$doc = new pdf();
            $doc = new certifygenpdf();
//            $image_file = $this->get_logo_url();
//            if (!empty($image_file)) {
//                $doc->setHeaderData($image_file, 8, 'Unimoodle Certifygen', '');
//            } else {
//                $doc->setPrintHeader(false);
//            }
            $doc->setPrintHeader(false);
            $footertext = get_config('certifygenreport_basic', 'footer');
            $doc->set_footer_text(strip_tags($footertext));
            $doc->setPrintFooter();

            $doc->AddPage();
            $renderer = $PAGE->get_renderer('certifygenreport_basic');
            if (count($courselist) <= report_view::MAX_NUMBER_COURSES) {
                $view = new report_view($teacherrequest->get('userid'), $courselist);
                $content = $renderer->render($view);
                $doc->writeHTML($content);
            } else {
                // Course blocks.
                $numblocks = (int) (count($courselist) / report_view::MAX_NUMBER_COURSES);
                $showendtext = false; // only on last page, true.
                for($i = 0; $i <= $numblocks; $i++) {
                    if ($i == $numblocks) {
                        $showendtext = true;
                    }
                    $offset = $i * report_view::MAX_NUMBER_COURSES;
                    $coursespagelist = array_slice($courselist, $offset, report_view::MAX_NUMBER_COURSES);
                    $view = new report_view($teacherrequest->get('userid'), $coursespagelist, $i==0, $showendtext);
                    $content = $renderer->render($view);
                    $doc->writeHTML($content);
                    if ($i < $numblocks) {
                        $doc->AddPage();
                    }
                }
            }
           $res = $doc->Output($teacherrequest->get('code') .'_'.time().'.pdf', 'S');
            //$res = $doc->Output($teacherrequest->get('code') .'_'.time().'.pdf', 'D');
            $fs = get_file_storage();
            $context = context_system::instance();
            $filerecord = [
                'contextid' => $context->id,
                'component' => self::FILE_COMPONENT,
                'filearea' => self::FILE_AREA,
                'itemid' => $teacherrequest->get('id'),
                'filename' => $teacherrequest->get('code') . '.pdf',
                'filepath' => self::FILE_PATH,
            ];
            $file = $fs->get_file($filerecord['contextid'], $filerecord['component'], $filerecord['filearea'], $filerecord['itemid'],
                $filerecord['filepath'], $filerecord['filename']);
            if (!$file) {
                $file = $fs->create_file_from_string($filerecord, $res);
            }
            $result['result'] = true;
            $result['file'] = $file;
        } catch (moodle_exception $e) {
            error_log(__FUNCTION__ . ' ' . ' error: '.var_export($e->getMessage(), true));
            $result['result'] = false;
            $result['message'] = $e->getMessage();
        }

        return $result;
    }
//    public function get_logo_url() : string {
//        global $CFG;
//        try {
//            $fs = get_file_storage();
//            $context = context_system::instance();
//            $filename = get_config('certifygenreport_basic', 'logo');
//            if (empty($filename)) {
//                return '';
//            }
//            $logo = $fs->get_file($context->id, report_view::REPORT_COMPONENT, 'logo', 0,
//                '/', $filename);
//            if (!$logo) {
//                return '';
//            }
//            // Lo guardamos en el repo.
//            $tempdir = make_temp_directory('certifygen');
//            //$url = tempnam($tempdir, 'certifygen') . '.png';
//            $url = tempnam($tempdir, 'certifygen');
//            file_put_contents($url, $logo->get_content());
//
//            // no funciona como pantalla pero en pdf si,
//            // //por lo menos desde el report_view que lo lleva a template y luego se pasa a pdf.
////            $img_base64_encoded =  'data:image/png;base64, ' . base64_encode($logo->get_content());
////            $url = '@' . preg_replace('#^data:image/[^;]+;base64,#', '', $img_base64_encoded) . '">';
//
//            // base64 no funciona en loca
////            $img_base64_encoded =  'data:image/png;base64, ' . base64_encode($logo->get_content());
////            //$url = '@' . preg_replace('#^data:image/[^;]+;base64,#', '', $img_base64_encoded) . '">';
////            $url = preg_replace('#^data:image/[^;]+;base64,#', '', $img_base64_encoded);
//
//            // FUnciona en local pero en pre no.
////            $url = '/mod/certifygen/report/basic/pix' . $filename;
////            if (file_exists($CFG->dirroot . $url)) {
////                unlink($url);
////            }
////            $logo->copy_content_to($CFG->dirroot . $url);
//
//        } catch ( \moodle_exception $exception) {
//            $url = '';
//        }
//
//        error_log(__FUNCTION__ . ' url : '.var_export($url, true));
//        return $url;
//    }
}