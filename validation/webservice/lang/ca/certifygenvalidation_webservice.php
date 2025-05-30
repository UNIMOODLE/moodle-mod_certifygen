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
 * Ca lang strings
 * @package   certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Validació WEBSERVICE';
$string['enable'] = 'Habilitar';
$string['enable_help'] = 'Si aquest complement està habilitat, pots utilitzar-lo per validar Certificats Unimoodle mitjançant serveis web';
$string['certifygenvalidation_webservice_settings'] = 'Configuració de WEBSERVICE';
$string['certificate_not_emited'] = 'Certificat no emès';
$string['webservicenotconfigured'] = 'Certifygen WEBSERVICE no configurat';
$string['pluginnamesettings'] = 'Configuració de Validació WEBSERVICE';
$string['privacy:metadata'] = 'El complement de Validació WEBSERVICE no emmagatzema cap dada personal.';
$string['inprogress_msg'] = 'Accedeix a l’administració de la teva universitat per continuar amb el procés de validació del certificat.';
$string['invalidcourses'] = 'Instància no vàlida';
$string['invalidinstanceid'] = 'Instància no vàlida';
$string['invalidmodelid'] = 'Model no vàlid';
$string['invaliduser'] = 'Usuari no vàlid';
$string['request_not_found'] = 'Request not found';
$string['request_status_not_accepted'] = 'Estat no permès';
$string['request_user_not_matched'] = 'Aquesta no és la sol·licitud de l\'usuari';
$string['repositoryplugin_not_accepted'] = 'Connector de repositori no acceptat';
$string['teacherrequest_pdf_error'] = 'There was a problem getting certificate';
$string['validationplugin_not_accepted'] = 'Connector de validació no acceptat';
$string['wsoutput'] = 'Sortida del servei web';
$string['wsoutput_help'] = 'Si és cert, les activitats de certificació relacionades amb aquest tipus de validació formaran part de la sortida de
get_id_instance_certificate_external ws. Si és cert, les sol·licituds del professor amb models amb aquest tipus de validació formaran part de la
sortida de get_courses_as_teacher ws.';
