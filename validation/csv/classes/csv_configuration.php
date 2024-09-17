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
use dml_exception;

/**
 * csv_configuration
 *
 * @package   certifygenvalidation_csv
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csv_configuration {
    /** @var bool $pluginenabled */
    private bool $pluginenabled;
    /** @var bool $wsdlenabled */
    private bool $wsdlenabled;
    /** @var bool $querywsdlenabled */
    private bool $querywsdlenabled;
    /** @var bool $appidenabled */
    private bool $appidenabled;
    /** @var string $appid*/
    private string $appid;
    /** @var string|false|mixed|object $wsdl */
    private string $wsdl;
    /** @var string|false|mixed|object $querywsdl */
    private string $querywsdl;

    /**
     * Construct
     * @throws dml_exception
     */
    public function __construct() {
        $this->pluginenabled = get_config('certifygenvalidation_csv', 'enabled') == '1';
        $wsdl = get_config('certifygenvalidation_csv', 'firmacatalogserviceurl');
        $this->wsdlenabled = !empty($wsdl);
        $querywsdl = get_config('certifygenvalidation_csv', 'firmaquerycatalogserviceurl');
        $this->querywsdlenabled = !empty($querywsdl);
        $appid = get_config('certifygenvalidation_csv', 'appID');
        $this->appidenabled = !empty($appid);
        if ($this->is_enabled()) {
            $this->appid = $appid;
            $this->wsdl = $wsdl;
            $this->querywsdl = $querywsdl;
        }
    }

    /**
     * is enabled
     * @return bool
     */
    public function is_enabled(): bool {
        return $this->querywsdlenabled && $this->wsdlenabled && $this->appidenabled;
    }

    /**
     * ge appid
     * @return string
     */
    public function get_appid(): string {
        return $this->appid;
    }

    /**
     * Get wuerywsdl
     * @return string
     */
    public function get_querywsdl(): string {
        return $this->querywsdl;
    }

    /**
     * Get wsdl
     * @return string
     */
    public function get_wsdl(): string {
        return $this->wsdl;
    }
}
