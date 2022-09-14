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
        "principal" => 2,
        /* "credicorp" => 3,
          "banrep" => 4,
          "uala" => 5,
          "hostdime" => 6,
          "bancamia" => 7,
          "rci" => 8 */
    ];

    private $ruta_init = "http://localhost/phpmyadmin/index.php?route=/";
    private $link_snr = "table/export&db=stradata_sds_global&table=InfoNotaria&single_table=true&server=";


    private $user_primaria = "";
    private $password_primaria = "vi8VAeQBsASitVxDJw";
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
            self::set_rds($value, $key);
        }
    }

    private function set_rds($rds, $key)
    {

        try {
            $this->browse(function (Browser $browser) use ($rds, $key) {

                $url = $browser->driver->getCommandExecutor()->getAddressOfRemoteServer();
                $uri = '/session/' . $browser->driver->getSessionID() . '/chromium/send_command';
                $body = [
                    'cmd' => 'Page.setDownloadBehavior',
                    'params' => ['behavior' => 'allow', 'downloadPath' => 'D:\sip\hola.zip']
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


                $browser->visit($this->ruta_init . $this->link_snr . $rds)
                    ->waitForText('InfoNotaria')
                    ->press('Continuar');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
