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


namespace certifygenvalidation_csv;
global $CFG;
require_once $CFG->libdir . '/soaplib.php';

use coding_exception;
use context_course;
use core\invalid_persistent_exception;
use core\session\exception;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_validations;
use SoapFault;
use stdClass;
use stored_file;

class certifygenvalidation_csv implements ICertificateValidation
{
    private csv_configuration $configuration;

    /**
     * @throws exception
     */
    public function sendFile(certifygen_file $file): array
    {
        $haserror = false;
        $message = '';
        if (!$this->is_enabled()) {
            throw new exception('csvnotconfigured', 'certifygenvalidation_csv');
        }
        try {
            $params = $this->create_params_sendFile($file);
            $conection = new soapconnection();
            $result = $conection->call($this->configuration->get_wsdl(), 'iniciarProcesoFirma', $params);
            if (!isset($result->{'resultadoInicioProcesoFirma'})) {
                $haserror = true;
                $message = 'response_not_valid';
            } else {
                $result = $result->{'resultadoInicioProcesoFirma'};
                if (!isset($result->{'resultado'})) {
                    $haserror = true;
                    $message = 'resultado_not_exists_in_response';
                } else if ($result->{'resultado'} == 'KO') {
                    $haserror = true;
                    if (!isset($result->{'error'})) {
                        $haserror = true;
                        $message = 'error_not_exists_in_response';
                    } else {
                        $error = $result->{'error'};
                        $message = $error->{'codError'} . ' - ' . $error->{'descError'};
                    }
                } else {
                    // TODO: ok.
                }
            }
        }
        catch ( SoapFault $e ) {
            $haserror = true;
            error_log(__FUNCTION__ . ' SoapFault error: '.var_export($e->getMessage(), true));
        }
        catch (Exception $e) {
            $haserror = true;
            error_log(__FUNCTION__ . ' error: '.var_export($e->getMessage(), true));
//            $connection = new SoapFault('client', 'Could not connect to the service');
        }

        return [
            'haserror' => $haserror,
            'message' => $message,
        ];
    }

    /**
     * @param certifygen_file $file
     * @return array[]
     */
    private function create_params_sendFile(certifygen_file $file) : array {

        $token = str_replace('.pdf', '', $file->get_file()->get_filename());
        $params['asunto'] = 'prueba elena';
        // TODO: ----Valor que nos indica si hay que hacer la llamada al servicio de avisos cada vez que un firmante
        // realice alguna de las operaciones indicada en el parámetro “avisoEstadoFinFirma” dentro de la firma de un token-versión.
        $params['avisos'] = [
            'avisoFinFirmante' => 'false',
//            'avisoEstadoFinFirma' => 'RECHAZADO;FIRMADO;VISTOBUENO',// obligatorio si avisoFinFirmante true
            'avisoFinCircuito' => 'false',
//            'avisoEstadoFinCircuito' => 'RECHAZADO;FIRMADO;CADUCADO', // obligatorio si avisoFinCircuito true
            'avisoURL' => 'https://moodle410.test', // wsdl
//            'avisoURL' => 'https://moodle410.test/webservice/rest/server.php?wstoken=cd5b110ca1acdcbc28f25abf4f649738&wsfunction=mod_certifygen_notify_certification&moodlewsrestformat=json&userid=123&idinstance=2&datos=asdasd',
        ];
        //        $params['cuerpo'] = ''; // Opcional.
        $file = [
            'nombre' => $file->get_file()->get_filename(),
            'descripcion' => $file->get_file()->get_filename(),
            'datos' => $file->get_file()->get_contenthash(),
//            'datos' => utf8_encode($file->get_file()->get_contenthash()),
//            'Datos' => $file->get_file()->get_content(),
        ];
        $params['documentosFirma'] = [$file];
        $params['firmaSello'] = 'Universidad'; // ","Universidad",”Rector”,”Secretario”.
        $params['firmanTodos'] = "true"; //-Indica si el circuito es de tipo “Normal” o “Solidario”, según
        $params['idAplicacion'] = $this->configuration->get_appid();
        $params['identificador'] = [
            'token' => $token,// TODO: -Identificador de un expediente dentro de la aplicación que invoca al servicio.
            'version' => 1,// TODO: --Valor que nos indica la versión del expediente dentro de la aplicación que invoca al servicio.
        ];
        $params['remitente'] = 'Universidad de Valladolid';
        // Indica si el proceso de firma se realiza “en Cascada” o
        //“Paralelo”, según RF04. Posibles valores:
        // CASCADA: El circuito de firma es según el orden de
        //envío de los firmantes.
        // PARALELO: No importa el orden de firma.
        //-Campo de texto.
        //-Campo obligatorio.
        $params['secuenciaFirma'] = 'CASCADA';
        $params['sustituye'] = "true"; // TODO: ---Indica si los documentos de la nueva versión del token sustituyen a los de la anterior o los complementa.
        // Obligatorio si firmaSello es vacio
        // -Listado del tipo de firma separados por ‘;’ que tendrá cada
        //uno de los firmantes indicados en el parámetro “firmantes”. Se
        //indicará un ‘0’ si se quiere que el firmante firme la petición y
        //un ‘1’ si se quiere que el firmante dé el visto bueno.
        //-Si sólo llega un valor se tendrá en cuenta dicho para como
        //tipo de firma de todos los firmantes.
        //-Si llegan varios valores, el número de valores
//        $params['tipoFirma'] = '';


        // - Lista de los estados separados por el carácter ‘;’ para los
        //que el aplicativo invocante quiere que se le avise cuando el
        //firmante termina una de las operaciones indicadas dentro de
        //esta lista.
        //-Estados aceptados “RECHAZADO”, “FIRMADO”,
        //“VISTOBUENO”.
        //-Campo obligatorio si avisoFinFirma es “true”.
//        $params['avisoEstadoFinFirma'] = 'false';
        // -Valor que nos indica si hay que hacer la llamada al servicio
        //de avisos cuando se acabe la firma de un token-versión.
        //-Campo de texto.
        //-Valores "true" o "false".
//        $params['avisoFinCircuito'] = 'false';
        // - Lista de los estados separados por el carácter ‘;’ para los
        //que el aplicativo invocante quiere que se le avise si al terminar
        //el circuito de firma la petición está en alguno de ellos.
        //-Estados aceptados “RECHAZADO”, “FIRMADO”,
        //“CADUCADO”.
        //-Campo obligatorio si avisoEstadoFinCircuito es “true”.
//        $params['avisoEstadoFinCircuito'] = 'RECHAZADO;FIRMADO;CADUCADO';
        // - URL a la que invocar en el caso de que avisoFinFirma o
        //avisoFinCircuito sea true. En dicha URL deberá existir una
        //implementación de un wsdl específico.
//        $params['avisoURL'] = '';
        // -Listado de los firmantes de los documentos. Cada firmante se
        //indica mediante su Documento de identificación (NIF o NIE) o
        //el código del cargo que ocupa. Los firmantes van separados
        //por el carácter ‘;’.
        //-Campo de texto.
        //-Campo obligatorio, si firmaSello=” “.
//        $params['firmantes'] = '';
        $params = ['request' => $params];
        return ['inicioProcesoFirma' => $params];
    }

