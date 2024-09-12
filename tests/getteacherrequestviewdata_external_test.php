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

use mod_certifygen\external\getteacherrequestviewdata_external;
use mod_certifygen\persistents\certifygen_model;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot.'/lib/externallib.php');
/**
 * Get teacher request view data test
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class getteacherrequestviewdata_external_test extends advanced_testcase {

    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public function test_getteacherrequestviewdata(): void {

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

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create users.
        $teacher = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test',
                'lastname' => 'user 1', 'email' => 'test_user_1@fake.es']);
        // Create manager.
        $manager = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_2', 'firstname' => 'test',
                'lastname' => 'user 2', 'email' => 'test_user_2@fake.es']);
        $context = context_system::instance();
        $this->getDataGenerator()->role_assign('manager', $manager->id, $context->id);

        // Login as teacher.
        $this->setUser($teacher);

        // Enrol into the course as teacher.
        self::getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $result = getteacherrequestviewdata_external::getteacherrequestviewdata($teacher->id);

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('table', $result);
        self::assertArrayHasKey('userid', $result);
        self::assertEquals($teacher->id, $result['userid']);
        self::assertArrayHasKey('mycertificates', $result);
        self::assertTrue($result['mycertificates']);
        self::assertArrayNotHasKey('title', $result);
        self::assertEquals(get_string('nothingtodisplay'), trim($result['table']));

        // Login as manager.
        $this->setUser($manager);
        $result = getteacherrequestviewdata_external::getteacherrequestviewdata($teacher->id);
        self::assertIsArray($result);
        self::assertArrayHasKey('table', $result);
        self::assertArrayHasKey('userid', $result);
        self::assertEquals($teacher->id, $result['userid']);
        self::assertArrayNotHasKey('mycertificates', $result);
        self::assertArrayHasKey('title', $result);
        $title = get_string('othercertificates', 'mod_certifygen', fullname($teacher));
        self::assertEquals($title, $result['title']);
        self::assertEquals(get_string('nothingtodisplay'), trim($result['table']));
    }

    /**
     * Test
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public function test_getteacherrequestviewdata_with_data(): void {
        // Create course.
        $course = self::getDataGenerator()->create_course();

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
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id);

        // Create user and enrol as teacher.
        $teacher = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test',
                'lastname' => 'user 1', 'email' => 'test_user_1@fake.es']);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        // Create teacherrequest.
        $modgenerator->create_teacher_request($model->get('id'), $course->id, $teacher->id);

        // Create manager.
        $manager = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_2', 'firstname' => 'test',
                'lastname' => 'user 2', 'email' => 'test_user_2@fake.es']);
        $context = context_system::instance();
        $this->getDataGenerator()->role_assign('manager', $manager->id, $context->id);

        // Login as teacher.
        $this->setUser($teacher);

        // Enrol into the course as teacher.
        self::getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $result = getteacherrequestviewdata_external::getteacherrequestviewdata($teacher->id);

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('table', $result);
        self::assertArrayHasKey('userid', $result);
        self::assertEquals($teacher->id, $result['userid']);
        self::assertArrayHasKey('mycertificates', $result);
        self::assertTrue($result['mycertificates']);
        self::assertArrayNotHasKey('title', $result);
        self::assertNotEquals(get_string('nothingtodisplay'), trim($result['table']));

        // Login as manager.
        $this->setUser($manager);
        $result = getteacherrequestviewdata_external::getteacherrequestviewdata($teacher->id);
        self::assertIsArray($result);
        self::assertArrayHasKey('table', $result);
        self::assertArrayHasKey('userid', $result);
        self::assertEquals($teacher->id, $result['userid']);
        self::assertArrayNotHasKey('mycertificates', $result);
        self::assertArrayHasKey('title', $result);
        $title = get_string('othercertificates', 'mod_certifygen', fullname($teacher));
        self::assertEquals($title, $result['title']);
        self::assertNotEquals(get_string('nothingtodisplay'), trim($result['table']));
    }
}
