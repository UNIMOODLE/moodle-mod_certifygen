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

$string['pluginname'] = 'Certifygen Sense Validació';
$string['enable'] = 'Habilitar';
$string['enable_help'] = 'Si aquest complement està habilitat, pots utilitzar-lo per validar Certificats Unimoodle mitjançant serveis web';
$string['certifygenvalidation_none_settings'] = 'Configuració de Certifygen Sense Validació';
$string['pluginnamesettings'] = 'Configuració de Certifygen Sense Validació';
$string['privacy:metadata'] = 'El complement Certifygen Sense Validació no emmagatzema cap dada personal.';
$string['wsoutput'] = 'Sortida del servei web';
$string['wsoutput_help'] = 'Si és cert, les activitats de certificació relacionades amb aquest tipus de validació formaran part de la sortida de
get_id_instance_certificate_external ws. Si és cert, les sol·licituds del professor amb models amb aquest tipus de validació formaran part de la
sortida de get_courses_as_teacher ws.';
