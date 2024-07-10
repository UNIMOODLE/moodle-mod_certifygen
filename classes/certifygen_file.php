<?php

namespace mod_certifygen;
global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
use coding_exception;
use context;
use dml_exception;
use stdClass;
use stored_file;

class certifygen_file {
    private stored_file $file;
    private stdClass $course;
    private int $userid;
    private int $modelid;
    private int $validationid; // validationid or teacherrequestid.
    private string $lang;
    private array $metadata;

    /**
     * @throws coding_exception
     */
    public function __construct(stored_file $file, int $userid, string $lang, int $modelid, int $validationid) {
        $this->file = $file;
//        $this->course = $course;
        $this->userid = $userid;
        $this->lang = $lang;
        $this->modelid = $modelid;
        $this->validationid = $validationid;
    }

    /**
     * @return stdClass
     * @throws dml_exception
     */
//    public function get_course() : stdClass {
//        return $this->course;
//    }
    /**
     * @return int
     */
    public function get_validationid() : int {
        return $this->validationid;
    }
    /**
     * @return stored_file
     */
    public function get_file() : stored_file {
        return $this->file;
    }

    /**
     * @return stdClass
     */
    public function get_user() : stdClass {
        $users = user_get_users_by_id([$this->userid]);
        return reset($users);
    }

    /**
     * @param array $data
     * @return void
     */
    public function set_metadata(array $data) : void {
        $this->metadata = $data;
    }
    /**
     * @return array
     * @throws dml_exception
     */
    public function get_metadata() : array {
        return $this->metadata;
//        return [
//            'userid' => $this->userid,
//            'courseid' => $this->course->id,
//            'coursefullname' => $this->get_course()->fullname,
//            'lang' => $this->lang,
//            'contentfile' => $this->file->get_content(),
//            'modelid' => $this->modelid
//        ];
    }
}