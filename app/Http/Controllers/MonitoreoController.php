<?php

namespace App\Http\Controllers;

use ZipArchive;
use Illuminate\Http\Request;
use App\Imports\MonitoreoImport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MonitoreoController extends Controller
{
    private $list_file = array();
    private $rutaInit = "D:\Monitoreos2";

    public function extract_monitoreo()
    {

        $rutaMonits = [
            "/aaa/",
            "/aml/",
            // "/auteco/",
        ];

        $get_list = self::get_unzip_monitoreo($rutaMonits);
        self::create_notify_people($get_list);
    }

    private function get_unzip_monitoreo($monits)
    {

        for ($i = 0; $i < count($monits); $i++) {

            $arrFiles = scandir($this->rutaInit . $monits[$i]);
            $arrFiles = self::filter_zip($arrFiles);

            for ($j = 0; $j < count($arrFiles); $j++) {

                $informacionDelArchivo = $this->rutaInit . $monits[$i] . $arrFiles[$j];

                $zip = new ZipArchive;
                $directorioSalida = $this->rutaInit . $monits[$i];

                if (!file_exists($informacionDelArchivo)) {
                    die("No se puede abrir el archivo" . $monits[$i] . $arrFiles[$j]);
                    continue;
                }

                $zip->open($informacionDelArchivo);
                $zip->extractTo($directorioSalida);
                $zip->close();

                $tempKey = trim($monits[$i], "/");
                $tempName = trim($arrFiles[$j], ".zip");
                $this->list_file[$tempKey][] = $tempName;
            }
        }

        return (object)$this->list_file;
    }


    private function filter_zip($list): array
    {
        $result = array();
        for ($i = 0; $i < count($list); $i++) {
            $pos = strrpos($list[$i], '.zip');
            if (!empty($pos)) {
                array_push($result, $list[$i]);
            }
        }

        return $result;
    }

    public function create_notify_people($monitoreos)
    {
        $position = 3; # posicion del nombre del archivo xlsx que vamos a leer

        foreach ($monitoreos as $key => $value) {
            for ($i = 0; $i < count($value); $i++) {
                $rutaRaiz = $this->rutaInit . '/' . $key . '/' . $value[$i];
                $arrFiles = scandir($rutaRaiz);
                if (!empty($arrFiles[$position])) {
                    $path = $this->rutaInit . '/' . $key . '/' . $value[$i] . '/' . $arrFiles[$position];
                    $resultEmails = Excel::toCollection(new MonitoreoImport, $path);
                    $emails = $resultEmails->first()[0][1];
                    self::create_folder($rutaRaiz, $emails);
                }
            }
        }
    }

    public function create_folder($path, $emails)
    {
        $fh = fopen($path . "/" . "notifyPeople.JSON", 'w') or die("Se produjo un error al crear el archivo");
        $texto = $emails;
        fwrite($fh, $texto) or die("No se pudo escribir en el archivo");
        fclose($fh);
        echo "Se ha escrito sin problemas" . "\n";
    }
}
