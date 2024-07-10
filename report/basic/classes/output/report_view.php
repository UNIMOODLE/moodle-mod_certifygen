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
 * @package    certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace certifygenreport_basic\output;
global $CFG;
require_once($CFG->dirroot.'/user/lib.php');
use context_system;
use dml_exception;
use moodle_url;

class report_view implements \renderable, \templatable
{
    private int $userid;
    private bool $showtext;
    const REPORT_COMPONENT = 'certifygenreport_basic';
    const REPORT_FILEAREA = 'logo';
    const MAX_NUMBER_COURSES = 26;

    /**
     * @param int $userid
     */
    public function __construct(int $userid, array $courses, bool $showtext = true)
    {
        $this->userid = $userid;
        $this->courses = $courses;
        $this->showtext = $showtext;
    }
    /**
     * @param \renderer_base $output
     * @return \stdClass
     * @throws \coding_exception
     * @throws dml_exception
     */
    public function export_for_template(\renderer_base $output)
    {
        $user = user_get_users_by_id([$this->userid]);
        $user = reset($user);
        $data = new \stdClass();
        $url = $this->get_logo_url();
//        $data->logo = $url->out();
        $data->logosrc = $url;
        $data->footer = $this->get_footer();
        $name = fullname($user);
        if ($this->showtext) {
            $data->hastext = true;
            $data->text = get_string('reporttext', 'certifygenreport_basic', (object)['name' => $name] );
        }
        $data->list = $this->courses;
        return $data;
    }

    /**
     * @return moodle_url
     * @throws dml_exception
     */
    public function get_logo_url() : string {
        global $OUTPUT, $CFG;
        $fs = get_file_storage();
        $context = context_system::instance();
        $filename = get_config('certifygenreport_basic', 'logo');
        $logo = $fs->get_file($context->id, self::REPORT_COMPONENT, self::REPORT_FILEAREA, 0,
            '/', $filename);
        $img_base64_encoded =  'data:image/png;base64, ' . base64_encode($logo->get_content());
        return  '@' . preg_replace('#^data:image/[^;]+;base64,#', '', $img_base64_encoded) . '">';
    }

    /**
     * @return string
     * @throws dml_exception
     */
    public function get_footer() : string {
        return get_config('certifygenreport_basic', 'footer');
    }
}