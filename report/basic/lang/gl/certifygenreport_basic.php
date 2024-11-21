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

$string['pluginname'] = 'Informe Básico';
$string['pluginnamesettings'] = 'Configuración do Informe Básico';
$string['enable'] = 'Activar';
$string['enable_help'] = 'Se este complemento está activado, poderá usalo para informar sobre os Certificados dos Profesores de Unimoodle';
$string['path'] = 'Camiño';
$string['path_help'] = 'Camiño do comando do servizo externo HELP';
$string['certifygenreport_basic_settings'] = 'Configuración do Informe Básico';
$string['logo'] = 'Logotipo';
$string['logo_desc'] = 'Logotipo que aparece nos certificados de profesor';
$string['footer'] = 'Pe de páxina';
$string['footer_desc'] = 'Pe de páxina que aparece nos certificados de profesor';
$string['and'] = 'e';
$string['reporttext'] = 'Certificado de uso do Campus Virtual da Universidade de XXXXX emitido para o profesor {$a->teacher}  >> de acordo co método automático de clasificación de uso de cursos referido ao final deste documento1';
$string['courseinfo'] = 'O curso/asignatura {$a->coursename} {$a->coursedetails}, impartido polo profesor {$a->teachers}, é do tipo {$a->type}.';
$string['courseinfopl'] = 'O curso/asignatura {$a->coursename} {$a->coursedetails}, impartido polos profesores {$a->teachers}, é do tipo {$a->type}.';
$string['coursetypedesc'] = 'Se TIPO=Inactivo >>“Detectase un uso baixo do Campus Virtual por parte dos profesores e/ou alumnos. Recoméndase aumentar o uso do Campus Virtual, incorporando recursos adicionais que os alumnos consulten e/ou actividades nas que participen de forma máis activa.”<br>
Se TIPO=Con entregas >>“O Campus Virtual úsase principalmente para canalizar a entrega de tarefas e como repositorio. Recoméndase aproveitar mellor os mecanismos de retroalimentación e o libro de notas do campus virtual, para mellorar a comunicación cos alumnos orientada á avaliación formativa, así como valorar unha maior incorporación de actividades participativas.”<br>
Se TIPO= Repositorio >>“O Campus Virtual úsase principalmente como repositorio. Recoméndase mellorar a utilización dos módulos de actividades do campus virtual, para canalizar a entrega de tarefas e mellorar os mecanismos de comunicación cos alumnos.”';
$string['cdetail_1'] = 'pertenecente á categoría {$a->name}';
$string['cdetail_2'] = 'con data de inicio no Campus Virtual {$a->date}';
$string['cdetail_3'] = 'con data de peche no Campus Virtual {$a->date}';
$string['cannotusealgorith_nostudents'] = 'Non hai alumnos no curso';
$string['privacy:metadata'] = 'O complemento Informe Básico non almacena datos persoais.';
