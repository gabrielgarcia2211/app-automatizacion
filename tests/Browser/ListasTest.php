<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ListasTest extends DuskTestCase
{

    private $user = "procesos.sds";
    private $password = "isqRDw9w5GDA7";
    private $rutaInit = "D:\listas";

    /**
     * A Dusk test example.
     * @group listas 
     * @return void
     */
    public function testExample()
    {
        # extraemos los sitios para la carga de archivos
        $sites = self::map_sites();
        $arrFiles = scandir($this->rutaInit);
        $arrFiles = self::filter_file($arrFiles);

        try {
            foreach ($sites as $key => $value) {
                $this->browse(function (Browser $browser) use ($value, $key, $arrFiles) {

                    $browser->visit($key)
                        ->waitForText('Stradata')
                        ->type('usuario', $this->user)
                        ->type('clave', $this->password)
                        ->press('Ingresar');

                    for ($k = 0; $k < count($arrFiles); $k++) {

                        $browser->visit($key . 'app/lista/');

                        $file_name = $this->rutaInit . "/" . $arrFiles[$k];

                        # cargar la informacion csv
                        $browser->attach('userfile',  $file_name);

                        $browser->click('[name="cruce_listas_p"]')->acceptDialog();
                        $browser->pause(2000);

                        # extraemos y escribimos la informacion en el log
                        $text = null;
                        if ((self::seeExists('no corresponde', $browser) == TRUE) 
                        || (self::seeExists('No se completa sincronizacion', $browser) == TRUE)) {
                            Log::info("No pudo ser cargada: $file_name " . $key);
                        } else {
                            Log::info("Cargado Correctamente: $file_name " . $key);
                        }
                        $text = $browser->text(false);
                        Log::info($text);


                    }
                });
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function filter_file($list): array
    {
        $result = array();
        for ($i = 0; $i < count($list); $i++) {
            $pos = str_contains($list[$i], '.csv');
            if ($pos) {
                array_push($result, $list[$i]);
            }
        }
        return array_values($result);
    }

    function seeExists($element, $I)
    {
        try {
            $I->assertSee($element);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    private function map_sites()
    {
        return array(
            'https://sdscode.local/' =>     'local',
            /* 'https://bpo.stradata.com.co/' =>     'bpo',
            'https://btg.stradata.com.co/' =>     'btg',
            'https://sds.stradata.com.co/' =>     'sds',// NO PEPS-EXTRANJEROS - RELACIONADOS-PEPS-EXTRANJEROS
            'https://aaa.stradata.com.co/' =>     'aaa',
            'https://accivalores.stradata.com.co/' =>     'accivalores',
            'https://alianza.stradata.com.co/' =>     'alianza',// NO PEPS-EXTRANJEROS - RELACIONADOS-PEPS-EXTRANJEROS
            'https://amlsds.stradata.com.co/' =>     'aml',// NO PEPS-EXTRANJEROS - RELACIONADOS-PEPS-EXTRANJEROS
            'https://monitoreo.stradata.com.co/'  => 'monitoreo',// NO PEPS-EXTRANJEROS - RELACIONADOS-PEPS-EXTRANJEROS
            'https://aress.stradata.com.co/' =>     'aress',
            'https://atleticonacional.stradata.com.co/' =>     'nacional',
            'https://auteco.stradata.com.co/' =>     'auteco',
            'https://bancamiasds.stradata.com.co/' =>     'bancamia',
            'https://bancolombiasds.stradata.com.co/' =>     'bancolombia',
            'https://banrepsds.stradata.com.co/' =>     'banrep',
            'https://cala.stradata.com.co/' =>     'cala_new',
            'https://canapro.stradata.com.co/' =>     'canapro',
            'https://cementosargos.stradata.com.co/' =>     'cementosargos',
            'https://centralcervecera.stradata.com.co/' =>     'centralcervecera',
            'https://comfama.stradata.com.co/' =>     'comfama',
            'https://corbeta.stradata.com.co/' =>     'corbeta',
            'https://credicorp.stradata.com.co/' =>     'credicorp',
            'https://crediservir.stradata.com.co/' =>     'crediservir',
            'https://crystal.stradata.com.co/' =>     'crystal',
            'https://cuerosvelez.stradata.com.co/' =>     'cuerosvelez',
            'https://demos.stradata.com.co/' =>     'demos',
            'https://edemsa-ethuss.stradata.com.co/' =>     'edemsa',
            'https://edinsa.stradata.com.co/' =>     'edinsa',
            'https://epm.stradata.com.co/' =>     'epm',
            'https://fiduagrariasds.stradata.com.co/' =>     'fiduagraria',
            'https://flamingo.stradata.com.co/' =>     'flamingo',
            'https://gana.stradata.com.co/' =>     'gana',
            'https://girosyfinanzassds.stradata.com.co/' =>     'girosyfinanzas',
            'https://grupoargos.stradata.com.co/' =>     'grupoargos',
            'https://grupoexito.stradata.com.co/' =>     'grupoexito',
            'https://grupofamilia.stradata.com.co/' =>     'grupofamilia',
            'https://iberplast.stradata.com.co/' =>     'iberplast',
            'https://isasds.stradata.com.co/' =>     'isa',
            'https://levapansds.stradata.com.co/' =>     'levapan',// NO PEPS-EXTRANJEROS - RELACIONADOS-PEPS-EXTRANJEROS
            'https://notarias.stradata.com.co/' =>     'notarias',// NO PEPS-EXTRANJEROS - RELACIONADOS-PEPS-EXTRANJEROS
            'https://nubanksds.stradata.com.co/' =>     'nubank',
            'https://nutresa.stradata.com.co/' =>     'nutresa',
            'https://porvenirsds.stradata.com.co/' =>     'porvenir',
            'https://postobon.stradata.com.co/' =>     'postobon',
            'https://ilumasds.stradata.com.co/' =>     'premex',
            'https://rcisds.stradata.com.co/' =>     'rci',
            'https://sagrilaftsds.stradata.com.co/' =>     'sagrilaft',// NO PEPS-EXTRANJEROS - RELACIONADOS-PEPS-EXTRANJEROS
            'https://scotiacolpatriasds.stradata.com.co/' =>     'scotia',
            'https://scotiacolpatriacfsds.stradata.com.co/' =>     'scotiacf',
            'https://sdsexdom.stradata.com.co/' =>     'sdsexdom',
            'https://seasif-pacific.stradata.com.co/' =>     'seasif_pacific',// NO PEPS-EXTRANJEROS - RELACIONADOS-PEPS-EXTRANJEROS
            'https://serfinansa.stradata.com.co/' =>     'serfinansa',
            'https://orgsolla.stradata.com.co/' =>     'solla',
            'https://tigoune.stradata.com.co/' =>     'tigoune',
            'https://ualasds.stradata.com.co/' =>     'uala',
            'https://release.stradata.com.co/'  => 'release',
            'https://wisds.stradata.com.co/' =>     'wi', // NO PEPS-EXTRANJEROS - RELACIONADOS-PEPS-EXTRANJEROS
            'https://omnilatamsds.stradata.com.co/' => 'omnilatam',
            'https://cissds.stradata.com.co/' => 'cis', */
        );
    }
}
