<?php

namespace mod_certifygen\output\views;
use mod_certifygen\tables\profile_my_certificates_table;
use renderable;
use stdClass;
use templatable;
use renderer_base;
class profile_my_certificates_view implements renderable, templatable
{

    private int $userid;
    private int $pagesize;
    /**
     * @param int $pagesize
     * @param bool $useinitialsbar
     */
    public function __construct(int $userid = 0, int $pagesize = 10) {
        global $USER;
        $this->userid = $userid;
        if (!$userid) {
            $this->userid = $USER->id;
        }
        $this->pagesize = $pagesize;
    }

    /**
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {

        global $USER;
        $data = new stdClass();

        $tablelist = new profile_my_certificates_table($this->userid);
        $tablelist->baseurl = new \moodle_url('/mod/certifygen/mycertificates.php');
        ob_start();
        // TODO: optional_params 10 and true
        $tablelist->out($this->pagesize, false);
        $out1 = ob_get_contents();
        ob_end_clean();
        $data->table = $out1;
        $data->userid = $this->userid;
        return $data;
    }
}