# GuateCovidData

1. [Español](#español)
2. [English](#english)

## Español

Un script PHP para convertir los archivos de formato CSV producido diariamente por el Ministerio de Salud en Guatemala (MSPAS) al formato NDJSON en la forma de una tabla integrada para subir a servicios como Google BigQuery o Amazon Redshift.

Favor de notar: este script fue escrito en PHP 7.4 y se usa funcionalidad como “variable typing” que solo ha estado disponible desde el PHP 7.4. Entonces, hay que tener PHP 7.4 (o mas) disponible en el terminal/cmd para que este script funciona.

### Uso

Los archivos CSV tienen que pasar al script como parámetros, y tienen que estar en el orden mostrado en el ejemplo abajo. Los archivo(s) producidos serian en el carpeta for_upload, en el directory donde se encuentra el script.

#### Ejemplo:

```
php GuateCovidData.php "/Downloads/Tamizados por municipio, fecha de emisión de resultado del 2020-02-13 al 2020-08-14.csv" "/Downloads/Confirmados por municipio, fecha de emisión de resultado del 2020-02-13 al 2020-08-14.csv" "/Downloads/Fallecidos por municipio, fecha de fallecimiento del 2020-02-13 al 2020-08-14.csv"
```

El script también entiende un parámetro extra –completo. Por predeterminado archivo(s) producido(s) serian por partes de 10mb cada uno (10mb es el limite para subir a BigQuery vía su interface web), si usted pasa el parámetro –completo,  el archivo producido seria uno, que contiene todos los datos.

## English

A PHP utility to convert the CSVs produced daily by the Ministry of Health in Guatemala (MSPAS) to NDJSON format as one integrated table for uploading to services like Google BigQuery or Amazon Redshift.

Please note: this utility was written in PHP 7.4 and uses features such as variable typing that have only been available since PHP 7.4. Therefore you must have PHP 7.4 (or greater) available on the terminal/command line to use this utility.

### Usage

The files need to passed to the script as parameters, and must be in the order shown in the example below. The file(s) produced will be sent to the for_upload folder in which the script resides.

#### Example:

```
php GuateCovidData.php "/Downloads/Tamizados por municipio, fecha de emisión de resultado del 2020-02-13 al 2020-08-14.csv" "/Downloads/Confirmados por municipio, fecha de emisión de resultado del 2020-02-13 al 2020-08-14.csv" "/Downloads/Fallecidos por municipio, fecha de fallecimiento del 2020-02-13 al 2020-08-14.csv"
```

The script also understands an extra parameter --completo. By default the output will be files broken up into 10mb chunks (the limit for uploading to BigQuery via it’s web interface), passing the --completo  parameter which will not break the output file into 10mb chunks and will place all data into one output file.


