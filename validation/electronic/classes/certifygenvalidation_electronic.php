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
 * @package   certifygenvalidation_electronic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace certifygenvalidation_electronic;

use context_course;
use context_system;
use dml_exception;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\Fpdi\PdfReader\PdfReaderException;
use setasign\Fpdi\Tcpdf\Fpdi;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/tcpdf/tcpdf.php');
require_once($CFG->dirroot . '/mod/assign/feedback/editpdf/fpdi/autoload.php');
/**
 * Certifygen Validation Electronic
 * @package   certifygenvalidation_electronic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenvalidation_electronic implements ICertificateValidation {
    /**
     * add_certificate_signature
     * @param certifygen_file $file
     * @return string
     * @throws CrossReferenceException
     * @throws FilterException
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws PdfReaderException
     * @throws dml_exception
     */
    private function add_certificate_signature(certifygen_file $file) {
        global $CFG;

        $pdf = new Fpdi(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information.
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Unimoodle Certifygen');
        $pdf->SetTitle('Unimoodle Certifygen title');
        $pdf->SetSubject('Unimoodle Certifygen subject');
        $pdf->SetKeywords('Unimoodle Certifygen, certifygen');

        // Set header and footer fonts.
        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

        // Set default monospaced font.
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins.
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // Set auto page breaks.
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        // Set image scale factor.
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        /*
        NOTES:
         - To create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
         - To export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
         - To convert pfx certificate to pem: openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes
        */

        // Set certificate file.
        $certificate = get_config('certifygenvalidation_electronic', 'path');
        // Set additional information.
        $info = [
            'Name' => get_config('certifygenvalidation_electronic', 'name'),
            'Location' => get_config('certifygenvalidation_electronic', 'location'),
            'Reason' => get_config('certifygenvalidation_electronic', 'reason'),
            'ContactInfo' => get_config('certifygenvalidation_electronic', 'contactinfo'),
        ];

        // Set document signature.
        $pdf->setSignature($certificate, $certificate, 'tcpdfdemo', '', 2, $info);
        // Temporary file.
        $tempdir = make_temp_directory('certifygen');
        $tempfile = tempnam($tempdir, 'certifygen');
        $file->get_file()->copy_content_to($tempfile);
        $pages = $pdf->setSourceFile($tempfile);
        for ($i = 0; $i < $pages; $i++) {
            // Set font.
            $pdf->SetFont('helvetica', '', 12);
            $pdf->AddPage();
            $tplidx = $pdf->importPage($i + 1);
            $pdf->useTemplate($tplidx, 10, 10, 200);
            // Change font size.
            $pdf->SetFontSize(8);
            // Change text color: grey.
            $pdf->SetTextColor(128, 128, 128);
            $firstborder = ['LTR' => ['width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => [128, 128, 128]]];
            $middleborder = ['LR' => ['width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => [128, 128, 128]]];
            $lastborder = ['LRB' => ['width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => [128, 128, 128]]];
            $pdf->Ln(75);
            $total = count($info);
            $num = 0;
            foreach ($info as $key => $data) {
                if ($num == 0) {
                    $border = $firstborder;
                } else if ($num == $total - 1) {
                    $border = $lastborder;
                } else {
                    $border = $middleborder;
                }
                $num++;
                $pdf->Cell(45, 0, $key . ' : ' . $data, $border, 1, 'C', 0, '', 1);
            }
        }
        // Set font.
        $pdf->SetFont('helvetica', '', 12);
        // Set signature appearance.
        // Define active area for signature appearance.
        $pdf->setSignatureAppearance(180, 60, 15, 15);

        // Set an empty signature appearance.
        $pdf->addEmptySignatureAppearance(180, 80, 15, 15);

        // Close and output PDF document.
        return  $pdf->Output($file->get_file()->get_filename(), 'S');
    }

    /**
     * sendFile
     * @param certifygen_file $file
     * @return array
     * @throws CrossReferenceException
     * @throws FilterException
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws PdfReaderException
     */
    public function send_file(certifygen_file $file): array {
        $haserror = false;
        $message = get_string('ok', 'mod_certifygen');
        try {
            $fs = get_file_storage();
            $context = context_system::instance();
            $cv = new certifygen_validations($file->get_validationid());
            if (!empty($cv->get('certifygenid'))) {
                $cert = new certifygen($cv->get('certifygenid'));
                $context = context_course::instance($cert->get('course'));
            }
            $filerecord = [
                'contextid' => $context->id,
                'component' => self::FILE_COMPONENT,
                'filearea' => self::FILE_AREA_VALIDATED,
                'itemid' => $file->get_validationid(),
                'filepath' => self::FILE_PATH,
                'filename' => $file->get_file()->get_filename(),
            ];
            $pdfstring = $this->add_certificate_signature($file);
            $newfile = $fs->create_file_from_string($filerecord, $pdfstring);
        } catch (moodle_exception $exception) {
            $haserror = true;
            $message = $exception->getMessage();
            $newfile = null;
        }

        return [
            'haserror' => $haserror,
            'message' => $message,
            'newfile' => $newfile,
        ];
    }

    /**
     * getFile
     * @param int $courseid
     * @param int $validationid
     * @return array
     */
    public function get_file(int $courseid, int $validationid): array {
        $result = ['error' => [], 'message' => get_string('ok', 'mod_certifygen')];
        try {
            $validation = new certifygen_validations($validationid);
            $code = certifygen_validations::get_certificate_code($validation);
            $fs = get_file_storage();
            $context = context_system::instance();
            if (!empty($validation->get('certifygenid'))) {
                $cert = new certifygen($validation->get('certifygenid'));
                $context = context_course::instance($cert->get('course'));
            }
            $file = $fs->get_file(
                $context->id,
                self::FILE_COMPONENT,
                self::FILE_AREA,
                $validationid,
                self::FILE_PATH,
                $code . '.pdf'
            );
            if (!$file) {
                $result['error']['code'] = 'file_not_found';
                $result['error']['message'] = 'file_not_found';
                return $result;
            }
            $result['file'] = $file;
        } catch (moodle_exception $exception) {
            $result['error']['code'] = $exception->getCode();
            $result['error']['message'] = $exception->getMessage();
        }
        return $result;
    }

    /**
     * revoke
     * @param string $code
     * @return array
     */
    public function revoke(string $code): array {
        return [
            'haserror' => false,
            'message' => '',
        ];
    }

    /**
     * is_enabled
     * @return bool
     * @throws dml_exception
     */
    public function is_enabled(): bool {
        return (int)get_config('certifygenvalidation_electronic', 'enabled');
    }

    /**
     * checkStatus
     * @return bool
     */
    public function check_status(): bool {
        return false;
    }

    /**
     * getStatus
     * @param int $validationid
     * @param string $code
     * @return int
     */
    public function get_status(int $validationid, string $code): int {
        return certifygen_validations::STATUS_VALIDATION_OK;
    }

    /**
     * checkfile
     * @return bool
     */
    public function checkfile(): bool {
        return false;
    }

    /**
     * canRevoke
     * @param int $courseid
     * @return bool
     */
    public function can_revoke(int $courseid): bool {
        return false;
    }
    /**
     * Returns an array of strings associated to certifiacte status to be shown on
     * activityteacher_table and profile_my_certificates_table
     * @return array
     */
    public function get_status_messages(): array {
        return [];
    }
}
