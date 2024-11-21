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
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 *
 * @package   mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Certifygen module upgrade task
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool always true
 * @throws ddl_exception
 * @throws ddl_table_missing_exception
 * @throws downgrade_exception
 * @throws moodle_exception
 * @throws upgrade_exception
 */
function xmldb_certifygen_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024092002) {
        // Define field data to be added to certifygen_repository.
        $table = new xmldb_table('certifygen_repository');
        $field = new xmldb_field('data', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timemodified');

        // Conditionally launch add field data.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Certifygen savepoint reached.
        upgrade_mod_savepoint(true, 2024092002, 'certifygen');
    }

    if ($oldversion < 2024101100) {

        // Define field modelid to be dropped from certifygen.
        $table = new xmldb_table('certifygen');
        $key = new xmldb_key('fk_model', XMLDB_KEY_FOREIGN, ['modelid'], 'certifygen_model', ['id']);
        $dbman->drop_key($table, $key);
        $field = new xmldb_field('modelid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define table certifygen_cmodels to be created.
        $table = new xmldb_table('certifygen_cmodels');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('modelid', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('certifygenid', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('fkmodelid', XMLDB_KEY_FOREIGN, ['modelid'], 'certifygen_model', ['id']);
        $table->add_key('fkcertifygenid', XMLDB_KEY_FOREIGN, ['certifygenid'], 'certifygen', ['id']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Certifygen savepoint reached.
        upgrade_mod_savepoint(true, 2024101100, 'certifygen');
    }

    return true;
}
