<?php
// After Moodle 4.2 external classes were moved.
if ($CFG->version >= 2023042400) {
    class_alias('core_external\external_api', 'external_api');
    class_alias('core_external\external_description', 'external_description');
    class_alias('core_external\external_function_parameters', 'external_function_parameters');
    class_alias('core_external\external_multiple_structure', 'external_multiple_structure');
    class_alias('core_external\external_single_structure', 'external_single_structure');
    class_alias('core_external\external_value', 'external_value');
}
