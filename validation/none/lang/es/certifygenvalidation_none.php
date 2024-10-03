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
 * @package   certifygenvalidation_none
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Certifygen Sin Validación';
$string['enable'] = 'Habilitar';
$string['enable_help'] = 'Si este plugin está habilitado, puedes usarlo para validar Certificados Unimoodle a través de servicios web';
$string['certifygenvalidation_none_settings'] = 'Configuración de Certifygen Sin Validación';
$string['pluginnamesettings'] = 'Configuración de Certifygen Sin Validación';
$string['privacy:metadata'] = 'El plugin Certifygen Sin Validación no almacena ningún dato personal.';
$string['wsoutput'] = 'Salida del Servicio Web';
$string['wsoutput_help'] = 'Si es verdadero, las actividades de certifygen relacionadas con este tipo de validación serán parte de la salida del ws
get_id_instance_certificate_external. Si es verdadero, las solicitudes de los profesores con modelos con este tipo de validación serán parte de la
salida del ws get_courses_as_teacher.';
