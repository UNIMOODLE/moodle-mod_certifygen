<?php
// After Moodle 4.2 external classes were moved.
if ($CFG->version >= 2023042400) {
    if (!class_exists('external_api')) {
        class_alias('core_external\external_api', 'external_api');
    }
    if (!class_exists('external_description')) {
        class_alias('core_external\external_description', 'external_description');
    }
    if (!class_exists('external_function_parameters')) {
        class_alias('core_external\external_function_parameters', 'external_function_parameters');
    }
    if (!class_exists('external_multiple_structure')) {
        class_alias('core_external\external_multiple_structure', 'external_multiple_structure');
    }
    if (!class_exists('external_single_structure')) {
        class_alias('core_external\external_single_structure', 'external_single_structure');
    }
    if (!class_exists('external_value')) {
        class_alias('core_external\external_value', 'external_value');
    }
}
