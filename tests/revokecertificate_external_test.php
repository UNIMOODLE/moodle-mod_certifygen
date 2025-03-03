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
namespace mod_certifygen;
use core\invalid_persistent_exception;
use mod_certifygen\external\emitcertificate_external;
use mod_certifygen\external\revokecertificate_external;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot . '/lib/externallib.php');
/**
 * Revoke certificate
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class revokecertificate_external_test extends \advanced_testcase {
    /** @var string|mixed $lang */
    private string $lang;
    /** @var int $userid */
    private int $userid;
    /** @var int $certifygenid */
    private int $certifygenid;
    /** @var int $modelid */
    private int $modelid;
    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test: student can not revoke
     *
     * @return void
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws invalid_persistent_exception
     * @covers \mod_certifygen\external\revokecertificate_external::revokecertificate
     */
    public function test_revokecertificate(): void {
        // Emit a certificate.
        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY,
        );
        $this->modelid = $model->get('id');
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $this->lang = $langs[0];

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create mod_certifygen module.
        $datamodule = [
            'name' => 'Test 1,',
            'course' => $course->id,
            'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $this->certifygenid = $modcertifygen->id;
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course->id, false, MUST_EXIST);

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                ]);
        $this->userid = $student->id;
        // Enrol into the course as student.
        self::getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Create user and enrol as teacher.
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_2@fake.es',
                ]);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        // Login as student.
        $this->setUser($student);

        emitcertificate_external::emitcertificate(0, $cm->instance, $model->get('id'), $this->lang, $student->id, $course->id);

        $data = [
            'userid' => $this->userid,
            'certifygenid' => $this->certifygenid,
        ];
        $validation = certifygen_validations::get_record($data);
        $result = revokecertificate_external::revokecertificate($validation->get('issueid'), $this->userid, $this->modelid);

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertFalse($result['result']);
        self::assertEquals(get_string('nopermissiontorevokecerts', 'mod_certifygen'), $result['message']);
    }

    /**
     * Test: manager can revoke
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws invalid_persistent_exception
     * @covers \mod_certifygen\external\revokecertificate_external::revokecertificate
     */
    public function test_revokecertificate_2(): void {
        global $DB;
        // Emit a certificate
        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY,
        );
        $this->modelid = $model->get('id');
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $this->lang = $langs[0];

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create mod_certifygen module.
        $datamodule = [
            'name' => 'Test 1,',
            'course' => $course->id,
            'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $this->certifygenid = $modcertifygen->id;
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course->id, false, MUST_EXIST);

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                ]);
        $this->userid = $student->id;
        // Enrol into the course as student.
        self::getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Create user and enrol as teacher.
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_2@fake.es',
                ]);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $manager = $this->getDataGenerator()->create_user([
                'username' => 'test_manager_1',
                'firstname' => 'test',
                'lastname' => 'manager 1',
                'email' => 'test_manager_1@fake.es',
                ]);
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);

        // Login as student.
        $this->setUser($student);

        emitcertificate_external::emitcertificate(0, $cm->instance, $model->get('id'), $this->lang, $student->id, $course->id);

        // Login as manager.
        $this->setUser($manager);

        $data = [
            'userid' => $this->userid,
            'certifygenid' => $this->certifygenid,
        ];
        $validation = certifygen_validations::get_record($data);
        $result = revokecertificate_external::revokecertificate($validation->get('issueid'), $this->userid, $this->modelid);

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertTrue($result['result']);
        self::assertEquals(get_string('ok', 'mod_certifygen'), $result['message']);
    }
}
