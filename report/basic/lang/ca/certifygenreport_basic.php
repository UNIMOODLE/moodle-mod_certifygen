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
 * @package   certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Informe Bàsic';
$string['pluginnamesettings'] = 'Configuració de l\'Informe Bàsic';
$string['enable'] = 'Habilitar';
$string['enable_help'] = 'Si aquest complement està habilitat, podràs utilitzar-lo per informar sobre els Certificats dels Professors de Unimoodle';
$string['path'] = 'Camí';
$string['path_help'] = 'Camí del comandament del servei extern HELP';
$string['certifygenreport_basic_settings'] = 'Configuració de l\'Informe Bàsic';
$string['logo'] = 'Logotip';
$string['logo_desc'] = 'Logotip que apareix als certificats de professor';
$string['footer'] = 'Peu de pàgina';
$string['footer_desc'] = 'Peu de pàgina que apareix als certificats de professor';
$string['and'] = 'i';
$string['reporttext'] = 'Certificat d\'ús del Campus Virtual de la Universitat de XXXXX emès per al professor {$a->teacher}  >> d\'acord amb el mètode automàtic de classificació d\'ús dels cursos referit al final d\'aquest document1';
$string['courseinfo'] = 'El curs/assignatura {$a->coursename} {$a->coursedetails}, impartit pel professor {$a->teachers}, és de tipus {$a->type}.';
$string['courseinfopl'] = 'El curs/assignatura {$a->coursename} {$a->coursedetails}, impartit pels professors {$a->teachers}, és de tipus {$a->type}.';
$string['coursetypedesc'] = 'Si TIPO=Inactiu >>“Es detecta un ús baix del Campus Virtual per part dels professors i/o alumnes. Es recomana augmentar l\'ús del Campus Virtual, incorporant recursos addicionals que els alumnes puguin consultar i/o activitats en què participin de manera més activa.”<br>
Si TIPO=Amb lliuraments >>“El Campus Virtual s\'utilitza principalment per canalitzar la lliurament d\'assignatures i com a repositori. Es recomana aprofitar millor els mecanismes de retroalimentació i el llibre de notes del campus virtual, per millorar la comunicació amb els alumnes orientada a l\'avaluació formativa, així com valorar una major incorporació d\'activitats participatives.”<br>
Si TIPO= Repositori >>“El Campus Virtual s\'utilitza principalment com a repositori. Es recomana millorar l\'ús dels mòduls d\'activitats del campus virtual, per canalitzar la lliurament d\'assignatures i millorar els mecanismes de comunicació amb els alumnes.”';
$string['cdetail_1'] = 'pertenent a la categoria {$a->name}';
$string['cdetail_2'] = 'amb data d\'inici al Campus Virtual {$a->date}';
$string['cdetail_3'] = 'amb data de tancament al Campus Virtual {$a->date}';
$string['cannotusealgorith_nostudents'] = 'No hi ha alumnes en el curs';
$string['privacy:metadata'] = 'El complement Informe Bàsic no emmagatzema dades personals.';
