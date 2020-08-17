<?php
/**
 * @author: Celeryness (Mick Powell)
 * @license: GPL V3
 *
 * The main class for this utility and the call to it below that with the
 * arguments passed on the command line.
 *
 * This utility is designed to read CSV files emitted via the Guatemalan Government Agency MSPAS @ https://tablerocovid.mspas.gob.gt/
 * and convert these into tabular format and convert to ND Json.
 *
 * For PHP versions 7.4 and above.
 *
 * This file is part of GuatemalaCovidData.
 *
 * GuatemalaCovidData is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GuatemalaCovidData is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GuatemalaCovidData.  If not, see <https://www.gnu.org/licenses/>.
 */
class FileWriteOptions
{
    /**
     * Works with the arrayToNDJSON function to set the new line
     * character for subsequent lines in the file, will always be assigned the 
     * ascii new line character 0x0A. The new line is necessary when there
     * is more than one line going to the file, it should separate records
     * and should not occur after the last record. 
     */
    var string $newLine = "";
    /**
     * The bytes written to the current file. If the $fileWriteLimit is not -1, and the limit will be exceeded, a new
     * file is created and this value reset to 0.
     */
    var int $bytesWritten = 0;
    /**
     * The limit in bytes that should be written to the file. This is defaulted to Google's web interface limit 
     * for BigQuery (10mb), but can be switched off or changed via user parameters.
     */
    var int $fileWriteLimit = 10000000;
    /**
     * The directory where the output will be created, defaults to a folder in the project's own folder
     */
    var string $outputDir = "for_upload";
    /**
     * The output file name. There may be multiple output files in the format "fileName-01.extension" if the 
     * fileWriteLimit property is set.
     */
    var string $outputFileName = "casos_integrados";
    /**
     * File extension - stays as json as this is the only valid output right now.
     */
    var string $outputFileExtension = "json";
    /**
     * File count - if the file write limit is set, this will be incremented for each successive file created.
     */
    var int $fileCount = 1;

    /**
     * Returns the current file name depending on the settings for fileWriteLimit and fileCount
     * @return string
     */
    public function getFileName():string
    {
        $file = $this->outputDir.DIRECTORY_SEPARATOR.$this->outputFileName;
        if ($this->fileWriteLimit > -1)
        {
            $file .= "-".substr("0".$this->fileCount, -2, 2); 
        }
        return $file.".".$this->outputFileExtension;
    }
    /**
     * Checks if the next line to the file will be within the limit. If not it sets up for a new file.
     *
     * @param string $nextLine
     * @return void
     */
    public function checkFileBytes(string $nextLine):void
    {
        if ($this->fileWriteLimit > 0 && strlen($nextLine)+$this->bytesWritten > $this->fileWriteLimit)
        {
            $this->fileCount ++;
            $this->bytesWritten = 0;
        }
    }
    /**
     * Adds to the bytes written for the current file
     *
     * @param string $writtenLine
     * @return void
     */
    public function addBytesWritten(string $writtenLine):void
    {
        $this->bytesWritten += strlen($writtenLine);
    }

}

class GuateCovidData
{
    /**
     * Value used in exponential smoothing calculation
     */
    public const ALPHA = 0.2; 
    
