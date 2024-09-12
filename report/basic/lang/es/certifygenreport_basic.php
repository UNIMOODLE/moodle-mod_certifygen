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

$string['pluginname'] = 'Basic Report';
$string['pluginnamesettings'] = 'Configuración del Informe Básico';
$string['enable'] = 'Enable';
$string['enable_help'] = 'If this plugin is enabled, you can use it to Report Unimoodle Teacher Certificates';
$string['path'] = 'Path';
$string['path_help'] = 'External service command path HELP';
$string['certifygenreport_basic_settings'] = 'Configuración del Basic Report';
$string['logo'] = 'Logo';
$string['logo_desc'] = 'Logo que aparece en los certificados de profesor';
$string['footer'] = 'Pie de página';
$string['footer_desc'] = 'Pie de página que aparece en los certificados de profesor';
$string['and'] = 'y';
$string['reporttext'] = 'Certificado de uso del Campus Virtual de la Universidad de XXXXX emitido para el profesor {$a->teacher}  >> de acuerdo con el método automático de clasificación de uso de cursos referido al final del presente documento1';
$string['courseinfo'] = 'El curso/asignatura {$a->coursename} {$a->coursedetails}, impartido por el profesor {$a->teachers}, es de tipo {$a->type}.';
$string['courseinfopl'] = 'El curso/asignatura {$a->coursename} {$a->coursedetails}, impartido por los profesores {$a->teachers}, es de tipo {$a->type}.';
$string['coursetypedesc'] = 'Si TIPO=Inactivo >>“Se detecta un uso bajo del Campus Virtual por parte de los profesores y/o los alumnos. Se recomienda aumentar el uso del Campus Virtual, con la incorporación adicional de recursos que los alumnos consulten y/o actividades en las que participen de forma más activa.”<br> 
Si TIPO=Con entregas >>“Se emplea el Campus Virtual fundamentalmente para canalizar la entrega de tareas y como repositorio. Se recomienda aprovechar más los mecanismos de realimentación y el libro de calificaciones del campus virtual, para mejorar la comunicación con los alumnos orientada a la evaluación formativa, así como valorar una mayor incorporación de actividades participativas.”<br> 
Si TIPO= Repositorio >>“Se emplea el Campus Virtual fundamentalmente como repositorio. Se recomienda aprovechar mejor los módulos de actividades del campus virtual, para canalizar la entrega de tareas y mejorar los mecanismos comunicación con los alumnos.”';
$string['cdetail_1'] = 'perteneciente a la categoría {$a->name}';
$string['cdetail_2'] = 'con fecha de inicio en el Campus Virtual {$a->date}';
$string['cdetail_3'] = 'con fecha de cierre en el Campus Virtual {$a->date}';
$string['cannotusealgorith_nostudents'] = 'There are no students in course';
$string['privacy:metadata'] = 'El plugin Basic Report no almacena datos personales.';