    /**
     * @param string $code
     * @return array
     */
    private function create_params_getStatus(string $code) : array {
        $identificadores = [];
        $identificadores[] = [
            'token' => $code,
            'version' => 1,
        ];
        $params = [
            'idAplicacion' => $this->configuration->get_appid(),
            'identificadores' => $identificadores,
        ];
        return ['consultaEstadoPeticion' => $params];
    }

    /**
     * @param int $courseid
     * @param int $validationid
     * @param string $code
     * @return int
     */
    public function getState(int $courseid, int $validationid, string $code): int {
        // TODO: primero llamar al serivico, y actualizar en db.
        $validation = new certifygen_validations($validationid);
        try {
            $params = $this->create_params_getStatus($code);
            $conection = new soapconnection();
            $result = $conection->call($this->configuration->get_querywsdl(), 'consultaEstadoPeticion', $params);
            if (!isset($result->{'resultadoConsultaEstadoPeticion'})) {
                $haserror = true;
                $message = 'response_not_valid';
                error_log(__FUNCTION__. ' ' . __LINE__ . ' resultadoConsultaEstadoPeticion key not exists ');
            } else {
                $result = $result->{'resultadoConsultaEstadoPeticion'};
                if (!isset($result->{'resultado'})) {
                    $haserror = true;
                    $message = 'resultado_not_exists_in_response';
                } else if ($result->{'resultado'} == 'KO') {
                    $haserror = true;
                    if (!isset($result->{'error'})) {
                        $haserror = true;
                        $message = 'error_not_exists_in_response';
                    } else {
                        $error = $result->{'error'};
                        error_log(__FUNCTION__ . ' Result error: '.var_export($error, true));
                        $message = $error->{'codError'} . ' - ' . $error->{'descError'};
                    }
                } else {
                    // TODO: ok. coger el estado y si es distinto, guardar en db.
                }
            }
        }
        catch ( SoapFault $e ) {
            $haserror = true;
            error_log(__FUNCTION__ . ' SoapFault error: '.var_export($e->getMessage(), true));
        }
        catch (Exception $e) {
            $haserror = true;
            error_log(__FUNCTION__ . ' error: '.var_export($e->getMessage(), true));
//            $connection = new SoapFault('client', 'Could not connect to the service');
        }

        return $validation->get('status');
    }

