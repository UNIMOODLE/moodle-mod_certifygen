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


use coding_exception;
use context_course;
use context_system;
use core\lock\lock;
use core\message\message;
use core_user;
use dml_exception;
use file_exception;
use moodle_url;
use pdf;
use stdClass;
use stored_file;
use stored_file_creation_exception;
use tool_certificate\certificate;
use tool_certificate\customfield\issue_handler;
use tool_certificate\event\certificate_issued;

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
    private string $lang;

    /**
     * @param int $id
     * @param stdClass|null $obj
     * @return template
     * @throws coding_exception
     */

    public static function instance(int $id = 0, ?stdClass $obj = null): template
    {
        $data = new stdClass();
        if ($obj !== null) {
            $lang = $obj->lang;
            // Ignore fields that are not properties.
            $data = (object)array_intersect_key((array)$obj, \tool_certificate\persistent\template::properties_definition());
        }
        $t = new self();
        $t->persistent = new \tool_certificate\persistent\template($id, $data);
        $t->lang = $lang ?? '';
        return $t;
    }

    /**
     * Generate the PDF for the template.
     * @param bool $preview True if it is a preview, false otherwise
     * @param stdClass $issue The issued certificate we want to view
     * @param bool $return
     * @return string|null Return the PDF as string if $return specified
     * @throws dml_exception
     */
    public function generate_pdf($preview = false, $issue = null, $return = false) {
        global $CFG, $USER;

        if (is_null($issue)) {
            $user = $USER;
        } else {
            $user = core_user::get_user($issue->userid);
        }

        require_once($CFG->libdir . '/pdflib.php');

        // Get the pages for the template, there should always be at least one page for each template.
        if ($pages = $this->get_pages()) {
            // Create the pdf object.
            $pdf = new pdf();
            $currentlang = force_current_language($this->lang);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetTitle($this->get_formatted_name());
            $pdf->setViewerPreferences([
                'DisplayDocTitle' => true,
            ]);
            $pdf->SetAutoPageBreak(true);
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
        return null;
    }

    /**
     * Gets the stored file for an issue. If issue file doesn't exist new file is created.
     * @param stdClass $issue
     * @return stored_file
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    public function get_issue_file(stdClass $issue): stored_file {
        $fs = get_file_storage();
        $file = $fs->get_file(
            context_system::instance()->id,
            'mod_certifygen',
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
     * @param stdClass $issue
     * @return moodle_url
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    public function get_issue_file_url(stdClass $issue): moodle_url {
        $file = $this->get_issue_file($issue);
        // We add timemodified instead of issue id to prevent caching of changed certificate.
        // The callback tool_certificate_pluginfile() ignores the itemid and only takes the code.
        return moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
            $file->get_itemid(), $file->get_filepath(), $issue->code . '.pdf');
    }

    /**
     * Creates stored file for an issue.
     * @param stdClass $issue
     * @param bool $regenerate
     * @return stored_file
     * @throws file_exception
     * @throws stored_file_creation_exception
     * @throws dml_exception
     */
    public function create_issue_file(stdClass $issue, bool $regenerate = false): stored_file {

        // Generate issue pdf contents.
        $filecontents = $this->generate_pdf(false, $issue, true);
        // Create a file instance.
        $file = (object) [
            'contextid' => context_system::instance()->id,
            'component' => 'mod_certifygen',
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

    /**
     * Issues a certificate to a user.
     *
     * @param $userid
     * @param null $expires
     * @param array $data
     * @param $component
     * @param null $courseid
     * @param lock|null $lock
     * @return int The ID of the issue
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     * @uses \tool_tenant\config::push_for_user()
     * @uses \tool_tenant\config::pop()
     *
     */
    public function issue_certificate($userid, $expires = null, array $data = [], $component = 'mod_certifygen',
                                      $courseid = null, ?lock $lock = null) : int {
        global $DB;
        error_log(__FUNCTION__. ' ' . __LINE__);
//        component_class_callback(tool_tenant\config::class, 'push_for_user', [$userid]);

        $issue = new stdClass();
        $issue->userid = $userid;
        $issue->templateid = $this->get_id();
        $issue->code = certificate::generate_code($issue->userid) . '_' . $this->lang ;
        $issue->emailed = 0;
        $issue->timecreated = time();
        $issue->expires = $expires;
        $issue->component = $component;
        $issue->courseid = $courseid;
        $issue->archived = 0;

        // Store user fullname.
        $data['userfullname'] = fullname($DB->get_record('user', ['id' => $userid]));
        $issue->data = json_encode($data);

        // Insert the record into the database.
        $issue->id = $DB->insert_record('tool_certificate_issues', $issue);
//        if ($lock) {
//            error_log(__FUNCTION__ . ' lock released ' . __LINE__);
//            $lock->release();
//        }
        issue_handler::create()->save_additional_data($issue, $data);

        // Trigger event.
        certificate_issued::create_from_issue($issue)->trigger();

        // Reload issue from DB in case the event handlers modified it.
        $issue = $this->get_issue_from_code($issue->code);

        // Create the issue file and send notification.
        $issuefile = $this->create_issue_file($issue);
        self::send_issue_notification($issue, $issuefile);

//        component_class_callback(tool_tenant\config::class, 'pop', []);

        return $issue->id;
    }

    /**
     * Sends a moodle notification of the certificate issued.
     *
     * @param stdClass $issue
     * @param stored_file $file
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    private function send_issue_notification(stdClass $issue, stored_file $file): void {
        global $DB;

        $user = core_user::get_user($issue->userid);
        $userfullname = fullname($user, true);
        $mycertificatesurl = new moodle_url('/admin/tool/certificate/my.php');
        $subject = get_string('notificationsubjectcertificateissued', 'tool_certificate');
        $fullmessage = get_string(
            'notificationmsgcertificateissued',
            'mod_certifygen',
            ['fullname' => $userfullname, 'url' => $mycertificatesurl->out(false)]
        );

        $message = new message();
        $message->courseid = $issue->courseid ?? SITEID;
        $message->component = 'mod_certifygen';
        $message->name = 'certificateissued';
        $message->notification = 1;
        $message->userfrom = core_user::get_noreply_user();
        $message->userto = $user;
        $message->subject = $subject;
        $message->contexturl = $mycertificatesurl;
        $message->contexturlname = get_string('mycertificates', 'tool_certificate');
        $message->fullmessage = html_to_text($fullmessage);
        $message->fullmessagehtml = $fullmessage;
        $message->fullmessageformat = FORMAT_HTML;
        $message->smallmessage = '';
        $message->attachment = $file;
        $message->attachname = $file->get_filename();

        if (message_send($message)) {
            $DB->set_field('tool_certificate_issues', 'emailed', 1, ['id' => $issue->id]);
        }
    }
}