    /**
     * Parses the three CSV files output on a daily basis by the government, and if they can be accessed without error
     * will read the values from each file into memory, merging those values, and then output a file in ND JSON format 
     * (see http://jsonlines.org/ for more info on the format). All files must be in the same date range, and must be
     * passed in the order specified.
     *
     * @param string $casosTamizadosCSV     The tested cases CSV for a given date range
     * @param string $casosConfirmadosCSV   The confirmed cases CSV for a given date range
     * @param string $casosFallecidosCSV    The CSV for number of people that have died for a given date range 
     * @return void
     */
    public static function parseCSV(string $casosTamizadosCSV, string $casosConfirmadosCSV, string $casosFallecidosCSV, bool $complete): void
    {
        $currentDir = getcwd();
        if (($confirmedHandle = self::checkAndOpenFile($casosConfirmadosCSV)) !== false) 
        {
            if (($testedHandle = self::checkAndOpenFile($casosTamizadosCSV)) !== false) 
            {
                if (($diedHandle = self::checkAndOpenFile($casosFallecidosCSV)) !== false) 
                {
                    $fileOptions = new FileWriteOptions();
                    $fileOptions->outputDir = "for_upload";
                    $fileOptions->fileName = "casos_integrados";
                    $fileOptions->fileExtension = "json";
                    if ($complete)
                    {
                        $fileOptions->fileWriteLimit = -1;
                    }

                    $logFile = $fileOptions->outputDir.DIRECTORY_SEPARATOR."anomalies.log";
                    if (file_exists($logFile))
                    {
                        unlink($logFile); 
                    }

                    $amalgamated = [];
                       

                    $labels = ['casos_tamizados', 'exp_tamizados', 'casos_confirmados', 'exp_confirmados','casos_fallecidos', 'exp_fallecidos'];
                    echo "\n[Leyendo el archivo ".substr($casosTamizadosCSV, 0, 45)."...]\n";
                    GuateCovidData::fileDataToArray($testedHandle, $labels, $amalgamated);
                    fclose($testedHandle);    

                    $labels = ['casos_confirmados', 'exp_confirmados'];
                    echo "\n[Leyendo el archivo ".substr($casosConfirmadosCSV, 0, 45)."...]\n";
                    GuateCovidData::fileDataToArray($confirmedHandle, $labels, $amalgamated);
                    fclose($confirmedHandle); 
        
                    $labels = ['casos_fallecidos', 'exp_fallecidos'];
                    echo "\n[Leyendo el archivo ".substr($casosFallecidosCSV, 0, 45)."...]\n";
                    GuateCovidData::fileDataToArray($diedHandle, $labels, $amalgamated);
                    fclose($diedHandle);        
                    if (self::checkUploadDirectory())
                    {
                   
                        echo "\n[Generando el archivo ".$fileOptions->getFileName()."]\n\n";
                        self::clearOldGeneratedFiiles($fileOptions);

                        foreach ($amalgamated as $key => $dept) 
                        {
                            foreach($dept as $muni => $valores)
                            {
                                if ($muni != "departamento")
                                {
                                    $poblacion = $dept[$muni]["poblacion"];
                                    foreach($dept[$muni]["datos"] as $date => $daily)
                                    {
                                        $record = ["departamento" => $dept["departamento"], "municipio" => $dept[$muni]["municipio"], "poblacion" => $poblacion]+$daily;
                                        self::arrayToNDJSON([$record], $fileOptions);
                                        $poblacion = 0;
                                    }
                                }
                            }
                        }
                    } 
                } 
                else 
                {
                    echo 'No se puede abrir el archivo '+$casosTamizadosCSV+', seguro que existe?';
                }
            } 
            else 
            {
                echo 'No se puede abrir el archivo '+$casosTamizadosCSV+', seguro que existe?';
            }
        } 
        else 
        {
            echo 'No se puede abrir el archivo '+$casosConfirmadosCSV+', seguro que existe?';
        }
    }
    /**
     * Adds to an array, or updates an array with, the key/value pair supplied. The array 
     * is passed by reference.
     * @param string $key   The key for an associative array
     * @param [type] $value The value to store for this key
     * @param array $record The array to update (by reference)
     * @return void
     */
    public static function addKeyValue(string $key, $value, array &$record)
    {
        $record[$key] = $value;
    }
    /**
     * Opens a file, and first checks to see if the file also has dirtectory info
     *
     * @param string $fileName
     * @return void
     */
    public static function checkAndOpenFile(string $fileName)
    {
        $handle = false;
        if (@file($fileName)) {
            if (($handle = fopen($fileName, 'r')) === false) {
                throw new Exception("El archivo " . $fileName . " no se puede abrir para leer.");
            }
        } else {
            throw new Exception("El archivo " . $fileName . " parece que no existe.");
        }
        return $handle;
    }
    public static function checkUploadDirectory():bool
    {
        if (!is_dir("for_upload"))
        {
            return mkdir("for_upload");
        }
        return true;
    }
    public static function clearOldGeneratedFiiles(FileWriteOptions $fileOptions):void
    {
        $fileList = glob($fileOptions->outputDir.DIRECTORY_SEPARATOR.$fileOptions->outputFileName."*.".$fileOptions->outputFileExtension);
        if ($fileList && count($fileList) > 0)
        {
            foreach ($fileList as $fileName)
            {
                unlink($fileName);
            }
        }
        
    }
    /**
     * Converts the data from a CSV file to an associative array structure. If that structure exists lines
     * in the file will be added to the relavant department/muni within the existing structure.
     * @param resource $handle          The file handle for the opened CSV file. 
     * @param array $labels             The field names for the items in this file, and the exponentially smoothed values.
     * @param array|null $amalgamate    If this is null it will be created. Used to store the values from the file (for
     *                                  the structure see amalgamateFileData below).
     * @return void
     */
    public static function fileDataToArray($handle, array $labels, ?array &$amalgamate):void
    {
        $datesAndHeaders = [];
        if ($amalgamate === null) {
            $amalgamate = [];
        }
        $line = 0;
        while (($data = fgetcsv($handle, 10000, ',')) !== false) 
        {
            if ($line === 0) 
            {
                $datesAndHeaders = $data;
            } 
            else 
            {
                self::amalgamateFileData($data, $amalgamate, $datesAndHeaders, $labels);
            }
            $line ++;
        }
    }
    /**
     * Adds to or updates an associative array stucture with values from a line in a CSV file. The array structure
     * is in a tree-like format:
     * DEPARTAMENTO -> MUNICIPIO -> DATOS DIARIOS
     *              -> MUNICIPIO -> DATOS DIARIOS
     * If the departamento does not exist it is added, the same with the municipios. There was at least one reference in
     * proper format in the files to Escuintla, but all other values are in capital letters and so all indexed departamento
     * and municipio values are stored in uppercase using mb_strtoupper().
     * @param array $data               The row of data from the CSV file
     * @param array $amalgamate         Associative array in which to store all values
     * @param array $datesAndHeaders    The first row of the CSV file for the headers and dates
     * @param array $labels             The field names for the values and exponentially smoothed values
     * @return void
     */
    public static function amalgamateFileData(array $data, array &$amalgamate, array $datesAndHeaders, array $labels):void
    {
        $departamento =  mb_strtoupper($data[0]);
        $deptCode = $data[1];
        $municipio = mb_strtoupper($data[2]);
        $muniCode = $data[3];
        if (!array_key_exists($deptCode, $amalgamate)) 
        {
            $amalgamate[$deptCode] = [$datesAndHeaders[0] => $departamento];
            self::logMisses($labels, $departamento);
        }
        if (!array_key_exists($muniCode, $amalgamate[$deptCode])) 
        {
            $amalgamate[$deptCode][$muniCode] = [$datesAndHeaders[2] => $municipio, $datesAndHeaders[4] => intval($data[4]), "datos" => []];
            self::logMisses($labels, $departamento ." - ".$municipio);
        }

        $num = count($data);
        $lastAverageValue = 0;
        $currentValue = 0;
        
        for ($i = 5; $i < $num; $i++) 
        {
            if (!array_key_exists($datesAndHeaders[$i], $amalgamate[$deptCode][$muniCode]["datos"]))
            {
                $amalgamate[$deptCode][$muniCode]["datos"][$datesAndHeaders[$i]] = [];
                self::logMisses($labels, $departamento." - ".$municipio." - ".$datesAndHeaders[$i] );
            }
            $record = &$amalgamate[$deptCode][$muniCode]["datos"][$datesAndHeaders[$i]] ;
            self::addKeyValue('fecha', $datesAndHeaders[$i], $record);
            $currentValue = intval($data[$i]);
            self::addKeyValue($labels[0], $currentValue, $record);
            $lastAverageValue = self::ALPHA * $currentValue + (1 - self::ALPHA) * $lastAverageValue;
            self::addKeyValue($labels[1], $lastAverageValue, $record);
            if (count($labels) > 2)
            {
                for ($x=2; $x < count($labels); $x++) 
                { 
                    if ($x & 1)
                    {
                        self::addKeyValue($labels[$x], 0.00, $record);
                    }
                    else
                    {
                        self::addKeyValue($labels[$x], 0, $record);
                    }
                }
            }
        }

    }
    /**
     * Formats the array passed into newline delimited JSON (NDJSON). The function will work
     * with multiple array rows or just one row at a time (as in this project).
     * @param array             $array      The array  - one or two dimensional will work
     * @param FileWriteOptions  $options    See the class definition
     * @return void
     */
    public static function arrayToNDJSON(array $array, FileWriteOptions $options):void
    {
        $count = count($array);
        
        for ($i = 0; $i < $count; $i++) 
        {
            $line = json_encode($array[$i]);
            $nextLine = $options->newLine .$line;
            $options->checkFileBytes($nextLine);
            file_put_contents($options->getFileName(), $nextLine, FILE_APPEND);
            $options->addBytesWritten($nextLine);
            $options->newLine = chr(10);
        }
    }
    public static function logMisses(array $labels, string $data):void
    {
        if (count($labels) == 2)
        {
            $message = $data." - ".$labels[0];
            self::log($message);
        }
    }
    public static function log($message, $logFile = "for_upload/anomalies.log")
    {
        $message = date('Y-m-d H:i:s')." ".$message.PHP_EOL;
        return file_put_contents($logFile, $message, FILE_APPEND);
        
    }
        
         
        
        
}

// quick and dirty exception handler
function onException($exception)
{
    echo "\nERROR: Perdon " . $exception->getMessage() . "\n\n";
}
set_exception_handler('onException');

// Only run a command if paramaters have been provided
if (isset($argc) && $argc > 3) {
    ini_set('auto_detect_line_endings', true);
    $complete = false;
    if ($argc > 4)
    {
        if ($argv[4] == "--completo")
        {
            $complete = true;
        }
    }
    GuateCovidData::parseCSV($argv[1], $argv[2], $argv[3], $complete);
}
