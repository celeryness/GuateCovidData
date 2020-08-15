<?php
/**
 * @author: Mick Powell
 * @license: GPL V3
 *
 * A suite of PHPUnit test cases for the GuateCovidData class.
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

require_once "GuateCovidData.php";
use PHPUnit\Framework\TestCase;

class GuateCovidDataTests extends TestCase
{

    public function testCheckAndOpenFileBadName(): void
    {
        $file = "nonexistantfilenoway.xyz";
        try 
        {
            GuateCovidData::checkAndOpenFile($file);
        } 
        catch (Exception $exception) 
        {
            $this->assertStringContainsString($file, $exception->getMessage(), "Expecting an error with a message as the file should not exist.");
        }
    }
    public function testCheckAndOpenFileLockedFile(): void
    {
        $file = sys_get_temp_dir().DIRECTORY_SEPARATOR."imlocked.xyz";
        $handle = fopen($file, "w+");
        if (flock($handle, LOCK_EX)) 
        {
            try
            {
                GuateCovidData::checkAndOpenFile($file);
            } 
            catch (Exception $exception) 
            {
                $this->assertStringContainsString($file, $exception->getMessage(), "Expecting an error with a message as the file should not exist.");
            }
        } 
        else 
        {
            $this->assertFalse(true, "Expecting a lock on the generated file!");
        }
        if (file($file))
        {
            @fclose($handle);
            @unlink($file);
        }
    }
    public function testCheckAndOpenGoodFile(): void
    {
        $file = sys_get_temp_dir().DIRECTORY_SEPARATOR."imnotlocked.xyz";
        $handle = fopen($file, "w+");
        
        try
        {
            GuateCovidData::checkAndOpenFile($file);
        } 
        catch (Exception $exception) 
        {
            $this->assertStringContainsString($file, $exception->getMessage(), "Expecting an error with a message as the file should not exist.");
        }
        
        if (file($file))
        {
            @fclose($handle);
            @unlink($file);
        }
    }
    public function testClearOldGeneratedFiles():void
    {
        $dir = sys_get_temp_dir();
        $file = "testGCD.json";
        $file_01 = "testGCD-01.json";
        $file_02 = "testGCD-02.json";
        $file_03 = "testGCD-03.json";

        file_put_contents($dir.DIRECTORY_SEPARATOR.$file, "test file");
        file_put_contents($dir.DIRECTORY_SEPARATOR.$file_01, "test file 01");
        file_put_contents($dir.DIRECTORY_SEPARATOR.$file_02, "test file 02");
        file_put_contents($dir.DIRECTORY_SEPARATOR.$file_03, "test file 03");

        $this->assertTrue(file_exists($dir.DIRECTORY_SEPARATOR.$file), "Expecting testGCD .json to exist.");
        $this->assertTrue(file_exists($dir.DIRECTORY_SEPARATOR.$file_01), "Expecting testGCD-01.json to exist.");
        $this->assertTrue(file_exists($dir.DIRECTORY_SEPARATOR.$file_02), "Expecting testGCD-02.json to exist.");
        $this->assertTrue(file_exists($dir.DIRECTORY_SEPARATOR.$file_03), "Expecting testGCD-03.json to exist.");

        $options = new FileWriteOptions();
        $options->outputDir = $dir;
        $options->outputFileName = "testGCD";
        GuateCovidData::clearOldGeneratedFiiles($options);

        $this->assertFalse(file_exists($dir.DIRECTORY_SEPARATOR.$file), "Expecting testGCD.json to not exist.");
        $this->assertFalse(file_exists($dir.DIRECTORY_SEPARATOR.$file_01), "Expecting testGCD-01.json to not exist.");
        $this->assertFalse(file_exists($dir.DIRECTORY_SEPARATOR.$file_02), "Expecting testGCD-02.json to not exist.");
        $this->assertFalse(file_exists($dir.DIRECTORY_SEPARATOR.$file_03), "Expecting testGCD-03.json to not exist.");

    }
    public function testAmalgamateFileData():void
    {
        $csv_headers = '"departamento","codigo_departamento","municipio","codigo_municipio","poblacion","2020-02-13","2020-02-14","2020-02-15","2020-02-16","2020-02-17","2020-02-18","2020-02-19","2020-02-20","2020-02-21","2020-02-22","2020-02-23","2020-02-24","2020-02-25","2020-02-26","2020-02-27","2020-02-28","2020-02-29","2020-03-01","2020-03-02","2020-03-03","2020-03-04","2020-03-05","2020-03-06","2020-03-07","2020-03-08","2020-03-09","2020-03-10","2020-03-11","2020-03-12","2020-03-13","2020-03-14","2020-03-15","2020-03-16","2020-03-17","2020-03-18","2020-03-19","2020-03-20","2020-03-21","2020-03-22","2020-03-23","2020-03-24","2020-03-25","2020-03-26","2020-03-27","2020-03-28","2020-03-29","2020-03-30","2020-03-31","2020-04-01","2020-04-02","2020-04-03","2020-04-04","2020-04-05","2020-04-06","2020-04-07","2020-04-08","2020-04-09","2020-04-10","2020-04-11","2020-04-12","2020-04-13","2020-04-14","2020-04-15","2020-04-16","2020-04-17","2020-04-18","2020-04-19","2020-04-20","2020-04-21","2020-04-22","2020-04-23","2020-04-24","2020-04-25","2020-04-26","2020-04-27","2020-04-28","2020-04-29","2020-04-30","2020-05-01","2020-05-02","2020-05-03","2020-05-04","2020-05-05","2020-05-06","2020-05-07","2020-05-08","2020-05-09","2020-05-10","2020-05-11","2020-05-12","2020-05-13","2020-05-14","2020-05-15","2020-05-16","2020-05-17","2020-05-18","2020-05-19","2020-05-20","2020-05-21","2020-05-22","2020-05-23","2020-05-24","2020-05-25","2020-05-26","2020-05-27","2020-05-28","2020-05-29","2020-05-30","2020-05-31","2020-06-01","2020-06-02","2020-06-03","2020-06-04","2020-06-05","2020-06-06","2020-06-07","2020-06-08","2020-06-09","2020-06-10","2020-06-11","2020-06-12","2020-06-13","2020-06-14","2020-06-15","2020-06-16","2020-06-17","2020-06-18","2020-06-19","2020-06-20","2020-06-21","2020-06-22","2020-06-23","2020-06-24","2020-06-25","2020-06-26","2020-06-27","2020-06-28","2020-06-29","2020-06-30","2020-07-01","2020-07-02","2020-07-03","2020-07-04","2020-07-05","2020-07-06","2020-07-07","2020-07-08","2020-07-09","2020-07-10","2020-07-11","2020-07-12","2020-07-13","2020-07-14","2020-07-15","2020-07-16","2020-07-17","2020-07-18","2020-07-19","2020-07-20","2020-07-21","2020-07-22","2020-07-23","2020-07-24","2020-07-25","2020-07-26","2020-07-27","2020-07-28","2020-07-29","2020-07-30","2020-07-31","2020-08-01","2020-08-02","2020-08-03","2020-08-04","2020-08-05","2020-08-06"';
        $csv_confirmed_line_1 = '"SIN DATO","99","SIN DATO","99","SIN DATO",0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0';
        $csv_confirmed_line_2 = '"GUATEMALA","01","SIN DATOS","99","SIN DATO",0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,4,0,0,1,0,1,1,2,0,3,1,0,4,1,1,2,6,1,5,8,2,2,1,2,15,4,1,9,7,0,2,10,8,3,1,5,5,14,14,16,5,35,0,0,0,0,0,0,0,0,0,0,2,0,0,4,0,0,2,0,2,1,0,0,1,0,2,0,4,12,6,0,2,2,5,0,2,2,0,1,2,2,3,6,0,5,3,8,1,0,0,0,0,2,0,0,2,2,0,1,0,0,1,0,3,0,0,3,1,3,0,3,0,3,0,1,1,1,1,0,2,3,2,2,0,1,0,1,7,3,0,4,1,0,1,1,0,0,0,0,0,1,0,0,0';
        $csv_confirmed_line_3 = '"GUATEMALA","01","GUATEMALA","0101","1205668",0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,1,0,0,0,4,2,3,3,2,3,2,12,6,6,13,7,0,6,8,4,17,13,10,45,17,34,27,65,54,28,35,41,77,85,110,106,203,64,116,126,95,77,61,61,49,55,105,63,99,123,159,116,98,111,104,163,106,101,33,126,159,274,196,246,185,101,99,320,283,125,438,256,298,213,271,422,436,450,462,308,232,346,213,372,350,334,253,112,398,375,279,316,379,192,59,358,379,344,309,427,216,69,426,345,338,245,291,97,60,196,336,237,183';
       
        $csv_tested_line_1 = '"SIN DATO","99","SIN DATO","99","SIN DATO",0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0';
        $csv_tested_line_2 = '"GUATEMALA","01","SIN DATOS","99","SIN DATO",0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,2,0,0,0,2,0,0,1,0,2,3,0,6,9,15,3,11,9,1,2,0,4,15,25,5,40,42,3,63,46,8,22,23,25,29,45,8,25,33,23,101,25,10,106,65,52,24,113,100,36,11,60,16,66,44,152,30,175,2,7,4,1,0,0,1,2,6,2,22,2,1,6,0,0,2,1,3,3,1,0,4,1,6,7,7,17,11,10,10,3,9,0,11,2,3,4,8,3,8,7,2,6,8,18,1,1,0,1,9,2,0,1,2,6,1,4,3,0,3,6,5,0,1,4,4,5,6,8,2,10,4,7,9,10,1,2,9,15,6,8,5,8,4,17,21,7,5,11,4,0,9,2,6,6,2,3,0,4,0,0,0';
        $csv_tested_line_3 = '"QUETZALTENANGO","09","SIN DATOS","99","SIN DATO",0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,0,2,1,0,0,0,0,0,0,0,1,9,2,0,7,2,0,1,4,2,2,1,1,0,0,6,3,1,2,4,1,1,0,18,11,0,3,7,0,18,3,4,0,19,0,1,0,0,0,0,0,0,0,0,1,1,0,0,0,0,0,0,1,1,0,0,0,0,0,0,0,1,0,0,0,3,1,0,0,0,1,0,1,0,0,0,1,1,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,2,0,1,1,0,0,0,0,0,0,2,1,0,0,1,4,1,0,0,0,0,3,0,0,0,1,0,0,0,0,0,0';
        $csv_tested_line_4 = '"GUATEMALA","01","GUATEMALA","0101","1205668",0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,4,11,10,8,10,1,0,0,2,0,1,0,1,0,0,0,0,0,0,0,1,1,2,0,0,0,3,3,2,1,4,5,6,14,7,16,14,12,14,6,15,22,58,46,59,67,79,12,29,39,36,41,57,58,131,68,86,86,159,167,132,101,99,201,212,245,297,387,193,241,274,312,181,132,145,118,140,220,164,221,242,325,204,138,206,184,280,170,216,92,225,414,585,486,539,434,216,166,677,567,407,977,901,808,415,692,1231,1147,1398,1357,1199,885,1124,644,1167,1167,954,594,264,1102,1184,1177,1282,1291,764,367,1379,1528,1258,1123,1357,735,153,1572,1382,1519,1495,1328,620,142,1139,1407,1277,1126';

        $amalgamated = [];

        $labels = ['casos_confirmados', 'exp_confirmados', 'casos_tamizados', 'exp_tamizados', 'casos_tamizados', 'exp_tamizados'];

        $datesAndHeaders = str_getcsv($csv_headers);

        $data = str_getcsv($csv_confirmed_line_1);
        GuateCovidData::amalgamateFileData($data, $amalgamated, $datesAndHeaders, $labels);
        $this->assertCount(1, $amalgamated, "Expecting a new row in the array.");

        $data = str_getcsv($csv_confirmed_line_2);
        GuateCovidData::amalgamateFileData($data, $amalgamated, $datesAndHeaders, $labels);
        $this->assertCount(2, $amalgamated, "Expecting a new row in the array.");

        $data = str_getcsv($csv_confirmed_line_3);
        GuateCovidData::amalgamateFileData($data, $amalgamated, $datesAndHeaders, $labels);
        $this->assertCount(2, $amalgamated, "Expecting no new rows in the top-level array.");
        $this->assertCount(3, $amalgamated["01"], "Expecting a new row in the muni-level array.");
        $this->assertCount(5, $amalgamated["01"]["0101"]["datos"]["2020-03-16"], "Expecting new values in the date-level array.");

        $labels = ['casos_tamizados', 'exp_tamizados'];
        $data = str_getcsv($csv_tested_line_1);
        GuateCovidData::amalgamateFileData($data, $amalgamated, $datesAndHeaders, $labels);
        
        
        $data = str_getcsv($csv_tested_line_2);
        GuateCovidData::amalgamateFileData($data, $amalgamated, $datesAndHeaders, $labels);
        $data = str_getcsv($csv_tested_line_3);
        GuateCovidData::amalgamateFileData($data, $amalgamated, $datesAndHeaders, $labels);
        $data = str_getcsv($csv_tested_line_4);
        GuateCovidData::amalgamateFileData($data, $amalgamated, $datesAndHeaders, $labels);
        $this->assertCount(2, $amalgamated["99"], "Expecting no new rows in the muni-level array.");
        $this->assertCount(3, $amalgamated, "Expecting one new rows in the array. Xela was not in the first loads.");
        $this->assertCount(3, $amalgamated["01"], "Expecting no new rows in the muni-level array.");
        $this->assertCount(5, $amalgamated["01"]["0101"]["datos"]["2020-03-16"], "Expecting new values in the date-level array.");
       
    }
    public function testFileDataToArray():void
    {
        $dir = getcwd();
        $amalgamated = [];
        $nHandle = fopen("tests/csv_output/Tamizados.csv", "r");
        if ($nHandle !== false)
        {
            $labels = ['casos_tamizados', 'exp_tamizados', 'casos_confirmados', 'exp_confirmados', 'casos_fallecidos', 'exp_fallecidos'];
            GuateCovidData::fileDataToArray($nHandle, $labels, $amalgamated);
            fclose($nHandle);    
        }
        $nHandle = fopen("tests/csv_output/Confirmados.csv", "r");
        if ($nHandle !== false)
        {
            $labels = ['casos_confirmados', 'exp_confirmados'];
            GuateCovidData::fileDataToArray($nHandle, $labels, $amalgamated);
            fclose($nHandle);    
        }
        $nHandle = fopen("tests/csv_output/Fallecidos.csv", "r");
        if ($nHandle !== false)
        {
            $labels = ['casos_fallecidos', 'exp_fallecidos'];
            GuateCovidData::fileDataToArray($nHandle, $labels, $amalgamated);
            fclose($nHandle);    
        }
        $this->assertCount(7, $amalgamated["01"]["0101"]["datos"]["2020-03-16"], "Expecting 7 values in the date-level array.");
        $this->assertCount(7, $amalgamated["03"]["0301"]["datos"]["2020-06-10"], "Expecting 7 values in the date-level array.");
        $this->assertEquals(5, $amalgamated["03"]["0301"]["datos"]["2020-06-10"]["casos_confirmados"], "Expecting 5 confirmed cases.");
        $this->assertEquals(15, $amalgamated["03"]["0301"]["datos"]["2020-06-10"]["casos_tamizados"], "Expecting 15 tested.");
        $this->assertEquals(5, $amalgamated["03"]["0301"]["datos"]["2020-06-10"]["casos_confirmados"], "Expecting 5 confirmed cases.");
        $this->assertEquals(0, $amalgamated["03"]["0301"]["datos"]["2020-06-10"]["casos_fallecidos"], "Expecting 0 deaths.");
        $this->assertEquals(2, $amalgamated["08"]["0801"]["datos"]["2020-04-29"]["casos_confirmados"], "Expecting 2 confirmed cases.");
        $this->assertEquals(13, $amalgamated["08"]["0801"]["datos"]["2020-04-29"]["casos_tamizados"], "Expecting 13 tested.");
        $this->assertEquals(0, $amalgamated["08"]["0801"]["datos"]["2020-04-29"]["casos_fallecidos"], "Expecting 0 deaths.");
    }
}