    /**
     * @param string $code
     * @return array[]
     */
    private function create_params_getFileContent(string $code) : array {
        $params = [
            'idAplicacion' => $this->configuration->get_appid(),
            'identificador' => $code,
        ];
        return ['obtenerContenidoDocumento' => $params];
    }
    /**
     * @param int $courseid
     * @param int $validationid
     * @param string $code
     * @return stored_file
     */
    public function getFile(int $courseid, int $validationid, string $code)
    {
        // Guardar en moodledata moemntaneamente.
        try {
            $params = $this->create_params_getFileContent($code);
            $conection = new soapconnection();
            $result = $conection->call($this->configuration->get_querywsdl(), 'obtenerContenidoDocumento', $params);
            if (!isset($result->{'resultadoObtenerContenidoDocumento'})) {
                $haserror = true;
                $message = 'response_not_valid';
                error_log(__FUNCTION__. ' ' . __LINE__ . ' resultadoObtenerContenidoDocumento key not exists ');
            } else {
                $result = $result->{'resultadoObtenerContenidoDocumento'};
                if (!isset($result->{'resultado'})) {
                    $haserror = true;
                    $message = 'resultado_not_exists_in_response';
                } else if ($result->{'resultado'} == 'KO') {
                    $haserror = true;
                    if (!isset($result->{'error'})) {
                        $haserror = true;
                        $message = 'error_not_exists_in_response';
                    } else {
                        $error = $result->{'error'};
                        error_log(__FUNCTION__ . ' Result error: '.var_export($error, true));
                        $message = $error->{'codError'} . ' - ' . $error->{'descError'};
                    }
                } else {
                    // TODO: ok. guardar en moodledata moemntaneamente el fichero.
                    $docs = $result->{'docsPeticion'};
                    foreach ($docs as $doc) {
                        if (!empty($result->{'documentos'})) {
                            foreach ($result->{'documentos'} as $documento) {
                                // Solo obtengo nombre y descripcion!  comprobar...
                                error_log(__FUNCTION__ . ' ' . __LINE__ . 'documento: '.var_export($documento, true));
                            }
                        }
                    }
                }
            }
        }
        catch ( SoapFault $e ) {
            $haserror = true;
            error_log(__FUNCTION__ . ' SoapFault error: '.var_export($e->getMessage(), true));
        }
        catch (Exception $e) {
            $haserror = true;
            error_log(__FUNCTION__ . ' error: '.var_export($e->getMessage(), true));
//            $connection = new SoapFault('client', 'Could not connect to the service');
        }
//        $fs = get_file_storage();
//        $contextid = context_course::instance($courseid)->id;
//        return $fs->get_file($contextid, self::FILE_COMPONENT,
//            self::FILE_AREA, $validationid, self::FILE_PATH, $code);
    }

    /**
     * @param stdClass $data
     * @return bool
     * @throws coding_exception
     */
//    public function deleteRecord(stdClass $data): bool {
//        $csv = new persistent\csv($data->modelid);
//        return $csv->delete();
//    }

    /**
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
//    public function addRecord(stdClass $data): int {
//        $csv = new persistent\csv(0, $data);
//        $csv->create();
//        return $csv->get('id');
//
//    }

    /**
     * @return bool
     */
    public function canRevoke(): bool
    {
        return true;
    }

    /**
     * @param string $code
     * @return array[]
     */
    private function create_params_revoke(string $code) : array {
        $params = [
            'idAplicacion' => $this->configuration->get_appid(),
            'identificador' => $code,
        ];
        return ['anularPeticion' => $params];
    }

    /**
     * @param string $code
     * @return array
     */
    public function revoke(string $code) : array {
        try {
            $message = '';
            $haserror = false;
            $params = $this->create_params_revoke($code);
            $conection = new soapconnection();
            $result = $conection->call($this->configuration->get_wsdl(), 'anularPeticion', $params);
            if (!isset($result->{'resultadoAnularPeticion'})) {
                $haserror = true;
                $message = 'response_not_valid';
            } else {
                $result = $result->{'resultadoAnularPeticion'};
                if (!isset($result->{'resultado'})) {
                    $haserror = true;
                    $message = 'resultado_not_exists_in_response';
                } else if ($result->{'resultado'} == 'KO') {
                    $haserror = true;
                    if (!isset($result->{'resultado'})) {
                        $haserror = true;
                        $message = 'resultado_not_exists_in_response';
                    } else {
                        $error = $result->{'error'};
                        $message = $error->{'codError'} . ' - ' . $error->{'descError'};
                    }
                }
            }
        }
        catch ( SoapFault $e ) {
            $haserror = true;
            error_log(__FUNCTION__ . ' SoapFault error: '.var_export($e->getMessage(), true));
        }
        catch (Exception $e) {
            $haserror = true;
            error_log(__FUNCTION__ . ' error: '.var_export($e->getMessage(), true));
//            $connection = new SoapFault('client', 'Could not connect to the service');
        }
        return [
            'haserror' => $haserror,
            'message' => $message,
        ];
    }
    /**
     * @param int $courseid
     * @param int $validationid
     * @param string $code
     * @return string
     */
    public function getFileUrl(int $courseid, int $validationid, string $code): string
    {
        // TODO: Implement getFileUrl() method.
        return '';
    }

    /**
     * @return bool
     */
    public function is_enabled(): bool
    {
        return false; // TODO: de moemnto no lo habilitamos.
        $this->configuration = new csv_configuration();
        return $this->configuration->is_enabled();
    }
}