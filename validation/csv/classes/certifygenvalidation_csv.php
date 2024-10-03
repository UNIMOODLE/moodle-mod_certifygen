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

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->libdir . '/soaplib.php');
require_once($CFG->libdir . '/pdflib.php');

use certifygenvalidation_csv\persistents\certifygenvalidationcsv;
use coding_exception;
use context_course;
use context_system;
use core\session\exception;
use dml_exception;
use file_exception;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use moodle_url;
use SoapFault;
use stored_file;
use stored_file_creation_exception;
/**
 * CSV
 * @package   certifygenvalidation_csv
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenvalidation_csv implements ICertificateValidation {
    /** @var csv_configuration $configuration */
    private csv_configuration $configuration;

    /**
     * Construct
     */
    public function __construct() {
        $this->configuration = new csv_configuration();
    }


    /**
     * Send file
     * @param certifygen_file $file
     * @return array
     */
    public function send_file(certifygen_file $file): array {
        global $USER;

        try {
            $params = $this->create_params_send_file($file);
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->configuration->get_wsdl(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/xml',
                ],
            ]);
            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                return [
                    'haserror' => true,
                    'message' => curl_error($curl),
                ];
            }
            $xml = simplexml_load_string(
                $response,
                null,
                null,
                'http://schemas.xmlsoap.org/soap/envelope/'
            );
            $ns = $xml->getNamespaces(true);
            $soap = $xml->children($ns['soap']);
            $res = $soap->Body->children($ns['ns2']);
            if (is_null($res)) {
                return [
                    'haserror' => true,
                    'message' => get_string('csv_result_not_expected', 'certifygenvalidation_csv'),
                ];
            }
            $iniciarprocesofirmaresponse = $res->iniciarProcesoFirmaResponse->children();
            $iniciarprocesofirmaresponsechildren = $iniciarprocesofirmaresponse->children();
            $resultado = (string) $iniciarprocesofirmaresponsechildren->resultado;
            if ($resultado === 'KO') {
                $codeerror = (string) $iniciarprocesofirmaresponsechildren->error->children()->codError;
                $descerror = (string) $iniciarprocesofirmaresponsechildren->error->children()->descError;
                return [
                    'haserror' => true,
                    'message' => $codeerror . ' - ' . $descerror,
                ];
            }
            // Se obtiene idExpediente.
            $idexpediente = (string) $iniciarprocesofirmaresponsechildren->idExpediente;
            $validationid = $file->get_validationid();
            $token = str_replace('.pdf', '', $file->get_file()->get_filename());
            $data = [
                'validationid' => $validationid,
                'applicationid' => $idexpediente,
                'token' => $token,
                'usermodified' => $USER->id,
            ];
            $cv = new certifygenvalidationcsv(0, (object)$data);
            $cv->save();
            curl_close($curl);

            return [
                'haserror' => false,
                'message' => get_string('ok', 'mod_certifygen'),
            ];
        } catch (\Exception $e) {
            return [
                'haserror' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send file params
     * @param certifygen_file $file
     * @return string
     */
    private function create_params_send_file(certifygen_file $file): string {

        $token = str_replace('.pdf', '', $file->get_file()->get_filename());
        $avisourl = (new moodle_url('/'))->out();
        $base64 = base64_encode($file->get_file()->get_content());
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
xmlns:fir="http://firma.ws.producto.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <fir:iniciarProcesoFirma>
         <!--Optional:-->
         <request>
            <!--Optional:-->
            <asunto>Peticion certificado</asunto>
            <!--Optional:-->
            <avisos>
               <!--Optional:-->
               <avisoEstadoFinCircuito>prueba elena</avisoEstadoFinCircuito>
               <!--Optional:-->
               <avisoFinCircuito>false</avisoFinCircuito>
               <avisoFinFirmante>false</avisoFinFirmante>
               <!--Optional:-->
               <avisoURL>' . $avisourl . '</avisoURL>
            </avisos>
            <!--Optional:-->
            <!--Zero or more repetitions:-->
            <documentosFirma>
               <!--Optional:-->
               <descripcion>' . $token . '</descripcion>
               <!--Optional:-->
               <nombre>' . $file->get_file()->get_filename() . '</nombre>
               <!--Optional:-->
               <datos>' . $base64 . '</datos>
            </documentosFirma>
            <!--Zero or more repetitions:-->
            <!--Optional:-->
            <firmaSello>UNIVERSIDAD</firmaSello>
            <firmanTodos>true</firmanTodos>
            <!--Optional:-->
            <idAplicacion>MOODLE</idAplicacion>
            <!--Optional:-->
            <identificador>
               <!--Optional:-->               
               <token>' . $token . '</token>
               <version>1</version>
            </identificador>
            <!--Optional:-->
            <remitente>Universidad de Valladolid</remitente>
            <!--Optional:-->
            <!--Optional:-->
            <secuenciaFirma>CASCADA</secuenciaFirma>
            <sustituye>true</sustituye>
            <!--Optional:-->
         </request>
      </fir:iniciarProcesoFirma>
   </soapenv:Body>
</soapenv:Envelope>';
        return $xml;
    }

    /**
     * Get file content params
     * @param string $code
     * @return string
     */
    private function create_params_getfilecontent(string $code): string {

        return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
xmlns:fir="http://firma.ws.producto.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <fir:obtenerContenidoDocumento>
         <!--Optional:-->
         <request>
            <!--Optional:-->
            <idAplicacion>' . $this->configuration->get_appid() . '</idAplicacion>
            <!--Optional:-->
            <identificador>
               <!--Optional:-->
               <token>' . $code . '</token>
               <version>1</version>
            </identificador>
         </request>
      </fir:obtenerContenidoDocumento>
   </soapenv:Body>
</soapenv:Envelope>';
    }

    /**
     * get file url params
     * @param string $code
     * @return string
     */
    private function create_params_get_file_url(string $code): string {
        return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
xmlns:fir="http://firma.ws.producto.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <fir:obtenerDocumentosFirmados>
         <!--Optional:-->
         <request>
            <!--Optional:-->
            <idAplicacion>' . $this->configuration->get_appid() . '</idAplicacion>
            <!--Zero or more repetitions:-->
            <identificadores>
               <!--Optional:-->
               <token>' . $code . '</token>
               <version>1</version>
            </identificadores>
         </request>
      </fir:obtenerDocumentosFirmados>
   </soapenv:Body>
</soapenv:Envelope>';
    }

    /**
     * Get file
     * @param int $courseid
     * @param int $validationid
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws moodle_exception
     * @throws stored_file_creation_exception
     */
    public function get_file(int $courseid, int $validationid): array {
        try {
            $validation = new certifygen_validations($validationid);
            $code = certifygen_validations::get_certificate_code($validation);
            $params = ['validationid' => $validationid];
            $teacherrequest = certifygenvalidationcsv::get_record($params);
            $haserror = true;
            if (!$teacherrequest) {
                throw new moodle_exception('certifygenvalidationcsvnotfound', 'certifygen');
            }
            $params = $this->create_params_getFileContent($code);
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->configuration->get_querywsdl(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/xml',
                ],
            ]);
            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                $result['error']['code'] = '';
                $result['error']['message'] = curl_error($curl);
                return $result;
            }
            $xml = simplexml_load_string(
                $response,
                null,
                null,
                'http://schemas.xmlsoap.org/soap/envelope/'
            );
            $ns = $xml->getNamespaces(true);
            $soap = $xml->children($ns['soap']);
            $res = $soap->Body->children($ns['ns2']);
            $obtenercontenidodocumentoresponse = $res->obtenerContenidoDocumentoResponse->children();
            $obtenercontenidodocumentorc = $obtenercontenidodocumentoresponse->children();
            $resultado = (string) $obtenercontenidodocumentorc->resultado;
            if ($resultado === 'KO') {
                $codeerror = (string) $obtenercontenidodocumentorc->error->children()->codError;
                $descerror = (string) $obtenercontenidodocumentorc->error->children()->descError;
                $result['error']['code'] = $codeerror;
                $result['error']['message'] = $descerror;
                return $result;
            }
            $docspeticion = $obtenercontenidodocumentorc->docsPeticion;
            $docspeticiondocumentos = $docspeticion->documentos;
            $datos = (string) $docspeticiondocumentos->datos;
            $datos = base64_decode($datos);
            $file = $this->create_file_from_content($datos, $validationid, $code, $courseid);
            $result['error'] = [];
            $result['file'] = $file;
            return $result;
        } catch (SoapFault $e) {
            debugging(__FUNCTION__ . ' SoapFault error: ' . $e->getMessage());
            $message = $e->getMessage();
        } catch (Exception $e) {
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $message = $e->getMessage();
        }
        $result['error']['code'] = $message;
        $result['error']['message'] = $message;
        return $result;
    }

    /**
     * Get file url
     * @param int $validationid
     * @param string $code
     * @return array
     */
    public function get_file_url_from_external_service(int $validationid, string $code): array {
        try {
            $params = ['validationid' => $validationid];
            $teacherrequest = certifygenvalidationcsv::get_record($params);
            if (!$teacherrequest) {
                throw new moodle_exception('certifygenvalidationcsvnotfound', 'certifygen');
            }
            $params = $this->create_params_get_file_url($code);
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->configuration->get_querywsdl(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/xml',
                ],
            ]);
            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                return [
                    'haserror' => true,
                    'message' => curl_error($curl),
                ];
            }
            $xml = simplexml_load_string(
                $response,
                null,
                null,
                'http://schemas.xmlsoap.org/soap/envelope/'
            );
            $ns = $xml->getNamespaces(true);
            $soap = $xml->children($ns['soap']);
            $res = $soap->Body->children($ns['ns2']);
            $obtenercontenidodocumentoresponse = $res->obtenerDocumentosFirmadosResponse->children();
            $obtenercontenidodocumentorc = $obtenercontenidodocumentoresponse->children();
            $resultado = (string) $obtenercontenidodocumentorc->resultado;
            if ($resultado === 'KO') {
                $codeerror = (string) $obtenercontenidodocumentorc->error->children()->codError;
                $descerror = (string) $obtenercontenidodocumentorc->error->children()->descError;
                return [
                    'haserror' => true,
                    'message' => $codeerror . ' - ' . $descerror,
                ];
            }
            $docspeticion = $obtenercontenidodocumentorc->docsPeticiones;
            $docspeticiondocumentos = $docspeticion->documentos;
            $url = (string) $docspeticiondocumentos->url;
            return [
                'haserror' => false,
                'url' => $url,
            ];
        } catch (coding_exception $e) {
            debugging(__FUNCTION__ . ' coding_exception error: ' . $e->getMessage());
            return [
                'haserror' => true,
                'message' => $e->getMessage(),
            ];
        } catch (moodle_exception $e) {
            debugging(__FUNCTION__ . '  moodle_exception error: ' . $e->getMessage());
            return [
                'haserror' => true,
                'message' => $e->getMessage(),
            ];
        } catch (SoapFault $e) {
            debugging(__FUNCTION__ . ' SoapFault error: ' . $e->getMessage());
            return [
                'haserror' => true,
                'message' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            return [
                'haserror' => true,
                'message' => $e->getMessage(),
            ];
        }
        return [
            'haserror' => true,
            'message' => 'something went wrong',
        ];
    }

    /**
     * Create file from content
     * @param string $content
     * @param int $validationid
     * @param string $code
     * @param $courseid
     * @return stored_file
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    public function create_file_from_content(string $content, int $validationid, string $code, $courseid) {

        // Get pdf content.
        $context = context_system::instance();
        if (!empty($courseid)) {
            $context = context_course::instance($courseid);
        }

        // Save pdf on moodledata.
        $fs = get_file_storage();
        $filerecord = [
            'contextid' => $context->id,
            'component' => self::FILE_COMPONENT,
            'filearea' => self::FILE_AREA_VALIDATED,
            'itemid' => $validationid,
            'filepath' => self::FILE_PATH,
            'filename' => $code . '.pdf',
        ];

        if (
            $file = $fs->get_file(
                $filerecord['contextid'],
                $filerecord['component'],
                $filerecord['filearea'],
                $filerecord['itemid'],
                $filerecord['filepath'],
                $filerecord['filename']
            )
        ) {
            $file->delete();
        }
        return $fs->create_file_from_string($filerecord, $content);
    }

    /**
     * Can revoke
     * @param int $courseid
     * @return bool
     */
    public function can_revoke(int $courseid): bool {
        return true;
    }

    /**
     * Param for revoke
     * @param string $code
     * @return string
     */
    private function create_params_revoke(string $code): string {
        return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
xmlns:fir="http://firma.ws.producto.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <fir:anularPeticion>
         <!--Optional:-->
         <request>
            <!--Optional:-->
            <idAplicacion>' . $this->configuration->get_appid() . '</idAplicacion>
            <!--Optional:-->
            <identificador>
               <!--Optional:-->
               <token>' . $code . '</token>
               <version>1</version>
            </identificador>
         </request>
      </fir:anularPeticion>
   </soapenv:Body>
</soapenv:Envelope>';
        $params = [
            'idAplicacion' => $this->configuration->get_appid(),
            'identificador' => [
                'token' => $code,
                'version' => 1,
            ],
        ];
        return ['anularPeticion' => $params];
    }

    /**
     * Status param
     * @param string $code
     * @return string
     */
    private function create_params_status(string $code): string {
        $params = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:fir="http://firma.ws.producto.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <fir:consultaEstadoPeticion>
         <!--Optional:-->
         <request>
            <!--Optional:-->
            <idAplicacion>' . $this->configuration->get_appid() . '</idAplicacion>
            <!--Zero or more repetitions:-->
            <identificadores>
               <!--Optional:-->
               <token>' . $code . '</token>
               <version>1</version>
            </identificadores>
         </request>
      </fir:consultaEstadoPeticion>
   </soapenv:Body>
</soapenv:Envelope>';
        return $params;
    }

    /**
     * Revoke
     * @param
     * string $code
     * @return array
     * @throws moodle_exception
     */
    public function revoke(string $code): array {
        try {
            $message = '';
            $haserror = false;
            $params = $this->create_params_revoke($code);
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->configuration->get_wsdl(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/xml',
                ],
            ]);
            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                return [
                    'haserror' => true,
                    'message' => curl_error($curl),
                ];
            }
            $xml = simplexml_load_string(
                $response,
                null,
                null,
                'http://schemas.xmlsoap.org/soap/envelope/'
            );
            $ns = $xml->getNamespaces(true);
            $soap = $xml->children($ns['soap']);
            $res = $soap->Body->children($ns['ns2']);
            $anularpeticionresponse = $res->anularPeticionResponse->children();
            $anularpeticionresponsechildren = $anularpeticionresponse->children();
            $resultado = (string) $anularpeticionresponsechildren->resultado;
            if ($resultado === 'KO') {
                $codeerror = (string) $anularpeticionresponsechildren->error->children()->codError;
                $descerror = (string) $anularpeticionresponsechildren->error->children()->descError;
                debugging(__FUNCTION__ . '  moodle_exception error: ' . $descerror);
                throw new moodle_exception(
                    'revokeerror',
                    'certifygenvalidation_csv',
                    '',
                    null,
                    $codeerror . ' - ' . $descerror
                );
            }
        } catch (SoapFault $e) {
            $haserror = true;
            debugging(__FUNCTION__ . '  SoapFault error: ' . $e->getMessage());
        } catch (Exception $e) {
            $haserror = true;
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
        }
        return [
            'haserror' => $haserror,
            'message' => $message,
        ];
    }

    /**
     * Is enable
     * @return bool
     */
    public function is_enabled(): bool {
        return $this->configuration->is_enabled();
    }

    /**
     * Check status
     * @return bool
     */
    public function check_status(): bool {
        return true;
    }

    /**
     * get status
     * @param int $validationid
     * @param string $code
     * @return int
     * @throws moodle_exception
     */
    public function get_status(int $validationid, string $code): int {
        $params = ['validationid' => $validationid];
        $csvvalidation = certifygenvalidationcsv::get_record($params);
        if (!$csvvalidation) {
            throw new moodle_exception('validationidnotfound', 'certifygenvalidation_csv');
        }
        try {
            $message = '';
            $haserror = false;
            $params = $this->create_params_status($code);
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->configuration->get_querywsdl(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/xml',
                ],
            ]);

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                return [
                    'haserror' => true,
                    'message' => curl_error($curl),
                ];
            }
            curl_close($curl);

            $xml = simplexml_load_string($response, null, null, 'http://schemas.xmlsoap.org/soap/envelope/');
            $ns = $xml->getNamespaces(true);
            $soap = $xml->children($ns['soap']);
            $res = $soap->Body->children($ns['ns2']);
            $iniciarprocesofirmaresponse = $res->consultaEstadoPeticionResponse->children();
            $iniciarprocesofirmaresponsechildren = $iniciarprocesofirmaresponse->children();
            $resultado = (string) $iniciarprocesofirmaresponsechildren->resultado;
            if ($resultado === 'KO') {
                $codeerror = (string) $iniciarprocesofirmaresponsechildren->error->children()->codError;
                $descerror = (string) $iniciarprocesofirmaresponsechildren->error->children()->descError;
                debugging(__FUNCTION__ . '  moodle_exception error: ' . $descerror);
                throw new moodle_exception('getstatuserror', 'certifygenvalidation_csv', '', null, $codeerror . ' - ' . $descerror);
            }
            // Se obtiene idExpediente.
            $peticiones = $iniciarprocesofirmaresponsechildren->peticiones;
            $estado = '';
            foreach ($peticiones as $peticion) {
                $estado = (string) $peticion->estadoCircuito;
            }

            if ($estado == 'FIRMADO') {
                return certifygen_validations::STATUS_VALIDATION_OK;
            } else if ($estado == 'RECHAZADO') {
                return certifygen_validations::STATUS_VALIDATION_ERROR;
            }
            return certifygen_validations::STATUS_IN_PROGRESS;
        } catch (SoapFault $e) {
            $haserror = true;
            debugging(__FUNCTION__ . '  SoapFault error: ' . $e->getMessage());
        } catch (Exception $e) {
            $haserror = true;
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
        }
        return [
            'haserror' => $haserror,
            'message' => $message,
        ];
    }

    /**
     * Check file
     * @return bool
     */
    public function checkfile(): bool {
        return true;
    }

    /**
     * Returns an array of strings associated to certifiacte status to be shown on
     * activityteacher_table and profile_my_certificates_table
     */
    public function get_status_messages(): array {
        return [];
    }

    /**
     * If true, the certifygen activities related with this type of validation will be part
     * of the output of get_id_instance_certificate_external ws.
     * If true, the teacher requests with models with this type of validation will be part
     *  of the output of get_courses_as_teacher ws.
     *
     * @return bool
     * @throws dml_exception
     */
    public function is_visible_in_ws(): bool {
        return (int)get_config('certifygenvalidation_csv', 'wsoutput');
    }
}
