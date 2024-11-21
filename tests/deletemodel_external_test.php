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


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/classes/external/deletemodel_external.php');

use mod_certifygen\external\deletemodel_external;
use mod_certifygen\persistents\certifygen_model;
//require_once($CFG->dirroot . '/lib/externallib.php');
/**
 * Delete model test
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deletemodel_external_test extends \advanced_testcase {
    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * test
     * @throws \invalid_parameter_exception
     * @throws \dml_exception
     */
    public function test_deletemodel(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user([
                'username' => 'test_manager_1',
                'firstname' => 'test',
                'lastname' => 'manager 1',
                'email' => 'test_manager_1@fake.es',
                ]);

        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $user->id);

        $this->setUser($user);

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            $certificate1->get_id(),
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED
        );

        // Assertion model exists.
        $this->assertTrue($model->get('id') > 0);
        $count = certifygen_model::count_records();
        $this->assertEquals(1, $count);

        // Delete model.
        $result = deletemodel_external::deletemodel($model->get('id'));
        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertTrue($result['result']);
        $this->assertEquals(get_string('ok', 'mod_certifygen'), $result['message']);
        $count = certifygen_model::count_records();
        $this->assertEquals(0, $count);
    }

    /**
     * test
     *
     * @return void
     * @throws \coding_exception
     * @throws \invalid_parameter_exception|\dml_exception
     */
    public function test_deletemodel2(): void {

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            $certificate1->get_id(),
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED
        );

        // Assertion model exists.
        $this->assertTrue($model->get('id') > 0);
        $count = certifygen_model::count_records();
        $this->assertEquals(1, $count);

        // Delete model.
        $result = deletemodel_external::deletemodel($model->get('id'));
        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertFalse($result['result']);
        $this->assertEquals(get_string('nopermissiondeletemodel', 'mod_certifygen'), $result['message']);
    }
}
