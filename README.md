<img src="pix/icon.png" width="160">

#  Certifygen Custom Course Certificate Mod #

Generation of PDF certificates with connection to digital signature systems and modular storage.
## Plugin Capabilities ##

This plugin defines an activity that can be added to a course and controlled by availability restrictions. Users can request their certificates directly, while academic management systems can invoke web services (WS) to verify conditions and generate certificates externally. The plugin leverages the template editor provided by the `tool_certificate` plugin.

### Validation Subplugins ###
The plugin supports signing certificates using the following validation subplugins:
* **certifygenvalidation_none**: No signature is applied.
* **certifygenvalidation_cmd**: Certificates are signed locally using a command.
* **certifygenvalidation_csv**: Certificates are signed using an external Web Service that generates a PDF with a secure verification code and validation URL printed on a PDF. This kind of signature is commonly used in academic institutions for easy validation in paper formats.
* **certifygenvalidation_electronic**: Certificates are signed locally with a private-key installed in the server.

### Repository Subplugins ###
Certificates are stored in repositories that can be managed using the following repository subplugins:
* **certifygenrepository_localrepository**: Certificates are stored locally within the Moodle instance.
* **certifygenrepository_csv**: Certificates are stored in an external certification repository that resolves the URLs of the CSV PDFs.
* **certifygenrepository_onedrive**: Certificates are stored in a OneDrive repository for cloud-based access.

This modular design allows for flexible integration with external systems and customizable storage options.

## Compatibility ##

The plugin has been tested on the following versions:

* Moodle 4.1.1 (Build: 20230116) - 2022112801.00
* Moodle 4.5.0 (Build: 20240419) - 2024041900.00

## Requirements ##

* User configuration and REST Web Services
* Admin tool certificate v2024042300 or higher.

## Languages ##

* English
* Spanish
* Catalan
* Euskera
* Galego

## Installation via uploaded ZIP file ##

1. Log in to your Moodle site as an administrator and go to Site Administration > Plugins > Install plugins.
1. Upload the ZIP file with the plugin code. You should only be asked to add additional details if your plugin type is not automatically detected.
1. Verify the plugin validation report and complete the installation.

## Manual Installation ##

The plugin can also be installed by placing the contents of this directory in
```
{your/moodle/dirroot}/mod/certifygen
```
Then, log in to your Moodle site as an administrator and go to Site Administration > Notifications to complete the installation.

Alternatively, you can run
```
php admin/cli/upgrade.php
```
to complete the installation from the command line.
## Global Configuration ##
```
{your/moodle/dirroot}/admin/settings.php?section=modsettingcertifygen
```
## CLI Executions ##
## Subplugins ##

### Certifygen validation ###
* certifygenvalidation_cmd
* certifygenvalidation_csv
* certifygenvalidation_electronic
* certifygenvalidation_none
### Certifygen report ###
* certifygenreport_basic
### Certifygen repository ###
* certifygenrepository_csv
* certifygenrepository_localrepository
* certifygenrepository_onedrive


### Tasks ###

## Check status ##
There is a task, checkstatus, that is recomended to enable it when the validation subplugin used does not validate inmediately the certificate.
This task verify the status of the certificate on the external aplication used by the validation subplugin.

```
php {your/moodle/dirroot}/admin/cli/scheduled_task.php --execute=\mod_certifygen\task\checkstatus
```
## Check file ##
There is a task, checkfile, that is recomended to enable it when the validation subplugin used does not receive inmediately the certificate.
This task get the certificate from the external aplication used by the validation subplugin.
```
php {your/moodle/dirroot}/admin/cli/scheduled_task.php --execute=\mod_certifygen\task\checkfile
```
## Check error ##
There is a task, checkerror. It is responsible for searching for error states in the validation processes and returning them to the not started state, so that the user can start the process again.
```
php {your/moodle/dirroot}/admin/cli/scheduled_task.php --execute=\mod_certifygen\task\checkerror
```
## Database Tables ##

* __certifygen__

Contains definitions about certifygens
* __certifygen_model__

Contains information about stores each certifygen models
* __certifygen_context__

Contains information about stores each context of a certifygen
* __certifygen_validations__

Contains information about stores each validation of a certifygen

* __certifygen_repository__

Contains information about sotores certificate url

* __certifygen__error__

Contains information about stores certificate errors


## Unit Test ##

You can run unit tests manually with the following command
```
php {your/moodle/dirroot}/vendor/bin/phpunit --testsuite mod_certifygen_testsuite
```
