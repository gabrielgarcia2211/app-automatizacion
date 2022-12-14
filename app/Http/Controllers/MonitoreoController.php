<?php

namespace App\Http\Controllers;

use ZipArchive;
use App\Models\LogMonitoreo;
use Illuminate\Http\Request;
use App\Imports\MonitoreoImport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MonitoreoController extends Controller
{
    private $list_file = array();
    private $rutaInit = "D:\Monitoreos";

    public function extract_monitoreo()
    {

        $rutaMonits = [
            "/crystal/",
            "/sds/",
            "/grupoexito/",
            "/postobon/",
            "/aaa/",
        ];

        $get_list = self::get_unzip_monitoreo($rutaMonits);
        self::create_notify_people($get_list);
    }

    private function get_unzip_monitoreo($monits)
    {

        try {
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
                }

                $arrFiles = scandir($this->rutaInit . $monits[$i]);
                $arrFiles = $this->filter_file($arrFiles);

                $tempKey = trim($monits[$i], "/");

                for ($k = 0; $k < count($arrFiles); $k++) {
                    $this->list_file[$tempKey][] = $arrFiles[$k];
                }
            }

            return (object)$this->list_file;
        } catch (\Exception $th) {
            Log::error($th->getMessage());
        }
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

    private function filter_file($list): array
    {
        $result = array();
        for ($i = 0; $i < count($list); $i++) {
            $pos = str_contains($list[$i], '.');
            if (!$pos) {
                array_push($result, $list[$i]);
            }
        }
        unset($result[0]);
        return array_values($result);
    }

    public function create_notify_people($monitoreos)
    {
        $position = 3; # posicion del nombre del archivo xlsx que vamos a leer

        foreach ($monitoreos as $key => $value) {
            for ($i = 0; $i < count($value); $i++) {
                $ruta_raiz = $this->rutaInit . '/' . $key . '/' . $value[$i];
                $arrFiles = scandir($ruta_raiz);
                if (!empty($arrFiles[$position])) {
                    $path = $this->rutaInit . '/' . $key . '/' . $value[$i] . '/' . $arrFiles[$position];
                    $resultEmails = Excel::toCollection(new MonitoreoImport, $path);
                    $data = $resultEmails->first();
                    $resp_file = self::create_file($ruta_raiz, $data[0][1]);
                    if ($resp_file) {
                        self::create_log($ruta_raiz, $key, $data);
                    }
                }
            }
        }
    }

    public function create_file($path, $emails)
    {
        $fh = fopen($path . "/" . "notifyPeople.JSON", 'w') or die("Se produjo un error al crear el archivo");
        $texto = $emails;
        fwrite($fh, $texto) or die("No se pudo escribir en el archivo");
        fclose($fh);
        Log::debug("Se ha escrito sin problemas");
        return true;
    }

    public function create_log($ruta_raiz, $key, $data)
    {


        $map_log = [
            'empresa' => $key,
            'ruta_consult' => $ruta_raiz . "/" . "consultedLists.JSON",
            'ruta_list' => $ruta_raiz . "/" . "listToSearch.JSON",
            'ruta_notify' => $ruta_raiz . "/" . "notifyPeople.JSON",
            'company_id' => $data[1][1],
            'num_row_searched' => $data[2][1],
        ];

        LogMonitoreo::create_log($map_log);
    }
}
