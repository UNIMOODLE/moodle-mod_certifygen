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
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 *
 * @package    certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace certifygenreport_basic;
use certifygenreport_basic\output\report_view;
use core\exception\coding_exception;
use core\context\system;
use dml_exception;
use mod_certifygen\interfaces\icertificatereport;
use mod_certifygen\persistents\certifygen_validations;
use core\exception\moodle_exception;

/**
 * certifygenreport_basic
 * @package    certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenreport_basic implements icertificatereport {
    /**
     * is_enabled
     * @return bool
     * @throws dml_exception
     */
    public function is_enabled(): bool {
        $enabled = (int) get_config('certifygenreport_basic', 'enabled');
        if ($enabled) {
            return true;
        }
        return false;
    }

    /**
     * createFile
     * @param certifygen_validations $teacherrequest
     * @return array
     * @throws coding_exception
     */
    public function create_file(certifygen_validations $teacherrequest): array {
        global $PAGE;
        $courselist = [];
        $courses = $teacherrequest->get('courses');
        $courses = explode(',', $courses);
        foreach ($courses as $courseid) {
            $courselist[] = ['courseid' => $courseid];
        }
        try {
            // Step 2: Create pdf.
            $doc = new certifygenpdf();
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
                $showendtext = false; // Only on last page, true.
                for ($i = 0; $i <= $numblocks; $i++) {
                    if ($i == $numblocks) {
                        $showendtext = true;
                    }
                    $offset = $i * report_view::MAX_NUMBER_COURSES;
                    $coursespagelist = array_slice($courselist, $offset, report_view::MAX_NUMBER_COURSES);
                    $view = new report_view($teacherrequest->get('userid'), $coursespagelist, $i == 0, $showendtext);
                    $content = $renderer->render($view);
                    $doc->writeHTML($content);
                    if ($i < $numblocks) {
                        $doc->AddPage();
                    }
                }
            }
            $res = $doc->Output($teacherrequest->get('code') . '_' . time() . '.pdf', 'S');
            $fs = get_file_storage();
            $context = system::instance();
            $filerecord = [
                'contextid' => $context->id,
                'component' => self::FILE_COMPONENT,
                'filearea' => self::FILE_AREA,
                'itemid' => $teacherrequest->get('id'),
                'filename' => $teacherrequest->get('code') . '.pdf',
                'filepath' => self::FILE_PATH,
            ];
            $file = $fs->get_file(
                $filerecord['contextid'],
                $filerecord['component'],
                $filerecord['filearea'],
                $filerecord['itemid'],
                $filerecord['filepath'],
                $filerecord['filename']
            );
            if (!$file) {
                $file = $fs->create_file_from_string($filerecord, $res);
            }
            $result['result'] = true;
            $result['file'] = $file;
        } catch (moodle_exception $e) {
            $result['result'] = false;
            $result['message'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get the certificate content.
     *
     * @param certifygen_validations $trequest
     * @return array
     */
    public function get_certificate_elements(certifygen_validations $trequest): array {
        $output = [];
        try {
            $courselist = [];
            $courses = $trequest->get('courses');
            $courses = explode(',', $courses);
            foreach ($courses as $courseid) {
                $courselist[] = ['courseid' => $courseid];
            }
            $view = new report_view($trequest->get('userid'), $courselist);
            $output['list'] = $view->get_courses_list();
        } catch (moodle_exception $exception) {
            $output['error']['code'] = 'general_error';
            $output['error']['message'] = $exception->getMessage();
        }

        return $output;
    }
}
