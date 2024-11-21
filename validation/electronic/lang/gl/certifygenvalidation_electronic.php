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
 * Gl lang strings
 * @package   certifygenvalidation_electronic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Validación ELECTRÓNICA';
$string['enable'] = 'Habilitar';
$string['enable_help'] = 'Se este plugin está habilitado, podes usalo para validar Certificados Unimoodle';
$string['pluginnamesettings'] = 'Configuración da Validación ELECTRÓNICA';
$string['path'] = 'Ruta do Servidor de Certificados';
$string['pathdesc'] = 'Este campo é obrigatorio para usar este plugin de validación';
$string['password'] = 'Contrasinal do Servidor de Certificados';
$string['passworddesc'] = 'Este campo é obrigatorio para usar este plugin de validación';
$string['name'] = 'Nome do Certificado';
$string['namedesc'] = 'Campo requirido para firmar o certificado';
$string['location'] = 'Ubicación do Certificado';
$string['locationdesc'] = 'Campo requirido para firmar o certificado';
$string['reason'] = 'Razón do Certificado';
$string['reasondesc'] = 'Campo requirido para firmar o certificado';
$string['contactinfo'] = 'Información de contacto do Certificado';
$string['contactinfodesc'] = 'Campo requirido para firmar o certificado';
$string['privacy:metadata'] = 'O plugin de Validación ELECTRÓNICA non almacena ningún dato persoal.';
$string['wsoutput'] = 'Saída do servizo web';
$string['wsoutput_help'] = 'Se é verdade, as actividades de certificación relacionadas con este tipo de validación formarán parte da saída de
get_id_instance_certificate_external ws. De ser certo, as solicitudes do profesor con modelos con este tipo de validación formarán parte do
saída de get_courses_as_teacher ws.';
