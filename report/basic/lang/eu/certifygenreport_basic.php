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

$string['pluginname'] = 'Oinarrizko Txostena';
$string['pluginnamesettings'] = 'Oinarrizko Txostenaren Konfigurazioa';
$string['enable'] = 'Gaitu';
$string['enable_help'] = 'Plugin hau gaituta dagoenean, Unimoodle irakasleen ziurtagiriak txostentzeko erabil dezakezu';
$string['path'] = 'Bidea';
$string['path_help'] = 'Kanpoko zerbitzuaren aginduaren bidea HELP';
$string['certifygenreport_basic_settings'] = 'Oinarrizko Txostenaren Konfigurazioa';
$string['logo'] = 'Logoa';
$string['logo_desc'] = 'Irakasleen ziurtagirietan agertzen den logoa';
$string['footer'] = 'Oineko testua';
$string['footer_desc'] = 'Irakasleen ziurtagirietan agertzen den oineko testua';
$string['and'] = 'eta';
$string['reporttext'] = 'XXXXX Unibertsitateko Campus Birtualaren erabileraren ziurtagiria, {$a->teacher} irakaslearentzat emana >> dokumentu honen amaieran aipatutako ikastaroen erabileraren automatikoaren sailkapen metodoaren arabera';
$string['courseinfo'] = '{$a->coursename} {$a->coursedetails} izeneko ikastaroa/gaia, {$a->teachers} irakaslearen eskutik, {$a->type} motakoa da.';
$string['courseinfopl'] = '{$a->coursename} {$a->coursedetails} izeneko ikastaroa/gaia, {$a->teachers} irakasleen eskutik, {$a->type} motakoa da.';
$string['coursetypedesc'] = 'TIPO=Inaktiboa bada >>“Irakasleen eta/edo ikasleen Campus Birtualaren erabilera baxua detektatzen da. Campus Birtualaren erabilera handitzea gomendatzen da, ikasleek kontsultatu ditzaketen baliabide gehigarri eta/edo parte-hartze aktiboagoa duten jarduerak barne hartuz.”<br>
TIPO=Entrega batzuekin bada >>“Campus Birtuala, batez ere, etengabeen entregak kanalizatzeko eta biltegi gisa erabiltzen da. Hobetu beharko litzateke feedback mekanismoak eta campus birtualeko kalifikazio-liburua erabiltzea, ikasleekin ebaluazio formatiboaren orientatutako komunikazioa hobetzeko, eta jarduera parte-hartzaile gehiago barne hartzeko baloratu beharko litzateke.”<br>
TIPO=Biltegia bada >>“Campus Birtuala, batez ere, biltegi gisa erabiltzen da. Hobetu beharko litzateke campus birtualeko jarduera-moduluak erabiltzea, etengabeen entregak kanalizatzeko eta ikasleekin komunikazio mekanismoak hobetzeko.”';
$string['cdetail_1'] = '{$a->name} kategoria lotutako';
$string['cdetail_2'] = 'Campus Birtualean hasi den data {$a->date}';
$string['cdetail_3'] = 'Campus Birtualean amaitu den data {$a->date}';
$string['cannotusealgorith_nostudents'] = 'Ikaslerik ez dago ikastaroan';
$string['privacy:metadata'] = 'Oinarrizko Txosten pluginak ez ditu datu pertsonalak gordetzen.';
