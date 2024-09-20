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
 * @package   certifygenrepository_onedrive
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Repositorio Onedrive';
$string['pluginnamesettings'] = 'Configuración del Repositorio Onedrive';
$string['enable'] = 'Habilitar';
$string['enable_help'] = 'Este repositorio guarda los certificados en uno de los repositorios habilitados de la plataforma.';
$string['settings_folder'] = 'Carpeta';
$string['settings_folder_desc'] = 'Directorio en Onedrive done se van a almacenar los certificados';
$string['privacy:metadata'] = 'El plugin Certifygen Repositorio Onedrive no almacena datos personales.';
$string['privacy:metadata:validationid'] = 'El id de emisión';
$string['privacy:metadata:userid'] = 'Id del usuario al que pertenece el certificado';
$string['privacy:metadata:url'] = 'Enlace al certificado';
