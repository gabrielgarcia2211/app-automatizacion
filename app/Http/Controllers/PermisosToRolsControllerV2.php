<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermisosToRolsControllerV2 extends Controller
{
    public function extract_permissions()
    {

        $business_id = env('DB_ID_BUSSINESS');
        $script = "SELECT u.id, u.permisos, u.usuario, u.email FROM usuario u WHERE estado = 2 AND empresa_id = $business_id";
        $data = DB::connection("mysql_rds_search")->select("$script");

        # PARTE 1 ---
        # agrupamos los usuarios por permisos identicos 
        $data = json_decode(json_encode($data), true);
        $data = self::group_array($data, 'permisos');

        # nuevos individuales y masivos
        $list_indv = [];
        $list_mass = [];

        $permission_mass = self::get_permissions_serach_massive();

        # recorremos los permisos para divirlos en individuales y masivos
        for ($i = 0; $i < count($data); $i++) {

            # filtramos la info eliminando la ultima posicion vacia
            $info = substr($data[$i]["permisos"], 0, -1);
            $info = explode(",", $info);
            sort($info);

            # dividimos
            for ($j = 0; $j < count($info); $j++) {
                if (in_array($info[$j], $permission_mass)) {
                    array_push($list_mass, $info[$j]);
                } else {
                    array_push($list_indv, $info[$j]);
                }
            }

            # obtenemos los valores unicos
            $list_indv = array_values(array_unique($list_indv));
            $list_mass = array_values(array_unique($list_mass));

            # luego en cada iteracion separamos solo los permisos search
            $result = self::filter_search($list_indv, $list_mass);

            # seteamos los valores para iterar en el nuevo registro
            $list_indv = [];
            $list_mass = [];

            unset($data[$i]["permisos"]);

            $data[$i]["permisos"]["individuales"] = (!empty($result[0])) ? $result[0] : [];
            $data[$i]["permisos"]["masivos"] = (!empty($result[1])) ? $result[1] : [];
        }

        //dd($data);

        # PARTE 2 ---
        # verficar cuales permisos coinciden en algunas de sus divisiones(indv/mass) y reagrupar

        $agroup_array_indv = [];
        $agroup_array_mass = [];

        for ($m = 0; $m < count($data); $m++) {

            for ($p = 0; $p < count($data[$m]["groupeddata"]); $p++) {

                if (!empty($data[$m]["permisos"]["individuales"])) {
                    array_push(
                        $agroup_array_indv,
                        [
                            "id" => $data[$m]["groupeddata"][$p]["id"],
                            "usuario" => $data[$m]["groupeddata"][$p]["usuario"],
                            "email" => $data[$m]["groupeddata"][$p]["email"],
                            "permisos" => (!empty($data[$m]["permisos"]["individuales"])) ? implode(",", $data[$m]["permisos"]["individuales"]) : [],
                        ]
                    );
                }
            }

            for ($r = 0; $r < count($data[$m]["groupeddata"]); $r++) {

                if (!empty($data[$m]["permisos"]["masivos"])) {
                    array_push(
                        $agroup_array_mass,
                        [
                            "id" => $data[$m]["groupeddata"][$r]["id"],
                            "usuario" => $data[$m]["groupeddata"][$r]["usuario"],
                            "email" => $data[$m]["groupeddata"][$r]["email"],
                            "permisos" => (!empty($data[$m]["permisos"]["masivos"])) ? implode(",", $data[$m]["permisos"]["masivos"]) : [],
                        ]
                    );
                }
            }
        }

        $agroup_array_indv = self::group_array($agroup_array_indv, 'permisos');
        $agroup_array_mass = self::group_array($agroup_array_mass, 'permisos');

        dd($agroup_array_indv, $agroup_array_mass);
    }

    private function filter_search($indv, $mass)
    {

        $filter = [];
        $filter_indv = [];
        $filter_mass = [];
        $script = "SELECT * FROM permisos p WHERE label  NOT like '%EXDOM%'  
            AND label  NOT like '%ANALYTICS%'  
            AND label  NOT like '%Risk Scoring%'  
            AND label  NOT like '%Case Manager%' 
            AND label  NOT like '%NEWS%'
            AND label  NOT like '%Name Finder%'
            AND label  NOT like '%Segmenter%';";

        $permission = DB::connection("mysql_rds_search")->select("$script");

        for ($i = 0; $i < count($permission); $i++) {
            array_push($filter, $permission[$i]->permiso);
        }

        # permisos de search para individuales
        for ($j = 0; $j < count($indv); $j++) {
            if (in_array($indv[$j], $filter)) {
                array_push($filter_indv, $indv[$j]);
            }
        }
        # permisos de search para masivos
        for ($k = 0; $k < count($mass); $k++) {
            if (in_array($mass[$k], $filter)) {
                array_push($filter_mass, $mass[$k]);
            }
        }

        return [$filter_indv, $filter_mass];
    }

    private function get_permissions_serach_massive()
    {

        $new_array = [];
        $script = "SELECT p.permiso FROM permisos p WHERE permiso LIKE '%masivo%' or label LIKE '%masivo%' ";
        $permission = DB::connection("mysql_rds_search")->select("$script");

        for ($i = 0; $i < count($permission); $i++) {
            array_push($new_array, $permission[$i]->permiso);
        }

        return $new_array;
    }

    private function group_array($array, $groupkey)
    {
        if (count($array) > 0) {
            $keys = array_keys($array[0]);
            $removekey = array_search($groupkey, $keys);
            if ($removekey === false)
                return array("Clave \"$groupkey\" no existe");
            else
                unset($keys[$removekey]);
            $groupcriteria = array();
            $return = array();
            foreach ($array as $value) {
                $item = null;
                foreach ($keys as $key) {
                    $item[$key] = $value[$key];
                }
                $busca = array_search($value[$groupkey], $groupcriteria);
                if ($busca === false) {
                    $groupcriteria[] = $value[$groupkey];
                    $return[] = array($groupkey => $value[$groupkey], 'groupeddata' => array());
                    $busca = count($return) - 1;
                }
                $return[$busca]['groupeddata'][] = $item;
            }
            return $return;
        } else
            return array();
    }
}
