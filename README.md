# GuateCovidData
A PHP utility to convert the CSVs produced daily by the Ministry of Health in Guatemala (MSPAS) to NDJSON format as one integrated table for uploading to services like Google BigQuery or Amazon Redshift.

Please note: this utility was written in PHP 7.4 and uses features such as variable typing that have only been available since PHP 7.4. Therefore you must have PHP 7.4 (or greater) available on the terminal/command line to use this utility.

### Usage:

The files need to passed to the script as parameters, and must be in the order shown in the example below. The file(s) produced will be sent to the for_upload folder in which the script resides.

#### Example:
```
php GuateCovidData.php "/Downloads/Tamizados por municipio, fecha de emisión de resultado del 2020-02-13 al 2020-08-14.csv" "/Downloads/Confirmados por municipio, fecha de emisión de resultado del 2020-02-13 al 2020-08-14.csv" "/Downloads/Fallecidos por municipio, fecha de fallecimiento del 2020-02-13 al 2020-08-14.csv"
```

The script also understands an extra parameter --completo. By default the output will be files broken up into 10mb chunks (the limit for uploading to BigQuery via it’s web interface), passing the --completo  parameter which will not break the output file into 10mb chunks and will place all data into one output file.
