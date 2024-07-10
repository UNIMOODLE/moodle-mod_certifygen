<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package   certifygenvalidation_csv
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'CSV Validation';
$string['enable'] = 'Enable';
$string['enable_help'] = 'If this plugin is enabled, you can use it to validate Unimoodle Certificates';
$string['firmacatalogservicewsdl'] = 'FirmaCatalogService Wsdl';
$string['firmacatalogservicewsdl_help'] = 'This is the FirmaCatalogService url to obtain the wsdl. <span class="bold">It is required in all the requests.</span>';
$string['firmaquerycatalogservicewsdl'] = 'FirmaQueryCatalogService Wsdl';
$string['firmaquerycatalogservicewsdl_help'] = 'This is the FirmaQueryCatalogService url to obtain the wsdl. <span class="bold">It is required in all the requests.</span>';
$string['appID'] = 'Application ID';
$string['appID_help'] = 'This is the application identifier. <span class="bold">It is required in all the requests.</span>';
$string['certifygenvalidation_csv_settings'] = 'CSV settings';
$string['csvnotconfigured'] = 'CSV not configured';
$string['pluginnamesettings'] = 'CSV Validation Configuration';
