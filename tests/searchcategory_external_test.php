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
use coding_exception;
use dml_exception;
use invalid_parameter_exception;
use mod_certifygen\external\searchcategory_external;
use moodle_exception;
use restricted_context_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/certificate/tests/generator/lib.php');

/**
 * Search category
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class searchcategory_external_test extends \advanced_testcase {
    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test
     *
     * @return void
     * @covers \mod_certifygen\external\searchcategory_external::searchcategory
     */
    public function test_searchcategory_nopermission(): void {

        // Create user and enrol as teacher.
        $user = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_2', 'firstname' => 'test',
            'lastname' => 'user 2', 'email' => 'test_user_2@fake.es']
        );

        // Login as user.
        $this->setUser($user);

        $name1 = 'Primaria 1';
        self::getDataGenerator()->create_category(['name' => $name1]);
        $name2 = 'primaria 2';
        self::getDataGenerator()->create_category(['name' => $name2]);
        self::getDataGenerator()->create_category(['name' => 'ESO 1']);
        $haserror = false;
        try {
            searchcategory_external::searchcategory('Prim');
        } catch (moodle_exception $e) {
            $haserror = true;
        }
        $this->assertTrue($haserror);
    }

    /**
     * Test
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     * @throws moodle_exception
     * @covers \mod_certifygen\external\searchcategory_external::searchcategory
     */
    public function test_searchcategory(): void {
        global $DB;
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create user and enrol as teacher.
        $this->getDataGenerator()->create_user(
            ['username' => 'test_user_2', 'firstname' => 'test',
            'lastname' => 'user 2', 'email' => 'test_user_2@fake.es']
        );

        $name1 = 'Primaria 1';
        $category1 = self::getDataGenerator()->create_category(['name' => $name1]);
        $name2 = 'primaria 2';
        $category2 = self::getDataGenerator()->create_category(['name' => $name2]);
        self::getDataGenerator()->create_category(['name' => 'ESO 1']);
        $result = searchcategory_external::searchcategory('Prim');

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('list', $result);
        self::assertArrayHasKey('maxusersperpage', $result);
        self::assertArrayHasKey('overflow', $result);
        self::assertCount(2, $result['list']);

        self::assertEquals(' / ' . $name1, $result['list'][$category1->id]->name);
        self::assertEquals(' / ' . $name2, $result['list'][$category2->id]->name);
    }
}
