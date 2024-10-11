<img src="https://github.com/UNIMOODLE/p31_mod/blob/develop/pix/icon.png" width="160" >

#  Certifygen Custom Course Certificate Mod #

Generation of PDF certificates with connection to digital signature systems and modular storage

## Compatibility ##

The plugin has been tested on the following versions:

* Moodle 4.1.1 (Build: 20230116) - 2022112801.00

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

