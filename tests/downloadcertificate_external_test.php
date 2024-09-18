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
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use certifygenvalidation_webservice\external\change_status_external;
use core\invalid_persistent_exception;
use mod_certifygen\external\downloadcertificate_external;
use mod_certifygen\external\emitcertificate_external;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use mod_certifygen\task\checkfile;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot . '/lib/externallib.php');
/**
 * Download certifiacte test
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class downloadcertificate_external_test extends advanced_testcase {
    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * test
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws invalid_persistent_exception
     * @throws moodle_exception
     */
    public function test_downloadcertificate(): void {

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_ACTIVITY,
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create mod_certifygen module.
        $datamodule = [
            'name' => 'Test 1,',
            'course' => $course->id,
            'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course->id, false, MUST_EXIST);

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                ]);

        // Enrol into the course as student.
        self::getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Login as student.
        $this->setUser($student);

        $data = [
            'userid' => $student->id,
            'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        emitcertificate_external::emitcertificate(0, $cm->instance, $model->get('id'), $lang, $student->id, $course->id);

        // Now validation record exists.
        $validation = certifygen_validations::get_record($data);
        $code = certifygen_validations::get_certificate_code($validation);
        $localrepository = new certifygenrepository_localrepository\certifygenrepository_localrepository();
        $fileurl = $localrepository->get_file_url($validation);
        $result = downloadcertificate_external::downloadcertificate(
            $validation->get('id'),
            $cm->instance,
            $model->get('id'),
            $code,
            $course->id
        );

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('url', $result);
        self::assertArrayHasKey('message', $result);
        self::assertTrue($result['result']);
        self::assertEquals(get_string('ok', 'mod_certifygen'), $result['message']);
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $validation->get('status'));
        self::assertEquals($fileurl, $result['url']);
    }

    /**
     * Test
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws invalid_persistent_exception
     * @throws moodle_exception
     */
    public function test_downloadcertificate2(): void {

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_ACTIVITY,
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create mod_certifygen module.
        $datamodule = [
            'name' => 'Test 1,',
            'course' => $course->id,
            'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course->id, false, MUST_EXIST);

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                ]);

        // Enrol into the course as student.
        self::getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $student2 = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_1@fake.es',
                ]);

        // Enrol into the course as student.
        self::getDataGenerator()->enrol_user($student2->id, $course->id, 'student');

        // Login as student.
        $this->setUser($student);

        $data = [
            'userid' => $student->id,
            'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        emitcertificate_external::emitcertificate(0, $cm->instance, $model->get('id'), $lang, $student->id, $course->id);

        // Login as student.
        $this->setUser($student2);
        // Now validation record exists.
        $validation = certifygen_validations::get_record($data);
        self::assertEquals(certifygen_validations::STATUS_FINISHED, (int)$validation->get('status'));

        $code = certifygen_validations::get_certificate_code($validation);
        $result = downloadcertificate_external::downloadcertificate(
            $validation->get('id'),
            $cm->instance,
            $model->get('id'),
            $code,
            $course->id
        );

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertFalse($result['result']);
        self::assertEquals(get_string('nopermissiontodownloadothercerts', 'mod_certifygen'), $result['message']);
    }

    /**
     * Test: validation ws + localrepository.
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws invalid_persistent_exception
     * @throws moodle_exception
     */
    public function test_downloadcertificate_3(): void {

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenreport_basic');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model(
            certifygen_model::TYPE_ACTIVITY,
            certifygen_model::MODE_UNIQUE,
            $certificate1->get_id(),
            'certifygenvalidation_webservice',
            '',
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create mod_certifygen module.
        $datamodule = [
                'name' => 'Test 1,',
                'course' => $course->id,
                'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course->id, false, MUST_EXIST);

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
        ]);

        // Enrol into the course as student.
        self::getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Login as student.
        $this->setUser($student);

        $data = [
                'userid' => $student->id,
                'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        emitcertificate_external::emitcertificate(0, $cm->instance, $model->get('id'), $lang, $student->id, $course->id);

        // Login as admin.
        $this->setAdminUser();

        // Change status.
        $validation = certifygen_validations::get_record($data);
        change_status_external::change_status(
            $student->id,
            '',
            $validation->get('id')
        );
        $validation = new certifygen_validations($validation->get('id'));
        self::assertEquals(certifygen_validations::STATUS_VALIDATION_OK, (int)$validation->get('status'));

        // Execute task.
        $removaltask = new checkfile();
        $removaltask->execute();

        $validation = new certifygen_validations($validation->get('id'));
        self::assertEquals(certifygen_validations::STATUS_FINISHED, (int)$validation->get('status'));

        // Now validation record exists.
        $code = certifygen_validations::get_certificate_code($validation);
        $localrepository = new certifygenrepository_localrepository\certifygenrepository_localrepository();
        $fileurl = $localrepository->get_file_url($validation);
        $result = downloadcertificate_external::downloadcertificate(
            $validation->get('id'),
            $cm->instance,
            $model->get('id'),
            $code,
            $course->id
        );

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
