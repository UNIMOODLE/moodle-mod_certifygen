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
use mod_certifygen\external\get_id_instance_certificate_external;
use mod_certifygen\persistents\certifygen_model;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot . '/lib/externallib.php');
/**
 * Get id instance certificate test
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_id_instance_certificate_external_test extends advanced_testcase {
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
    public function test_get_id_instance_certificate_nopermission(): void {

        // Create user.
        $user1 = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                ]);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as student.
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');
        self::setUser($user1);

        // Tests: Course with no mod_certifygen included.
        $result = get_id_instance_certificate_external::get_id_instance_certificate($user1->id, '', '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('nopermissiontogetcourses', $result['error']['code']);
        $this->assertEquals(get_string('nopermissiontogetcourses', 'mod_certifygen'), $result['error']['message']);
    }

    /**
     * Test
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public function test_get_id_instance_certificate(): void {
        global $DB;

        // Create user.
        $user1 = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                ]);
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as student.
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');

        // Tests: Course with no mod_certifygen included.
        $result = get_id_instance_certificate_external::get_id_instance_certificate($user1->id, '', '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('instances', $result);
        $this->assertIsArray($result['instances']);
        $this->assertEmpty($result['instances']);

        // Create mod_certifygen.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_ACTIVITY,
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];
        $datamodule = [
            'name' => 'Test 1,',
            'course' => $course1->id,
            'modelid' => $model->get('id'),
            'instance' => 0,
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course1->id, false, MUST_EXIST);

        // Tests: Course with no mod_certifygen included.
        $result = get_id_instance_certificate_external::get_id_instance_certificate($user1->id, '', '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('instances', $result);
        $this->assertIsArray($result['instances']);
        $this->assertCount(1, $result['instances']);
        $this->assertIsArray($result['instances'][0]);
        $this->assertArrayHasKey('course', $result['instances'][0]);
        $this->assertIsArray($result['instances'][0]['course']);
        $this->assertArrayHasKey('id', $result['instances'][0]['course']);
        $this->assertArrayHasKey('shortname', $result['instances'][0]['course']);
        $this->assertArrayHasKey('fullname', $result['instances'][0]['course']);
        $this->assertArrayHasKey('categoryid', $result['instances'][0]['course']);
        $this->assertEquals($course1->id, $result['instances'][0]['course']['id']);

        // Filter to return course names in $lang language.
        $filter = new certifygenfilter(context_system::instance(), [], $lang);
        $coursefullname = $filter->filter($course1->fullname);
        $coursefullname = strip_tags($coursefullname);
        $courseshortname = $filter->filter($course1->shortname);
        $courseshortname = strip_tags($courseshortname);
        $this->assertEquals($courseshortname, $result['instances'][0]['course']['shortname']);
        $this->assertEquals($coursefullname, $result['instances'][0]['course']['fullname']);
        $this->assertEquals($course1->category, $result['instances'][0]['course']['categoryid']);
        $this->assertArrayHasKey('instance', $result['instances'][0]);
        $this->assertIsArray($result['instances'][0]['instance']);
        $this->assertArrayHasKey('id', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('name', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modelname', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modelmode', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modeltimeondemmand', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modeltype', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modeltemplateid', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modellangs', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modelvalidation', $result['instances'][0]['instance']);
        $this->assertEquals($cm->instance, $result['instances'][0]['instance']['id']);
        $this->assertEquals($modcertifygen->name, $result['instances'][0]['instance']['name']);
        $this->assertEquals($model->get('name'), $result['instances'][0]['instance']['modelname']);
        $this->assertEquals($model->get('mode'), $result['instances'][0]['instance']['modelmode']);
        $this->assertEquals($model->get('timeondemmand'), $result['instances'][0]['instance']['modeltimeondemmand']);
        $this->assertEquals($model->get('type'), $result['instances'][0]['instance']['modeltype']);
        $this->assertEquals($model->get('templateid'), $result['instances'][0]['instance']['modeltemplateid']);
        $this->assertEquals($model->get('langs'), $result['instances'][0]['instance']['modellangs']);
        $this->assertEquals($model->get('validation'), $result['instances'][0]['instance']['modelvalidation']);
    }

    /**
     * Test
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public function test_get_id_instance_certificate_by_userfield(): void {
        global $DB;
        // Create user profile fields.
        $category = self::getDataGenerator()->create_custom_profile_field_category(['name' => 'Category 1']);
        $field = self::getDataGenerator()->create_custom_profile_field(
            ['shortname' => 'DNI',
                'name' => 'DNI',
                'categoryid' => $category->id,
                'required' => 1, 'visible' => 1,
                'locked' => 0,
                'datatype' => 'text',
                'defaultdata' => null,
            ]
        );

        // Configure the platform.
        set_config('userfield', 'profile_' . $field->id, 'mod_certifygen');

        // Create user.
        $dni = '123456789P';
        $user1 = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test', 'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
            'profile_field_DNI' => $dni]
        );
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as student.
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');

        // Tests: Course with no mod_certifygen included.
        $result = get_id_instance_certificate_external::get_id_instance_certificate(0, $dni, '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('instances', $result);
        $this->assertIsArray($result['instances']);
        $this->assertEmpty($result['instances']);

        // Create mod_certifygen.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_ACTIVITY,
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];
        $datamodule = [
            'name' => 'Test 1,',
            'course' => $course1->id,
            'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course1->id, false, MUST_EXIST);

        // Tests: Course with no mod_certifygen included.
        $result = get_id_instance_certificate_external::get_id_instance_certificate($user1->id, '', '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('instances', $result);
        $this->assertIsArray($result['instances']);
        $this->assertCount(1, $result['instances']);
        $this->assertIsArray($result['instances'][0]);
        $this->assertArrayHasKey('course', $result['instances'][0]);
        $this->assertIsArray($result['instances'][0]['course']);
        $this->assertArrayHasKey('id', $result['instances'][0]['course']);
        $this->assertArrayHasKey('shortname', $result['instances'][0]['course']);
        $this->assertArrayHasKey('fullname', $result['instances'][0]['course']);
        $this->assertArrayHasKey('categoryid', $result['instances'][0]['course']);
        $this->assertEquals($course1->id, $result['instances'][0]['course']['id']);
        // Filter to return course names in $lang language.
        $filter = new certifygenfilter(context_system::instance(), [], $lang);
        $coursefullname = $filter->filter($course1->fullname);
        $coursefullname = strip_tags($coursefullname);
        $courseshortname = $filter->filter($course1->shortname);
        $courseshortname = strip_tags($courseshortname);
        $this->assertEquals($courseshortname, $result['instances'][0]['course']['shortname']);
        $this->assertEquals($coursefullname, $result['instances'][0]['course']['fullname']);
        $this->assertEquals($course1->category, $result['instances'][0]['course']['categoryid']);
        $this->assertArrayHasKey('instance', $result['instances'][0]);
        $this->assertIsArray($result['instances'][0]['instance']);
        $this->assertArrayHasKey('id', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('name', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modelname', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modelmode', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modeltimeondemmand', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modeltype', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modeltemplateid', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modellangs', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modelvalidation', $result['instances'][0]['instance']);
        $this->assertEquals($cm->instance, $result['instances'][0]['instance']['id']);
        $this->assertEquals($modcertifygen->name, $result['instances'][0]['instance']['name']);
        $this->assertEquals($model->get('name'), $result['instances'][0]['instance']['modelname']);
        $this->assertEquals($model->get('mode'), $result['instances'][0]['instance']['modelmode']);
        $this->assertEquals($model->get('timeondemmand'), $result['instances'][0]['instance']['modeltimeondemmand']);
        $this->assertEquals($model->get('type'), $result['instances'][0]['instance']['modeltype']);
        $this->assertEquals($model->get('templateid'), $result['instances'][0]['instance']['modeltemplateid']);
        $this->assertEquals($model->get('langs'), $result['instances'][0]['instance']['modellangs']);
        $this->assertEquals($model->get('validation'), $result['instances'][0]['instance']['modelvalidation']);

        // Tests: userid not corresponds with user dni.
        $result = get_id_instance_certificate_external::get_id_instance_certificate($user1->id - 1, $dni, '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertEquals('userfield_and_userid_sent', $result['error']['code']);
    }

    /**
     * Test
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public function test_get_id_instance_certificate_by_lang(): void {
        global $CFG, $DB;

        // Add the multilang filter. Make sure it's enabled globally.
        $CFG->filterall = true;
        $CFG->stringfilters = 'multilang';
        filter_set_global_state('multilang', TEXTFILTER_ON);

        // Create user profile fields.
        $category = self::getDataGenerator()->create_custom_profile_field_category(['name' => 'Category 1']);
        $field = self::getDataGenerator()->create_custom_profile_field(
            ['shortname' => 'DNI',
                'name' => 'DNI',
                'categoryid' => $category->id,
            'required' => 1, 'visible' => 1, 'locked' => 0, 'datatype' => 'text', 'defaultdata' => null]
        );

        // Configure the platform.
        set_config('userfield', 'profile_' . $field->id, 'mod_certifygen');

        // Create user.
        $dni = '123456789P';
        $user1 = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test', 'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
            'profile_field_DNI' => $dni]
        );
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create courses.
        $spanishname = 'Titulo en castellano';
        $englishname = 'Titulo en ingles';
        $data = [
            'fullname' => '<span lang="es" class="multilang">' . $spanishname
                . '</span><span lang="en" class="multilang">' . $englishname . '</span>',
        ];
        $course1 = self::getDataGenerator()->create_course($data);
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as student.
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');

        // Tests: Course with no mod_certifygen included.
        $result = get_id_instance_certificate_external::get_id_instance_certificate(0, $dni, '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('instances', $result);
        $this->assertIsArray($result['instances']);
        $this->assertEmpty($result['instances']);

        // Create mod_certifygen.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_ACTIVITY,
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];
        $datamodule = [
            'name' => 'Test 1,',
            'course' => $course1->id,
            'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course1->id, false, MUST_EXIST);

        // Tests: Course with no mod_certifygen included.
        $result = get_id_instance_certificate_external::get_id_instance_certificate($user1->id, '', '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('instances', $result);
        $this->assertIsArray($result['instances']);
        $this->assertCount(1, $result['instances']);
        $this->assertIsArray($result['instances'][0]);
        $this->assertArrayHasKey('course', $result['instances'][0]);
        $this->assertIsArray($result['instances'][0]['course']);
        $this->assertArrayHasKey('id', $result['instances'][0]['course']);
        $this->assertArrayHasKey('shortname', $result['instances'][0]['course']);
        $this->assertArrayHasKey('fullname', $result['instances'][0]['course']);
        $this->assertArrayHasKey('categoryid', $result['instances'][0]['course']);
        $this->assertEquals($course1->id, $result['instances'][0]['course']['id']);
        // Filter to return course names in $lang language.
        $filter = new certifygenfilter(context_system::instance(), [], $lang);
        $courseshortname = $filter->filter($course1->shortname);
        $courseshortname = strip_tags($courseshortname);
        $this->assertEquals($courseshortname, $result['instances'][0]['course']['shortname']);
        $this->assertEquals($englishname, $result['instances'][0]['course']['fullname']);
        $this->assertEquals($course1->category, $result['instances'][0]['course']['categoryid']);
        $this->assertArrayHasKey('instance', $result['instances'][0]);
        $this->assertIsArray($result['instances'][0]['instance']);
        $this->assertArrayHasKey('id', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('name', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modelname', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modelmode', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modeltimeondemmand', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modeltype', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modeltemplateid', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modellangs', $result['instances'][0]['instance']);
        $this->assertArrayHasKey('modelvalidation', $result['instances'][0]['instance']);
        $this->assertEquals($cm->instance, $result['instances'][0]['instance']['id']);
        $this->assertEquals($modcertifygen->name, $result['instances'][0]['instance']['name']);
        $this->assertEquals($model->get('name'), $result['instances'][0]['instance']['modelname']);
        $this->assertEquals($model->get('mode'), $result['instances'][0]['instance']['modelmode']);
        $this->assertEquals($model->get('timeondemmand'), $result['instances'][0]['instance']['modeltimeondemmand']);
        $this->assertEquals($model->get('type'), $result['instances'][0]['instance']['modeltype']);
        $this->assertEquals($model->get('templateid'), $result['instances'][0]['instance']['modeltemplateid']);
        $this->assertEquals($model->get('langs'), $result['instances'][0]['instance']['modellangs']);
        $this->assertEquals($model->get('validation'), $result['instances'][0]['instance']['modelvalidation']);

        // Tests: userid not corresponds with user dni.
        $result = get_id_instance_certificate_external::get_id_instance_certificate($user1->id - 1, $dni, '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertEquals('userfield_and_userid_sent', $result['error']['code']);
    }
}
