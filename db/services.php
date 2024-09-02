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
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_certifygen\external\deletemodel_external;
use mod_certifygen\external\emitcertificate_external;
use mod_certifygen\external\downloadcertificate_external;
use mod_certifygen\external\get_courses_as_teacher_external;
use mod_certifygen\external\get_courses_as_student_external;
use mod_certifygen\external\get_json_teacher_certificate_external;
use mod_certifygen\external\get_pdf_teacher_certificate_external;
use mod_certifygen\external\getmycertificatedata_external;
use mod_certifygen\external\getmodellisttable_external;
use mod_certifygen\external\searchcategory_external;
use mod_certifygen\external\searchcourse_external;
use mod_certifygen\external\searchmycourses_external;
use mod_certifygen\external\revokecertificate_external;
use mod_certifygen\external\get_id_instance_certificate_external;
use mod_certifygen\external\get_json_certificate_external;
use mod_certifygen\external\get_pdf_certificate_external;
use mod_certifygen\external\getteacherrequestviewdata_external;
use mod_certifygen\external\deleteteacherrequest_external;
use mod_certifygen\external\getcoursesnames_external;
use mod_certifygen\external\emitteacherrequest_external;
use mod_certifygen\external\reemitteacherrequest_external;
use mod_certifygen\external\downloadteachercertificate_external;

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_certifygen_downloadteachercertificate' => [
        'classname' => downloadteachercertificate_external::class,
        'methodname' => 'downloadteachercertificate',
        'description' => 'downloadteachercertificate',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:view',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_emitteacherrequest' => [
        'classname' => emitteacherrequest_external::class,
        'methodname' => 'emitteacherrequest',
        'description' => 'emitteacherrequest',
        'type' => 'write',
        'capabilities' => 'mod/certifygen:view',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_reemitteacherrequest' => [
        'classname' => reemitteacherrequest_external::class,
        'methodname' => 'reemitteacherrequest',
        'description' => 'reemitteacherrequest',
        'type' => 'write',
        'capabilities' => 'mod/certifygen:view',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_deleteteacherrequest' => [
        'classname' => deleteteacherrequest_external::class,
        'methodname' => 'deleteteacherrequest',
        'description' => 'deleteteacherrequest',
        'type' => 'write',
        'capabilities' => 'mod/certifygen:view',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_getcoursesnames' => [
        'classname' => getcoursesnames_external::class,
        'methodname' => 'getcoursesnames',
        'description' => 'getcoursesnames',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:view',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_getteacherrequestviewdata' => [
        'classname' => getteacherrequestviewdata_external::class,
        'methodname' => 'getteacherrequestviewdata',
        'description' => 'getteacherrequestviewdata',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:view',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_revokecertificate' => [
        'classname' => revokecertificate_external::class,
        'methodname' => 'revokecertificate',
        'description' => 'Delete a certificate',
        'type' => 'write',
        'capabilities' => 'mod/certifygen:canmanagecertificates',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_deletemodel' => [
        'classname' => deletemodel_external::class,
        'methodname' => 'deletemodel',
        'description' => 'Delete a model',
        'type' => 'write',
        'capabilities' => 'mod/certifygen:manage',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_getmodellisttable' => [
        'classname' => getmodellisttable_external::class,
        'methodname' => 'getmodellisttable',
        'description' => 'Get model list table',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_searchcategory' => [
        'classname' => searchcategory_external::class,
        'methodname' => 'searchcategory',
        'description' => 'Search category',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_searchcourse' => [
        'classname' => searchcourse_external::class,
        'methodname' => 'searchcourse',
        'description' => 'Search course',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_searchmycourses' => [
        'classname' => searchmycourses_external::class,
        'methodname' => 'searchmycourses',
        'description' => 'Search my courses',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:viewmycontextcertificates',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_emitcertificate' => [
        'classname' => emitcertificate_external::class,
        'methodname' => 'emitcertificate',
        'description' => 'Emit Certificate',
        'type' => 'write',
        'capabilities' => 'mod/certifygen:view',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_downloadcertificate' => [
        'classname' => downloadcertificate_external::class,
        'methodname' => 'downloadcertificate',
        'description' => 'Download Certificate',
        'type' => 'write',
        'capabilities' => 'mod/certifygen:view',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_getmycertificatedata' => [
        'classname' => getmycertificatedata_external::class,
        'methodname' => 'getmycertificatedata',
        'description' => 'Get mycertificatedata table',
        'type' => 'write',
        'capabilities' => 'mod/certifygen:view',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_certifygen_get_pdf_teacher_certificate' => [
        'classname' => get_pdf_teacher_certificate_external::class,
        'methodname' => 'get_pdf_teacher_certificate',
        'description' => 'get Pdf Teaching',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
    ],
    'mod_certifygen_get_json_teacher_certificate' => [
        'classname' => get_json_teacher_certificate_external::class,
        'methodname' => 'get_json_teacher_certificate',
        'description' => 'get_json_teacher_certificate',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
    ],
//    'mod_certifygen_getPdfStudentCourseCompleted' => [
//        'classname' => getPdfStudentCourseCompleted_external::class,
//        'methodname' => 'getPdfStudentCourseCompleted',
//        'description' => 'get Pdf Student Course Completed',
//        'type' => 'read',
//        'capabilities' => 'mod/certifygen:manage',
//    ],
//    'mod_certifygen_getJsonStudentCourseCompleted' => [
//        'classname' => getJsonStudentCourseCompleted_external::class,
//        'methodname' => 'getJsonStudentCourseCompleted',
//        'description' => 'get Json Student Course Completed',
//        'type' => 'read',
//        'capabilities' => 'mod/certifygen:manage',
//    ],
    'mod_certifygen_get_courses_as_student' => [
        'classname' => get_courses_as_student_external::class,
        'methodname' => 'get_courses_as_student',
        'description' => 'get Courses As Student',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
    ],
    'mod_certifygen_get_courses_as_teacher' => [
        'classname' => get_courses_as_teacher_external::class,
        'methodname' => 'get_courses_as_teacher',
        'description' => 'get Courses As Teacher',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
    ],
    'mod_certifygen_get_id_instance_certificate' => [
        'classname' => get_id_instance_certificate_external::class,
        'methodname' => 'get_id_instance_certificate',
        'description' => 'get a list of instances of mod_certifygen',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
    ],
    'mod_certifygen_get_json_certificate' => [
        'classname' => get_json_certificate_external::class,
        'methodname' => 'get_json_certificate',
        'description' => 'get_json_certificate',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
    ],
    'mod_certifygen_get_pdf_certificate' => [
        'classname' => get_pdf_certificate_external::class,
        'methodname' => 'get_pdf_certificate',
        'description' => 'get_pdf_certificate',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
    ],
];
$services = [
    'Unimoodle Certifygen' => [
        'functions' => [
            'mod_certifygen_deletemodel',
            'mod_certifygen_getmodellisttable',
            'mod_certifygen_get_pdf_certificate',
            'mod_certifygen_get_json_certificate',
            'mod_certifygen_get_id_instance_certificate',
            'mod_certifygen_get_courses_as_teacher',
            'mod_certifygen_get_courses_as_student',
            'mod_certifygen_get_json_teacher_certificate',
            'mod_certifygen_get_pdf_teacher_certificate',
//            'mod_certifygen_getPdfTeaching',
//            'mod_certifygen_getJsonTeaching',
//            'mod_certifygen_getPdfStudentCourseCompleted',
//            'mod_certifygen_getJsonStudentCourseCompleted',
        ]
    ]
];