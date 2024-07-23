<?php

namespace certifygenvalidation_csv\persistents;

use core\persistent;

class certifygenvalidationcsv extends persistent
{
    public const TABLE = 'certifygen_validationcsv';

    protected static function define_properties(): array {
        return [
            'validationid' => [
                'type' => PARAM_INT,
                'default' => 0
            ],
            'applicationid' => [
                'type' => PARAM_RAW,
            ],
            'token' => [
                'type' => PARAM_RAW,
            ],
            'usermodified' => [
                'type' => PARAM_INT,
            ],
        ];
    }
}