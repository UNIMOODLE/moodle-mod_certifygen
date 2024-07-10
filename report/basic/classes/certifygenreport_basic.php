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
require_once($CFG->dirroot . '/lib/pdflib.php');

use certifygenreport_basic\output\report_view;
use mod_certifygen\interfaces\ICertificateReport;
use mod_certifygen\persistents\certifygen_teacherrequests;

class certifygenreport_basic implements ICertificateReport
{

    /**
     * @return bool
     */
    public function is_enabled(): bool
    {
        return true;
    }

    /**
     * @param \mod_certifygen\persistents\certifygen_teacherrequests $teacherrequest
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function createFile(\mod_certifygen\persistents\certifygen_teacherrequests $teacherrequest): array
    {
        global $PAGE;
        $courselist = [];
        $courses = $teacherrequest->get('courses');
        $courses = explode(',', $courses);
        foreach ($courses as $courseid) {
            $courselist[] = ['course' => format_text(get_course($courseid)->fullname)];
        }
        try {
            // Step 2: Create pdf.
            $doc = new \pdf();
            $doc->setPrintHeader(false);
            $doc->setPrintFooter(true);
            $doc->AddPage();
            $renderer = $PAGE->get_renderer('certifygenreport_basic');
            if (count($courselist) <= report_view::MAX_NUMBER_COURSES) {
                $view = new report_view($teacherrequest->get('userid'), $courselist);
                $content = $renderer->render($view);
                $doc->writeHTML($content);
            } else {
                // Course blocks.
                $numblocks = (int) (count($courselist) / report_view::MAX_NUMBER_COURSES);
                for($i = 0; $i <= $numblocks; $i++) {
                    $offset = $i * report_view::MAX_NUMBER_COURSES;
                    $coursespagelist = array_slice($courselist, $offset, report_view::MAX_NUMBER_COURSES);
                    $view = new report_view($teacherrequest->get('userid'), $coursespagelist, $i==0);
                    $content = $renderer->render($view);
                    //    $doc->writeHTML($content, true, false, true); // no es necesario.
                    $doc->writeHTML($content);
                    if ($i < $numblocks) {
                        $doc->AddPage();
                    }
                }
            }
            $res = $doc->Output(ICertificateReport::FILE_NAME_STARTSWITH . $teacherrequest->get('id') .'_'.time().'.pdf', 'S');
            $fs = get_file_storage();
            $context = \context_system::instance();
            $filerecord = [
                'contextid' => $context->id,
                'component' => self::FILE_COMPONENT,
                'filearea' => self::FILE_AREA,
                'itemid' => $teacherrequest->get('id'),
                'filename' => self::FILE_NAME_STARTSWITH . $teacherrequest->get('id') . '.pdf',
                'filepath' => self::FILE_PATH,
            ];
            $file = $fs->create_file_from_string($filerecord, $res);
            $result['result'] = true;
            $result['file'] = $file;
            $teacherrequest->set('status', certifygen_teacherrequests::STATUS_FINISHED_OK);
            $teacherrequest->save();
        } catch (moodle_exception $e) {
            error_log(__FUNCTION__ . ' ' . ' error: '.var_export($e->getMessage(), true));
            $result['result'] = false;
            $result['message'] = $e->getMessage();
            $teacherrequest->set('status', certifygen_teacherrequests::STATUS_FINISHED_ERROR);
            $teacherrequest->save();
        }

        return $result;
    }
}