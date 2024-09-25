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
 * @package   certifygenvalidation_cmd
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Validació CMD';
$string['enable'] = 'Habilitar';
$string['enable_help'] = 'Si aquest complement està habilitat, pots utilitzar-lo per validar Certificats Unimoodle';
$string['path'] = 'Ruta';
$string['path_help'] = 'Ruta al fitxer que valida els certificats';
$string['certifygenvalidation_cmd_settings'] = 'Configuració del CMD';
$string['cmdnotconfigured'] = 'CMD no configurat';
$string['pluginnamesettings'] = 'Configuració de la Validació CMD';
$string['privacy:metadata'] = 'El plugin de Validació CMD no emmagatzema cap dada personal.';
$string['originalfilespath'] = 'Ruta d\'Arxius Originals';
$string['originalfilespath_help'] = 'Ruta al servidor per allotjar els certificats originals';
$string['validatedfilespath'] = 'Ruta d\'Arxius Validats';
$string['validatedfilespath_help'] = 'Ruta al servidor per allotjar els certificats validats';
$string['temp_file_not_exists'] = 'temp_file_not_exists';
$string['missing_directory_permissions'] = 'missing_directory_permissions';
$string['error_cmd_code'] = 'Error ejecutando el comando. Código de salida: {$a}';
$string['path_not_exists'] = 'El camí no existeix';
