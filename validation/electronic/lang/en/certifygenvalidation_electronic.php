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
 * En lang strings
 * @package   certifygenvalidation_electronic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ELECTRONIC Validation';
$string['enable'] = 'Enable';
$string['enable_help'] = 'If this plugin is enabled, you can use it to validate Unimoodle Certificates';
$string['pluginnamesettings'] = 'ELECTRONIC Validation Configuration';
$string['path'] = 'Certificate Server Path';
$string['pathdesc'] = 'This field is mandatory to use this validation plugin';
$string['password'] = 'Certificate Server Password';
$string['passworddesc'] = 'This field is mandatory to use this validation plugin';
$string['name'] = 'Certificate Name';
$string['namedesc'] = 'Field required to sign the certificate';
$string['location'] = 'Certificate location';
$string['locationdesc'] = 'Field required to sign the certificate';
$string['reason'] = 'Certificate reason';
$string['reasondesc'] = 'Field required to sign the certificate';
$string['contactinfo'] = 'Certificate contact infornation';
$string['contactinfodesc'] = 'Field required to sign the certificate';
$string['privacy:metadata'] = 'The ELECTRONIC Validation plugin does not store any personal data.';
$string['wsoutput'] = 'Web Service Output';
$string['wsoutput_help'] = 'If true, the certifygen activities related with this type of validation will be part of the output of
get_id_instance_certificate_external ws. If true, the teacher requests with models with this type of validation will be part of the
output of get_courses_as_teacher ws.';
