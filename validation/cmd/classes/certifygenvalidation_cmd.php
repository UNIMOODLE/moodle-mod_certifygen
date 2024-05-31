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
 * @package   certifygenvalidation_cmd
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenvalidation_cmd;

use coding_exception;
use core\invalid_persistent_exception;
use core\session\exception;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use stdClass;

class certifygenvalidation_cmd implements ICertificateValidation
{

    /**
     * @param certifygen_file $file
     * @return array
     */
    public function sendFile(certifygen_file $file): array
    {
        $path = get_config('certifygenvalidation_cmd', 'path');
        if (empty($path)) {
            throw new exception('cmdnotconfigured', 'certifygenvalidation_cmd');
        }

        // Recupera los parámetros
        $filename = escapeshellarg($file->get_file()->get_filename());
        $userid = escapeshellarg($file->get_user()->username);

        // Ruta al ejecutable (asegúrate de que la ruta sea correcta)
//        $executable = '/path/to/crear_fichero.sh';

        // Construye el comando
        $command = "$path $filename $userid";

        // Ejecuta el comando y captura la salida
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        $haserror = false;
        // Muestra la salida del comando
        if ($return_var !== 0) {
            $haserror = true;
            error_log(__FUNCTION__ . " Error ejecutando el comando. Código de salida: ".var_export($return_var, true));
        } else {
            error_log(__FUNCTION__ .  "Comando ejecutado exitosamente.".var_export($output, true));
//            foreach ($output as $line) {
//                echo $line . "\n";
//            }
        }
        return [
            'haserror' => $haserror
        ];
    }

    /**
     * @return array
     */
    public function getState(): array
    {
        // TODO: Implement getState() method.
        return [];
    }

    /**
     * @return array
     */
    public function getFile(): array
    {
        // TODO: Implement getFile() method.
        return [];
    }

    /**
     * @return bool
     */
    public function canRevoke(): bool
    {
        return false;
    }
}