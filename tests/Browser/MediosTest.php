<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MediosTest extends DuskTestCase
{

    private $sites = [
      /*   "principal" => 2,
        "credicorp" => 3,
        "banrep" => 4,
        "uala" => 5,
        "hostdime" => 6,
        "bancamia" => 7,
        "rci" => 8 */

    ];

    private $sql_global = "SELECT FORMAT(COUNT(DISTINCT n.id),0,'de_DE') AS noticias,
        FORMAT(MAX(n.id),0,'de_DE') AS 'id_noticias',
        FORMAT(COUNT(i.id),0,'de_DE') AS implicados,
        FORMAT(MAX(i.id_noticia),0,'de_DE') AS 'id_implicados',
            (SELECT COUNT(*) FROM stradata_sds_global.sds_implicados WHERE delitos_implicado LIKE '%|%') AS barras
                FROM stradata_sds_global.sds_noticias n
                LEFT JOIN stradata_sds_global.sds_implicados i ON n.id = i.id_noticia
            WHERE DATE(n.created_at) = DATE(NOW())";


    
    private $ruta_init = "http://localhost/phpmyadmin/index.php?route=/";
    private $link_noticias = "table/import&db=stradata_sds_global&table=sds_noticias&server=";
    private $link_implicados = "table/import&db=stradata_sds_global&table=sds_implicados&server=";

    private $user_primaria = "";
    private $password_primaria = "vi8VAeQBsASitVxDJw";


    # ruta de las carpetas
    private $ruta_noticias = [
        "D:\mediospublicos" . "/" . "noticias_20220909.csv.gz",
        "D:\mediospublicos" . "/" . "noticias_fiscalia_20220909.csv.gz",
    ];

    private $ruta_implicados = [
        "D:\mediospublicos" . "/" . "implicados_20220909_sinAkasCortos.csv.gz",
        "D:\mediospublicos" . "/" . "implicados20220909_fiscalia_sinAkasCortos.csv.gz",
    ];

    private $fields = [
        "noticias" => 'id,medio,id_medio,fuente,fecha_noticia,seccion,lugar_noticia,otros_datos,url,estado_noticia,fecha_modificacion,usuario_modificacion,noticia_con_problemas,noticia_confusa,calidad_extraccion,fecha_cargue,titulo_noticia,cuerpo_noticia,cuerpo_noticia_tags,fecha_cierre_noticia,usuario_asignacion',
        "implicados" => 'id_noticia,id_medio,url_noticia,fecha_creacion,fecha_modificacion,usuario_modificacion,tipo_implicado,nombre_implicado,nombre_implicado_final,id_estado_procesal,estado_nombre,nacionalidad,genero,tipo_persona,aka,delitos_implicado,observacion,usuario_asignacion,tag_automatico,show_person',
    ];


    /**
     * A Dusk test example.
     * @group medios 
     * @return void
     */
    public function testExample()
    {
        $sites = (object) $this->sites;
        foreach ($sites as $key => $value) {
            $this->user_primaria = ($value == 6) ? "stradata_proceso" : "procesos";
            self::set_rds($value, $key);
        }
    }

    private function set_rds($rds, $key)
    {

        try {
            $this->browse(function (Browser $browser) use ($rds, $key) {

                # apuntador de rds
                $browser->visit($this->ruta_init)
                    ->select('server', $rds)
                    ->waitForText('Bienvenido a phpMyAdmin', 15);

                # inicio de sesion
                $browser->type('pma_username', $this->user_primaria)
                    ->type('pma_password', $this->password_primaria)
                    ->press('Continuar');


                # --noticias --
                for ($i = 0; $i < count($this->ruta_noticias); $i++) {
                    # import de noticias
                    $browser->visit($this->ruta_init . $this->link_noticias . $rds)
                        ->waitForText('sds_noticias');

                    #carga de datos
                    $browser->attach('import_file',  $this->ruta_noticias[$i])
                        ->type('csv_terminated', ";")
                        ->type('csv_columns', $this->fields["noticias"])
                        ->press('Continuar');

                    Log::info("Archivo subido noticias - , " . $this->ruta_noticias[$i]);
                    //->waitForText('Importación ejecutada exitosamente');
                }

                # --implicados --
                for ($i = 0; $i < count($this->ruta_implicados); $i++) {
                    # import de noticias
                    $browser->visit($this->ruta_init . $this->link_implicados . $rds)
                        ->waitForText('sds_implicados');

                    #carga de datos
                    $browser->attach('import_file',  $this->ruta_implicados[$i])
                        ->type('csv_terminated', ";")
                        ->type('csv_enclosed', "|")
                        ->type('csv_escaped', "|")
                        ->type('csv_columns', $this->fields["implicados"])
                        ->press('Continuar');
                    Log::info("Archivo subido implicados - , " . $this->ruta_implicados[$i]);
                    //->waitForText('Importación ejecutada exitosamente');
                }

                # Consulta RDS
                $data = DB::connection("mysql_rds_" . $key)->select("$this->sql_global");
                Log::info(
                    "\n" .
                        "noticias" . ": "  . $data[0]->noticias . "\n" .
                        "id noticias" . ": "  . ((empty($data[0]->id_noticias)) ? "null" : $data[0]->id_noticias) . "\n" .
                        "implicados" . ": " . $data[0]->implicados . "\n" .
                        "id implicados" . ": " . ((empty($data[0]->id_implicados)) ? "null" : $data[0]->id_implicados) . "\n" .
                        "barras" . ": " . $data[0]->barras
                );
            });
            Log::info("Archivo subido correctamente, " . "RDS" . array_search($rds, $this->sites));
            Log::info("-----------------------------");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
