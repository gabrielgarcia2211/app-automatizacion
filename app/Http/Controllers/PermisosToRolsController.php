<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermisosToRolsController extends Controller
{

    public function extract_permissions()
    {
        
        $business_id = env('DB_ID_BUSSINESS');
        $script = "SELECT u.id, u.permisos, u.usuario, u.email FROM usuario u WHERE estado = 2 AND empresa_id = $business_id";
        $data = DB::connection("mysql_rds_search")->select("$script");

        dd(count($data));

        # nuevos individuales y masivos
        $list_indv = [];
        $list_mass = [];

        $permission_mass = self::get_permissions_serach_massive();

        # recorremos los permisos para divirlos en individuales y masivos
        for ($i = 0; $i < count($data); $i++) {
            # filtramos la info eliminando la ultima posicion vacia
            $info = substr($data[$i]->permisos, 0, -1);
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
        }

        # obtenemos los valores unicos
        $list_indv = array_values(array_unique($list_indv));
        $list_mass = array_values(array_unique($list_mass));


        #dd($list_indv, $list_mass);

        # filtrar permisos SEARCH 
        $result = self::filter_search($list_indv, $list_mass);

        # SI DESEA CREAR MAS PUEDE SER HACIA ABAJO CREANDO FILTROS POR APLICACIONES

        return self::create_script($result[0], $result[1]);
    }

    private function create_script($indv, $mass)
    {
        $script_indv = "";
        $script_mass = "";

        try {

            # creamos script rol individual
            if (!empty($indv)) {
                $permission_indv = implode(',', $indv) . ",";
                $script_indv = "INSERT INTO `roles` (`id`, `role`, `nombre`, `descripcion`, `permisos`, `tipo`) 
                                VALUES (0, 'rol individual', 'Rol Individual', 'Perfil con permisos mas comunes en una cuenta individual', '$permission_indv', 'i');";

                # insertamos el script en la bd
                DB::connection("mysql_rds_search")->select("$script_indv");

                # obtenemos el ultimo id insertado
                $script = "SELECT r.id FROM roles r WHERE r.role = 'rol individual' and r.nombre = 'Rol Individual'";
                $id_indv  = DB::connection("mysql_rds_search")->select("$script");
            }

            # creamos script rol masivo
            if (!empty($mass)) {

                # unimos los permisos

                $new_array = array_merge($indv, $mass);
                sort($new_array);

                $permission_mass = implode(',', $new_array) . ",";

                $script_mass = "INSERT INTO `roles` (`id`, `role`, `nombre`, `descripcion`, `permisos`, `tipo`) 
                                VALUES (0, 'rol masivo', 'Rol Masivo', 'Perfil con permisos mas comunes en una cuenta masiva', '$permission_mass', 'm');";

                # insertamos el script en la bd
                DB::connection("mysql_rds_search")->select("$script_mass");

                # obtenemos el ultimo id insertado
                $script = "SELECT r.id FROM roles r WHERE r.role = 'rol masivo' and r.nombre = 'Rol Masivo'";
                $id_mass  = DB::connection("mysql_rds_search")->select("$script");
            }

            # vinculamos roles con la empresa
            $rols = self::link_company($id_indv, $id_mass);
            # vincular planes ----- PENDIENTE
            if ($rols) {
                return self::link_bussines_plans();
            }
        } catch (\Exception $e) {
            Log::debug("Ocurrio un error en la insercion de los roles" . $e->getMessage());
            return response()->json($e->getMessage());
        }

        return response()->json("Error en la vinculacion de roles con empresa");
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

    private function link_bussines_plans()
    {
        $business_id = env('DB_ID_BUSSINESS');
        $limit_indv = env('DB_LIMIT_INDV');
        $limit_mass = env('DB_LIMIT_MASS');

        # capturamos el plan actual de la empresa
        $script = "SELECT * FROM empresa_admin WHERE id_empresa = $business_id";
        $data = DB::connection("mysql_rds_search")->select("$script")[0];

        Log::info("Informacion de la empresa antes " . json_encode($data));

        # actualizamos el campo con el nuevo rol
        $script_indv = "UPDATE empresa_admin ep 
            SET limite_cuentas_individuales = '$limit_indv' , limite_cuentas_masivas = '$limit_mass'  
                WHERE ep.id_empresa = $business_id";

        DB::connection("mysql_rds_search")->select("$script_indv");

        return true;
    }

    private function link_company($id_indv, $id_mass)
    {

        $business_id = env('DB_ID_BUSSINESS');

        try {

            # vinculamos el script rol individual
            if (!empty($id_indv)) {

                $script = "SELECT * FROM empresa_admin ep WHERE ep.id_empresa = $business_id";
                # insertamos el script en la bd
                $data = DB::connection("mysql_rds_search")->select("$script")[0];

                $rols = json_decode($data->roles);
                if (!in_array($id_indv[0]->id, $rols)) {
                    array_push($rols, $id_indv[0]->id);
                    $rols = json_encode($rols);

                    # actualizamos el campo con el nuevo rol
                    $script_indv = "UPDATE empresa_admin ep SET roles = '$rols' WHERE ep.id_empresa = $business_id";
                    DB::connection("mysql_rds_search")->select("$script_indv");
                }
            }

            # vinculamos el script rol masivo
            if (!empty($id_mass)) {

                $script = "SELECT * FROM empresa_admin ep WHERE ep.id_empresa = $business_id";
                # insertamos el script en la bd
                $data = DB::connection("mysql_rds_search")->select("$script")[0];

                $rols = json_decode($data->roles);
                if (!in_array($id_mass[0]->id, $rols)) {
                    array_push($rols, $id_mass[0]->id);
                    $rols = json_encode($rols);

                    # actualizamos el campo con el nuevo rol
                    $script_mass = "UPDATE empresa_admin ep SET roles = '$rols' WHERE ep.id_empresa = $business_id";
                    DB::connection("mysql_rds_search")->select("$script_mass");
                }
            }


            return true;
        } catch (\Exception $e) {
            Log::debug("Ocurrio un error en la insercion de los roles" . $e->getMessage());
            return response()->json($e->getMessage());
        }
    }
}


# REALIZAR BACKUP DE TABLA empresa_admin

# ------------------------------------------------------------------ #

# EMPRESA 'SUPER POLLOS GALPON' (SOLLA)
/*
    ID - 176
    LIMITE_INDV - 120
    LIMITE_MASS - 10
    USUARIOS - 12

*/