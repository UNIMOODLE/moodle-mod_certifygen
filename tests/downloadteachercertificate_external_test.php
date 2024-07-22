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

use mod_certifygen\external\downloadteachercertificate_external;
use mod_certifygen\external\emitteacherrequest_external;
use mod_certifygen\interfaces\ICertificateReport;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_teacherrequests;

global $CFG;
require_once($CFG->dirroot.'/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot.'/lib/externallib.php');

class downloadteachercertificate_external_test extends advanced_testcase {

    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * @return void
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     * @throws invalid_parameter_exception
     */
    public function test_downloadteachercertificate(): void {

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            $certificate1->get_id(),
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
        );
        $modgenerator->assign_model_systemcontext($model->get('id'));

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create user and enrol as teacher.
        $teacher = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test',
                'lastname' => 'user 1', 'email' => 'test_user_1@fake.es']);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        // Create teacherrequest.
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course->id, $teacher->id);

        // Emit certificate.
        emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));
        $teacherrequest = new certifygen_teacherrequests($teacherrequest->get('id'));
        $contextid = context_system::instance()->id;
        self::assertEquals(certifygen_teacherrequests::STATUS_VALIDATION_OK, $teacherrequest->get('status'));
        $filename = ICertificateReport::FILE_NAME_STARTSWITH . $teacherrequest->get('id') . '.pdf';
        $fileurl = moodle_url::make_pluginfile_url($contextid, ICertificateReport::FILE_COMPONENT,
            ICertificateReport::FILE_AREA, $teacherrequest->get('id'), ICertificateReport::FILE_PATH,
            $filename)->out();

        // Download.
        $result = downloadteachercertificate_external::downloadteachercertificate($teacherrequest->get('id'));

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('url', $result);
        self::assertArrayHasKey('message', $result);
        self::assertTrue($result['result']);
        self::assertEquals(get_string('ok', 'mod_certifygen'), $result['message']);
        self::assertEquals($fileurl, $result['url']);
    }
}