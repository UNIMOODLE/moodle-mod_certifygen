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
 * @package   certifygenvalidation_csv
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenvalidation_csv;

use SoapClient;
/**
 * soapconnection
 * @package   certifygenvalidation_csv
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class soapconnection {
    /** @var array $options */
    private $options = [];

    /**
     * Constrcut
     */
    public function __construct() {
        $this->options = [
            "style" => SOAP_RPC,
            "use" => SOAP_ENCODED,
            "soap_version" => SOAP_1_1,
            "connection_timeout" => 15,
            "trace" => true,
            "encoding" => "UTF-8",
            "exceptions" => true,
        ];
    }

    /**
     * call
     * @param string $wsdl
     * @param string $function
     * @param array $params
     * @return mixed
     * @throws \SoapFault
     */
    public function call(string $wsdl, string $function, array $params) {
        $client = new SoapClient($wsdl, $this->options);
        return $client->__soapCall('iniciarProcesoFirma', $params);
    }
}
