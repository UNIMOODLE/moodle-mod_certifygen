# CertifyGen Releases

## 1.0.4 (Moodle 5.0 - 5.3)
- Moodle 5.0 to 5.3 support. This line requires Moodle 5.0; Moodle 4.5 stays on 1.0.3.
- Replaced the removed core/modal_factory module with the core/modal classes.
- Compatibility with tool_certificate 5.0.12 and later (create_issue_file signature).
- Fixed the FPDI library path in the electronic validation, which broke model management on Moodle 5.
- Replaced user_get_users_by_id(), deprecated in Moodle 5.3.
- Fixed course backups with user data, which failed with "Class system not found".
- Declared the properties that were created dynamically (deprecated since PHP 8.2).
- Revoking a certificate now also deletes the PDFs the plugin keeps for it.
- Restoring an activity with user data now gives each certificate its own copy in the restored activity, instead of pointing to the original one. On the same site the copy gets a new code and a regenerated PDF; certificates that cannot be regenerated (signed, externally validated or stored outside Moodle) are not restored.
- Fixed the model type selector, which no longer reacted to changes in Moodle 5.
- Migrated Bootstrap 4 markup to Bootstrap 5.
- Fixed a PHP 8 error when a user cannot add the activity in any course (issue #120).

## 1.0.3
- Fixed issue with certificate codes not displaying in the certificate listing.
- Updated language packs to reflect new features and fixes.
- Fix minor bugs and performance improvements.

## 1.0.2
- Added validation settings and help strings for certificate requests.
- Improved language pack grammar and terminology.

## 1.0.0
- Initial public release of the Unimoodle CertifyGen activity.
- Supports certificate emission, validation, and course-level management.
- Provides REST endpoints for external certificate consumption.