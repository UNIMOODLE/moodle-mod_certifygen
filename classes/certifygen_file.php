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
    private int $courseid;
    private int $userid;
    private int $modelid;
    private string $lang;

    /**
     * @throws coding_exception
     */
    public function __construct(stored_file $file, int $userid, string $lang, int $modelid) {
        $this->file = $file;
        $context = context::instance_by_id($this->file->get_contextid());
        $this->courseid = $context->instanceid;
        $this->userid = $userid;
        $this->lang = $lang;
        $this->modelid = $modelid;
    }

    /**
     * @return stdClass
     * @throws dml_exception
     */
    public function get_course() : stdClass {
        return get_course($this->courseid);
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
     * @return array
     * @throws dml_exception
     */
    public function get_metadata() : array {
        return [
            'userid' => $this->userid,
            'courseid' => $this->courseid,
            'coursefullname' => $this->get_course()->fullname,
            'lang' => $this->lang,
            'contentfile' => $this->file->get_content(),
            'modelid' => $this->modelid
        ];
    }
}