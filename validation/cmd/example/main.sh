#!/usr/bin/bash

filename=$1
user_json=$2

user_id=$(echo $user_json | jq -r '.user_id')
user_fullname=$(echo $user_json | jq -r '.user_fullname') 
course_fullname=$(echo $user_json | jq -r '.course_fullname')
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

##creo el fichero
printf "%s\n" "Datos del alumno:\n Nombre: $user_fullname.\n Datos del curso: $course_fullname\n" > ${DIR}/"$filename"

## Devuelvo el contenido del documento.
filecontent="`cat ${DIR}/$filename`"
echo $filecontent
