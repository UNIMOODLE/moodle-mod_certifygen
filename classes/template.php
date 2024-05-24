<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Class represents a certificate template.
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen;

use context_helper;
use core\message\message;
use core\output\inplace_editable;
use core_user;
use moodle_url;
use tool_certificate\customfield\issue_handler;
use tool_certificate\output;
use tool_certificate\page;
use tool_certificate\permission;
use tool_certificate\persistent;

/**
 * Class represents a certificate template.
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template extends \tool_certificate\template {
    /**
     * @param int $id
     * @param \stdClass|null $obj
     * @return template
     * @throws \coding_exception
     */

    public static function instance(int $id = 0, ?\stdClass $obj = null): \mod_certifygen\template
    {
        $data = new \stdClass();
        if ($obj !== null) {
            // Ignore fields that are not properties.
            $data = (object)array_intersect_key((array)$obj, \tool_certificate\persistent\template::properties_definition());
        }
        $t = new self();
        $t->persistent = new \tool_certificate\persistent\template($id, $data);
        return $t;
    }

    /**
     * Generate the PDF for the template.
     *
     * @param bool $preview True if it is a preview, false otherwise
     * @param \stdClass $issue The issued certificate we want to view
     * @param string $lang Language
     * @param bool $return
     * @return string|null Return the PDF as string if $return specified
     */
    public function generate_pdf($preview = false, $issue = null, $return = false, string $lang = "") {
        global $CFG, $USER;

        if (is_null($issue)) {
            $user = $USER;
        } else {
            $user = \core_user::get_user($issue->userid);
        }

        require_once($CFG->libdir . '/pdflib.php');

        // Get the pages for the template, there should always be at least one page for each template.
        if ($pages = $this->get_pages()) {
            // Create the pdf object.
            $pdf = new \pdf();

            //TODO: mi logica de lang
            // If 'issuelang' setting, force the current language to the users being issued otherwise force site language.
            if (get_config('tool_certificate', 'issuelang') && isset($user->lang)) {
                $currentlang = force_current_language($user->lang);
            } else {
                $currentlang = force_current_language($CFG->lang);
            }

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetTitle($this->get_formatted_name());
            $pdf->setViewerPreferences([
                'DisplayDocTitle' => true,
            ]);
            $pdf->SetAutoPageBreak(true, 0);
            // Remove full-stop at the end, if it exists, to avoid "..pdf" being created and being filtered by clean_filename.
            $filename = rtrim($this->get_formatted_name(), '.');
            $filename = clean_filename($filename . '.pdf');
            // Loop through the pages and display their content.
            foreach ($pages as $page) {
                $pagerecord = $page->to_record();
                // Add the page to the PDF.
                if ($pagerecord->width > $pagerecord->height) {
                    $orientation = 'L';
                } else {
                    $orientation = 'P';
                }
                $pdf->AddPage($orientation, [$pagerecord->width, $pagerecord->height]);
                $pdf->SetMargins($pagerecord->leftmargin, 0, $pagerecord->rightmargin);
                // Get the elements for the page.
                if ($elements = $page->get_elements()) {
                    // Loop through and display.
                    foreach ($elements as $element) {
                        $element->render($pdf, $preview, $user, $issue);
                    }
                }
            }
            // Reset forced language.
            force_current_language($currentlang);

            if ($return) {
                return $pdf->Output('', 'S');
            }
            if (defined('PHPUNIT_TEST') && PHPUNIT_TEST) {
                // For some reason phpunit on travis-ci.com do not return 'cli' on php_sapi_name().
                echo $pdf->Output($filename, 'S');
            } else {
                $pdf->Output($filename);
            }
        }
    }

    /**
     * Gets the stored file for an issue. If issue file doesn't exist new file is created.
     *
     * @param \stdClass $issue
     * @return \stored_file
     */
    public function get_issue_file(\stdClass $issue): \stored_file {
        $fs = get_file_storage();
        $file = $fs->get_file(
            \context_system::instance()->id,
            'tool_certificate',
            'issues',
            $issue->id,
            '/',
            $issue->code . '.pdf'
        );
        if (!$file) {
            $file = $this->create_issue_file($issue);
        }
        return $file;
    }

    /**
     * Gets the stored file url for an issue. If issue file doesn't exist, new file is created first.
     *
     * @param \stdClass $issue
     * @return moodle_url
     */
    public function get_issue_file_url(\stdClass $issue): moodle_url {
        $file = $this->get_issue_file($issue);
        // We add timemodified instead of issue id to prevent caching of changed certificate.
        // The callback tool_certificate_pluginfile() ignores the itemid and only takes the code.
        return moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
            $file->get_timemodified(), $file->get_filepath(), $issue->code . '.pdf');
    }

    /**
     * Creates stored file for an issue.
     *
     * @param \stdClass $issue
     * @param bool $regenerate
     * @return \stored_file
     */
    public function create_issue_file(\stdClass $issue, bool $regenerate = false): \stored_file {
        // TODO: comprobar de que modelo se trata para coger el idioma definido.
        // A partir del $issue->code , tendré una tabla en la que asocio idioma con codigo, y asi sabré cual tengo que escoger para generar el pdf.
        $lang = 'en';
        // Generate issue pdf contents.
        $filecontents = $this->generate_pdf(false, $issue, true, $lang);
        // Create a file instance.
        $file = (object) [
            'contextid' => \context_system::instance()->id,
            'component' => 'tool_certificate',
            'filearea'  => 'issues',
            'itemid'    => $issue->id,
            'filepath'  => '/',
            'filename'  => $issue->code . '.pdf',
        ];
        $fs = get_file_storage();

        // If file exists and $regenerate=true, delete current issue file.
        $storedfile = $fs->get_file($file->contextid, $file->component, $file->filearea, $file->itemid, $file->filepath,
            $file->filename);
        if ($storedfile && $regenerate) {
            $storedfile->delete();
        }

        return $fs->create_file_from_string($file, $filecontents);
    }
}
