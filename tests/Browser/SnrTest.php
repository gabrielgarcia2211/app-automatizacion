<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SnrTest extends DuskTestCase
{
  
    private $sites = [
        /* "principal" => 2,
        "credicorp" => 3,
        "banrep" => 4,
        "uala" => 5,
        "hostdime" => 6,
        "bancamia" => 7, 
        "rci" => 8,
        "alianza" => 9,  */
        "crezcamos" => 10,
    ];

    /**1. HOSTDIME
2. PRINCIPAL
3. CREDICORP
4. BANREP
5. UALA
6. BANCAMIA */


    private $ruta_init = "http://localhost/phpmyadmin/index.php?route=/";

    private $link_snr_create = "table/operations&db=stradata_sds_global&table=InfoNotaria&server=";
    private $link_snr_import = "table/import&db=stradata_sds_global&table=InfoNotaria_new&server=";
    private $link_snr_export = "table/export&db=stradata_sds_global&table=InfoNotaria_new&single_table=true&server=";
    private $link_snr_import_general = "database/import&db=stradata_sds_global&server=";


    private $user_primaria = "";
    private $password_primaria = "vi8VAeQBsASitVxDJw";

    # ruta de las carpetas
    private $ruta_archivo = "D:\snr" . "/" . "SNR_20221122.csv";
    private $ruta_archivo_import = "D:\snr" . "/" . "InfoNotaria_new.sql";

    private $fields = [
        "infoNotaria" => 'TIPO_DE_DOCUMENTO,RADICADO_DEL_PROCESO,NO_DE_JUZGADO,JUZGADO_DE_ORIGEN,FECHA_DE_OFICIO,FOLIO_MATRICULA_INMOBILIARIA,FECHA_DE_REPORTE,ORIGEN,FOLIO_NUMBER,MAIL_DESPACHO,INFORMACION_PREDIO'
    ];

    /**
     * A Dusk test example.
     * @group snr 
     * @return void
     */
    public function testExample()
    {
        $sites = (object) $this->sites;
        foreach ($sites as $key => $value) {
            $this->user_primaria = ($value == 6) ? "stradata_proceso" : "procesos";
            if ($value === 2) {
                # PARTE 1
                self::get_rds($value, $key);
            } else {
                # PARTE 2
                self::set_rds_snr($value, $key);
            }
        }
    }

    private function get_rds($rds, $key)
    {

        try {
            $this->browse(function (Browser $browser) use ($rds, $key) {

                $new_table = "InfoNotaria_new";

                # Configuracion para la descarga de archivos
                $url = $browser->driver->getCommandExecutor()->getAddressOfRemoteServer();
                $uri = '/session/' . $browser->driver->getSessionID() . '/chromium/send_command';
                $body = [
                    'cmd' => 'Page.setDownloadBehavior',
                    'params' => ['behavior' => 'allow', 'downloadPath' => 'D:\snr']
                ];
                (new \GuzzleHttp\Client())->post($url . $uri, ['body' => json_encode($body)]);

                # apuntador de rds
                $browser->visit($this->ruta_init)
                    ->select('server', $rds)
                    ->waitForText('Bienvenido a phpMyAdmin', 15);

                # inicio de sesion
                $browser->type('pma_username', $this->user_primaria)
                    ->type('pma_password', $this->password_primaria)
                    ->press('Continuar');

                # PARTE 1 ----

                # creacion de tabla 
                /* $browser->visit($this->ruta_init . $this->link_snr_create . $rds);

                $inputs = $browser->elements('input[name^=new_name]');

                foreach ($inputs as $key => $input) {
                    if ($key == 2) {
                        $input->clear();
                        $input->sendKeys($new_table);
                        break;
                    }
                }
                $browser->radio('what', 'structure')
                    ->click('input[name="submit_copy"]');

                $browser->pause(7000);

                # importacion del archvio csv
                $browser->visit($this->ruta_init . $this->link_snr_import . $rds)
                    ->waitForText($new_table);

                #carga de datos
                $browser->attach('import_file',  $this->ruta_archivo)
                    ->type('csv_terminated', ";")
                    ->type('csv_columns', $this->fields["infoNotaria"])
                    ->press('Continuar');  */


                # PARTE 2 ----

                # exportacion de datos
                $browser->visit($this->ruta_init . $this->link_snr_export . $rds)
                    ->waitForText($new_table)
                    ->press('Continuar'); 


                self::get_rename_table($key);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function set_rds_snr($rds, $key)
    {
        try {
            $this->browse(function (Browser $browser) use ($rds, $key) {

                $stash = "stradata_sds_global";

                # apuntador de rds
                $browser->visit($this->ruta_init)
                    ->select('server', $rds)
                    ->waitForText('Bienvenido a phpMyAdmin', 15);

                # inicio de sesion
                $browser->type('pma_username', $this->user_primaria)
                    ->type('pma_password', $this->password_primaria)
                    ->press('Continuar');


                # importacion del archvio csv
                $browser->visit($this->ruta_init . $this->link_snr_import_general . $rds)
                    ->waitForText($stash);

                #carga de datos
                $browser->attach('import_file',  $this->ruta_archivo_import)
                    ->press('Continuar');

                Log::info("Archivo subido - " . $this->ruta_archivo_import);

                self::get_rename_table($key);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function get_rename_table($key)
    {
        $sql_global = "RENAME TABLE InfoNotaria TO InfoNotaria_" . date("Ymd") . ", InfoNotaria_new TO InfoNotaria";
        $data = DB::connection("mysql_rds_" . $key)->select("$sql_global");
        Log::info('Proceso realizado: ' . $key);
    }
}
