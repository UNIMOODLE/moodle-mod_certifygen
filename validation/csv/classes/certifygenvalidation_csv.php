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
require_once $CFG->libdir . '/pdflib.php';

use certifygenvalidation_csv\persistents\certifygenvalidationcsv;
use coding_exception;
use core\session\exception;
use dml_exception;
use file_exception;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use moodle_url;
use pdf;
use SoapFault;
use stored_file;
use stored_file_creation_exception;

class certifygenvalidation_csv implements ICertificateValidation
{
    private csv_configuration $configuration;

    /**
     * @param csv_configuration $configuration
     */
    public function __construct()
    {
        $this->configuration = new csv_configuration();
    }


    /**
     * @throws exception
     */
    public function sendFile(certifygen_file $file): array
    {
        global $USER;

        $params = $this->create_params_sendFile($file);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://pru.sede.uva.es/FirmaCatalogService',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/xml'
            ),
        ));
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            return [
                'haserror' => true,
                'message' => curl_error($curl),
            ];
        }
        $xml = simplexml_load_string($response, null, null, 'http://schemas.xmlsoap.org/soap/envelope/');
        $ns = $xml->getNamespaces(true);
        $soap = $xml->children($ns['soap']);
        $res = $soap->Body->children($ns['ns2']);
        $iniciarProcesoFirmaResponse = $res->iniciarProcesoFirmaResponse->children();
        $iniciarProcesoFirmaResponsechildren = $iniciarProcesoFirmaResponse->children();
        $resultado = (string) $iniciarProcesoFirmaResponsechildren->resultado;
        if ($resultado === 'KO') {
            $codError = (string) $iniciarProcesoFirmaResponsechildren->error->children()->codError;
            $descError = (string) $iniciarProcesoFirmaResponsechildren->error->children()->descError;
            return [
                'haserror' => true,
                'message' => $codError . ' - ' . $descError,
            ];
        }
        // Se obtiene idExpediente;
        $idExpediente = (string) $iniciarProcesoFirmaResponsechildren->idExpediente;
        $validationid = $file->get_validationid();
        $token = str_replace('.pdf', '', $file->get_file()->get_filename());
        $data = [
            'validationid' => $validationid,
            'applicationid' => $idExpediente,
            'token' => $token,
            'usermodified' => $USER->id,
        ];
        $cv = new certifygenvalidationcsv(0, (object)$data);
        $cv->save();
        curl_close($curl);

        return [
            'haserror' => false,
            'message' => 'asdasd',
        ];
    }

    /**
     * @param certifygen_file $file
     * @return array[]
     */
    private function create_params_sendFile(certifygen_file $file) : string {

        $token = str_replace('.pdf', '', $file->get_file()->get_filename());
        $avisourl = (new moodle_url('/'))->out();
        $base64 = base64_encode($file->get_file()->get_content());
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:fir="http://firma.ws.producto.com/">
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
               <avisoURL>'. $avisourl .'</avisoURL>
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
     * @param string $code
     * @return string
     */
    private function create_params_getFileContent(string $code) : string {

        return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:fir="http://firma.ws.producto.com/">
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
     * @param string $code
     * @return array[]
     */
    private function create_params_getFileUrl(string $code) : string {
        return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:fir="http://firma.ws.producto.com/">
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
     * @param int $courseid
     * @param int $validationid
     * @param string $code
     * @return array
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function getFile(int $courseid, int $validationid) : array
    {
        try {
            $validation = new certifygen_validations($validationid);
            $code = certifygen_validations::get_certificate_code($validation);
            $params = ['validationid' => $validationid];
            $teacherrequest = certifygenvalidationcsv::get_record($params);
            $message = 'Something went wrong';
            $haserror = true;
            if (!$teacherrequest) {
                throw new moodle_exception('certifygenvalidationcsvnotfound', 'certifygen');
            }
            $params = $this->create_params_getFileContent($code);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://pru.sede.uva.es/FirmaQueryCatalogService',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/xml'
                ),
            ));
            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                return [
                    'haserror' => true,
                    'message' => curl_error($curl),
                ];
            }
            $xml = simplexml_load_string($response, null, null, 'http://schemas.xmlsoap.org/soap/envelope/');
            $ns = $xml->getNamespaces(true);
            $soap = $xml->children($ns['soap']);
            $res = $soap->Body->children($ns['ns2']);
            $obtenerContenidoDocumentoResponse = $res->obtenerContenidoDocumentoResponse->children();
            $obtenerContenidoDocumentoResponsechildren = $obtenerContenidoDocumentoResponse->children();
            $resultado = (string) $obtenerContenidoDocumentoResponsechildren->resultado;
            if ($resultado === 'KO') {
                $codError = (string) $obtenerContenidoDocumentoResponsechildren->error->children()->codError;
                $descError = (string) $obtenerContenidoDocumentoResponsechildren->error->children()->descError;
                return [
                    'haserror' => true,
                    'message' => $codError . ' - ' . $descError,
                ];
            }
            $docspeticion = $obtenerContenidoDocumentoResponsechildren->docsPeticion;
            $docspeticiondocumentos = $docspeticion->documentos;
            $datos = (string) $docspeticiondocumentos->datos;
            $datos = base64_decode($datos);
            $file = $this->create_file_from_content($datos, $validationid, $code);
            return [
                'haserror' => false,
                'message' => 'ok',
                'file' => $file,
            ];
        }
        catch ( SoapFault $e ) {
            error_log(__FUNCTION__ . ' ' . __LINE__ .  ' SoapFault error: '.var_export($e->getMessage(), true));
            $message = $e->getMessage();
        }
        catch (Exception $e) {
            error_log(__FUNCTION__ . ' ' . __LINE__ .  ' error: '.var_export($e->getMessage(), true));
            $message = $e->getMessage();
//            $connection = new SoapFault('client', 'Could not connect to the service');
        }
        return [
            'haserror' => $haserror,
            'message' => $message
        ];
    }

    /**
     * @param int $validationid
     * @param string $code
     * @return array
     * @throws moodle_exception
     */
    public function getFileUrlFromExternalService(int $validationid, string $code) : array
    {
        try {
            $params = ['validationid' => $validationid];
            $teacherrequest = certifygenvalidationcsv::get_record($params);
            if (!$teacherrequest) {
                throw new moodle_exception('certifygenvalidationcsvnotfound', 'certifygen');
            }
            $params = $this->create_params_getFileUrl($code);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://pru.sede.uva.es/FirmaQueryCatalogService',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/xml'
                ),
            ));
            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                return [
                    'haserror' => true,
                    'message' => curl_error($curl),
                ];
            }
            $xml = simplexml_load_string($response, null, null, 'http://schemas.xmlsoap.org/soap/envelope/');
            $ns = $xml->getNamespaces(true);
            $soap = $xml->children($ns['soap']);
            $res = $soap->Body->children($ns['ns2']);
            $obtenerContenidoDocumentoResponse = $res->obtenerDocumentosFirmadosResponse->children();
            $obtenerContenidoDocumentoResponsechildren = $obtenerContenidoDocumentoResponse->children();
            $resultado = (string) $obtenerContenidoDocumentoResponsechildren->resultado;
            if ($resultado === 'KO') {
                $codError = (string) $obtenerContenidoDocumentoResponsechildren->error->children()->codError;
                $descError = (string) $obtenerContenidoDocumentoResponsechildren->error->children()->descError;
                return [
                    'haserror' => true,
                    'message' => $codError . ' - ' . $descError,
                ];
            }
            $docspeticion = $obtenerContenidoDocumentoResponsechildren->docsPeticiones;
            $docspeticiondocumentos = $docspeticion->documentos;
            $url = (string) $docspeticiondocumentos->url;
            return [
                'haserror' => false,
                'url' => $url,
            ];
        }
        catch ( coding_exception $e ) {
            error_log(__FUNCTION__ . ' ' . __LINE__ .  ' coding_exception error: '.var_export($e->getMessage(), true));
            return [
                'haserror' => true,
                'message' => $e->getMessage(),
            ];
        }
        catch ( moodle_exception $e ) {
            error_log(__FUNCTION__ . ' ' . __LINE__ .  ' moodle_exception error: '.var_export($e->getMessage(), true));
            return [
                'haserror' => true,
                'message' => $e->getMessage(),
            ];
        }
        catch ( SoapFault $e ) {
            error_log(__FUNCTION__ . ' ' . __LINE__ .  ' SoapFault error: '.var_export($e->getMessage(), true));
            return [
                'haserror' => true,
                'message' => $e->getMessage(),
            ];
        }
        catch (Exception $e) {
            error_log(__FUNCTION__ . ' ' . __LINE__ .  ' error: '.var_export($e->getMessage(), true));
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
     * @param string $content
     * @param int $validationid
     * @param string $code
     * @return void
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     * @throws coding_exception
     */
    public function create_file_from_content(string $content, int $validationid, string $code) {
        // Create a Pdf file.
//        $doc = new pdf();
//        $doc->SetTitle('Certifygen certificate');
//        $doc->SetAuthor('UNIMOODLE ');
//        $doc->SetCreator('mod_certifygen');
//        $doc->SetKeywords('Moodle, PDF, Certifygen, Unimoodle');
//        $doc->SetSubject('This has been generated by mod_certifygen');
//        $doc->SetMargins(15, 30);
//        $doc->AddPage();
//        $doc->writeHTML($content);

        // Get pdf content.
        $itemid = $validationid;
        $cv = new certifygen_validations($validationid);
        if (!empty($cv->get('certifygenid'))) {
            $cert = new certifygen($cv->get('certifygenid'));
            $context = \context_course::instance($cert->get('course'));
        } else {
            $context = \context_system::instance();
        }
//        $pdfcontent = $doc->Output($code, 'S');

        // Save pdf on moodledata.
        $fs = get_file_storage();
        $filerecord = [
            'contextid' => $context->id,
            'component' => self::FILE_COMPONENT,
            'filearea' => self::FILE_AREA_VALIDATED,
            'itemid' => $itemid,
            'filepath' => self::FILE_PATH,
            'filename' => $code . '.pdf'
        ];

        if ($file = $fs->get_file($filerecord['contextid'], $filerecord['component'], $filerecord['filearea'], $filerecord['itemid'],
            $filerecord['filepath'], $filerecord['filename'])) {
            $file->delete();
        }
        return $fs->create_file_from_string($filerecord, $content);
    }

    /**
     * @return bool
     */
    public function canRevoke(): bool
    {
        return true;
    }

    /**
     * @param string $code
     * @return string
     */
    private function create_params_revoke(string $code) : string {
        return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:fir="http://firma.ws.producto.com/">
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
     * @param certifygenvalidationcsv $csvvalidation
     * @return array[]
     * @throws coding_exception
     */
    private function create_params_status(string $code) : string {
        $params = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:fir="http://firma.ws.producto.com/">
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
               <token>'. $code . '</token>
               <version>1</version>
            </identificadores>
         </request>
      </fir:consultaEstadoPeticion>
   </soapenv:Body>
</soapenv:Envelope>';
        return $params;
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
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://pru.sede.uva.es/FirmaCatalogService',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/xml'
                ),
            ));
            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                return [
                    'haserror' => true,
                    'message' => curl_error($curl),
                ];
            }
            $xml = simplexml_load_string($response, null, null, 'http://schemas.xmlsoap.org/soap/envelope/');
            $ns = $xml->getNamespaces(true);
            $soap = $xml->children($ns['soap']);
            $res = $soap->Body->children($ns['ns2']);
            $anularPeticionResponse = $res->anularPeticionResponse->children();
            $anularPeticionResponsechildren = $anularPeticionResponse->children();
            $resultado = (string) $anularPeticionResponsechildren->resultado;
            if ($resultado === 'KO') {
                $codError = (string) $anularPeticionResponsechildren->error->children()->codError;
                $descError = (string) $anularPeticionResponsechildren->error->children()->descError;
                error_log(__FUNCTION__ . ' error: '.var_export($descError, true));
                throw new moodle_exception('revokeerror', 'certifygenvalidation_csv', '', null, $codError . ' - ' . $descError);
            }
        }
        catch ( SoapFault $e ) {
            $haserror = true;
            error_log(__FUNCTION__ .  ' ' . __LINE__ . ' SoapFault error: '.var_export($e->getMessage(), true));
        }
        catch (Exception $e) {
            $haserror = true;
            error_log(__FUNCTION__ . ' ' . __LINE__ .  ' error: '.var_export($e->getMessage(), true));
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
        $itemid = $validationid;
        $cv = new certifygen_validations($validationid);
        if (!empty($cv->get('certifygenid'))) {
            $cert = new certifygen($cv->get('certifygenid'));
            $context = \context_course::instance($cert->get('course'));
        } else {
            $context = \context_system::instance();
        }
        $filerecord = [
            'contextid' => $context->id,
            'component' => self::FILE_COMPONENT,
            'filearea' => self::FILE_AREA_VALIDATED,
            'itemid' => $itemid,
            'filepath' => self::FILE_PATH,
            'filename' => $code
        ];

        $fs = get_file_storage();
        if ($newfile = $fs->get_file($filerecord['contextid'], $filerecord['component'], $filerecord['filearea'], $filerecord['itemid'],
            $filerecord['filepath'], $filerecord['filename'])) {
            $url = moodle_url::make_pluginfile_url(
                $newfile->get_contextid(),
                $newfile->get_component(),
                $newfile->get_filearea(),
                $newfile->get_itemid(),
                $newfile->get_filepath(),
                $newfile->get_filename(),
                false                     // Do not force download of the file.
            );
            return $url->out();
        }
        return '';
    }

    /**
     * @return bool
     */
    public function is_enabled(): bool
    {
        return $this->configuration->is_enabled();
    }

    /**
     * @return bool
     */
    public function checkStatus(): bool
    {
        return true;
    }

    /**
     * @param int $validationid
     * @return int
     * @throws moodle_exception
     * @throws coding_exception
     */
    public function getStatus(int $validationid, string $code): int
    {
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
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://pru.sede.uva.es/FirmaQueryCatalogService',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/xml'
                ),
            ));

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
            $iniciarProcesoFirmaResponse = $res->consultaEstadoPeticionResponse->children();
            $iniciarProcesoFirmaResponsechildren = $iniciarProcesoFirmaResponse->children();
            $resultado = (string) $iniciarProcesoFirmaResponsechildren->resultado;
            if ($resultado === 'KO') {
                $codError = (string) $iniciarProcesoFirmaResponsechildren->error->children()->codError;
                $descError = (string) $iniciarProcesoFirmaResponsechildren->error->children()->descError;
                error_log(__FUNCTION__ . ' error: '.var_export($descError, true));
                throw new moodle_exception('getstatuserror', 'certifygenvalidation_csv', '', null, $codError . ' - ' . $descError);
            }
            // Se obtiene idExpediente;
            $peticiones = $iniciarProcesoFirmaResponsechildren->peticiones;
            $estado = '';
            foreach ($peticiones as $peticion) {
                $estado =  (string) $peticion->estadoCircuito;
            }

            if ($estado == 'FIRMADO') {
                return certifygen_validations::STATUS_VALIDATION_OK;
            } else if ($estado == 'RECHAZADO') {
                return certifygen_validations::STATUS_VALIDATION_ERROR;
            }
            return certifygen_validations::STATUS_IN_PROGRESS;
        }
        catch ( SoapFault $e ) {
            $haserror = true;
            error_log(__FUNCTION__ .  ' ' . __LINE__ . ' SoapFault error: '.var_export($e->getMessage(), true));
        }
        catch (Exception $e) {
            $haserror = true;
            error_log(__FUNCTION__ . ' ' . __LINE__ .  ' error: '.var_export($e->getMessage(), true));
        }
        return [
            'haserror' => $haserror,
            'message' => $message,
        ];
    }

    /**
     * @return bool
     */
    public function checkfile(): bool
    {
        return true;
    }
}