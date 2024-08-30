<img src="https://github.com/UNIMOODLE/p31_mod/blob/develop/pix/icon.png" width="160" >

#  Certifygen Custom Course Certificate Mod #

## Compatibility ##

The plugin has been tested on the following versions:

* Moodle 4.1.1 (Build: 20230116) - 2022112801.00
* Moodle 3.11.17+ (Build: 20231124) - 2021051717.06

## Requirements ##

* User configuration and REST Web Services
* User for web service must have 'report/completion:view' capability.

## Languages ##

* English
* Spanish

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
$ php admin/cli/upgrade.php
```
to complete the installation from the command line.
## Global Configuration ##
## CLI Executions ##
## Subplugins ##

### certifygenvalidation ###
* certifygenvalidation_cmd 
* certifygenvalidation_csv 
* certifygenvalidation_webservice 
### certifygenreport ###
* certifygenreport_basic 
### certifygenrepository ###
* certifygenrepository_localrepository 
* certifygenrepository_onedrive 


### Tasks ###

## Check status ##
There is a task, checkstatus, that is recomended to enable it when the validation subplugin used does not validate inmediately the certificate.
This task verify the status of the certificate on the external aplication used by the validation subplugin.

## Check file ##
There is a task, checkfile, that is recomended to enable it when the validation subplugin used does not receive inmediately the certificate.
This task get the certificate from the external aplication used by the validation subplugin.

   
## Database Tables ##

* __certifygen__
  
Contains definitions about certifygens
* __certifygen_model__
  
Contains information about stores each certifygen models
* __certifygen_context__
  
Contains information about stores each context of a certifygen
* __certifygen_validations__
  
Contains information about stores each validation of a certifygen
## Unit Test ##

ES

<img src="https://github.com/UNIMOODLE/p31_mod/blob/develop/pix/icon.png" width="160" >

#  P31 Certifygen custom Course Certificate mod #

## Compatibilidad ##



