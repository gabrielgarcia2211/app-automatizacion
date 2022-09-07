<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\LogMonitoreo;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MonitoreoTest extends DuskTestCase
{

    private $user = "procesos.sds";
    private $password = "isqRDw9w5GDA7";
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {

        # extraemos los monitoreos pendientes de ejecucion
        $logs = LogMonitoreo::where('status', 0)->get()->toArray();
        $sites = self::map_sites();

        if(empty($logs)){
            echo "No logs found.";
            return;
        }

        # busqueda de sitios
        for ($i = 0; $i < count($logs); $i++) {
            if (in_array($logs[$i]["empresa"], $sites)) {
                $position = array_search($logs[$i]["empresa"], $sites);
                if (!empty($position)) {
                    $logs[$i]['site'] = $position;
                }
            }
        }

        # agrupamos la informacion por sitios para realizar la prueba duskTest
        $logs = (object) self::group_sites('site', $logs);


        foreach ($logs as $key => $value) {
            $this->browse(function (Browser $browser) use ($value, $key) {
                $test = true;

                if ($test && $key == "https://aaa.stradata.com.co/") {
                    $browser->visit('https://sdscode.local/');
                    sleep(15);
                    $browser->waitForText('Stradata')
                        ->type('usuario', 'gabriel.garcia')
                        ->type('clave', '12345')
                        ->press('Ingresar');
                } else if (!$test) {
                    $browser->visit($key)
                        ->waitForText('Stradata')
                        ->type('usuario', $this->user)
                        ->type('clave', $this->password)
                        ->press('Ingresar');
                }

                for ($k = 0; $k < count($value); $k++) {

                    if ($test) {
                        $browser->visit('https://sdscode.local/utils/lista/Kfuqm8FrRseVfMOAcSnSxnKRwH1s3DLGOeyT');
                    } else {
                        $browser->visit($key . '/utils/lista/Kfuqm8FrRseVfMOAcSnSxnKRwH1s3DLGOeyT');
                    }

                    # cargar la informacion consultedLists de la tabla
                    $browser->attach('consultedLists',  $value[$k]["ruta_consult"])
                        ->assertSee('consultedLists');

                    # cargar la informacion listToSearch de la tabla
                    $browser->attach('listToSearch',  $value[$k]["ruta_list"])
                        ->assertSee('listToSearch');

                    # cargar la informacion notifyPeople de la tabla
                    $browser->attach('notifyPeople',  $value[$k]["ruta_notify"])
                        ->assertSee('notifyPeople');

                    # seteo de campos companiId y nowRow
                    $browser->type('companyId', $value[$k]["company_id"])
                        ->type('numRowSearched', $value[$k]["num_row_searched"]);

                    /* $browser->press('Enviar datos')
                        ->assertSee('Petici\u00f3n procesada correctamente', 15); */

                    # actualizamos el registro
                    $regist_log = LogMonitoreo::find($value[$k]["id"]);
                    $regist_log->status = 1;
                    $regist_log->save();


                    Log::debug("Monitoreo Enviado: " . " " . $key);
                }
            });
        }
    }

    private function map_sites()
    {
        return array(
            'https://sdscode.local/' =>     'local',
            'https://bpo.stradata.com.co/' =>     'bpo',
            'https://btg.stradata.com.co/' =>     'btg',
            'https://sds.stradata.com.co/' =>     'sds',
            'https://aaa.stradata.com.co/' =>     'aaa',
            'https://accivalores.stradata.com.co/' =>     'accivalores',
            'https://alianza.stradata.com.co/' =>     'alianza',
            'https://amlsds.stradata.com.co/' =>     'aml',
            'https://monitoreo.stradata.com.co/'  => 'monitoreo',
            'https://aress.stradata.com.co/' =>     'aress',
            'https://atleticonacional.stradata.com.co/' =>     'atleticonacional',
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
            'https://levapansds.stradata.com.co/' =>     'levapan',
            'https://notarias.stradata.com.co/' =>     'notarias',
            'https://nubanksds.stradata.com.co/' =>     'nubank',
            'https://nutresa.stradata.com.co/' =>     'nutresa',
            'https://porvenirsds.stradata.com.co/' =>     'porvenir',
            'https://postobon.stradata.com.co/' =>     'postobon',
            'https://ilumasds.stradata.com.co/' =>     'premex',
            'https://rcisds.stradata.com.co/' =>     'rci',
            'https://sagrilaftsds.stradata.com.co/' =>     'sagrilaft',
            'https://scotiacolpatriasds.stradata.com.co/' =>     'scotia',
            'https://scotiacolpatriacfsds.stradata.com.co/' =>     'scotiacf',
            'https://sdsexdom.stradata.com.co/' =>     'sdsexdom',
            'https://seasif-pacific.stradata.com.co/' =>     'seasif_pacific',
            'https://serfinansa.stradata.com.co/' =>     'serfinansa',
            'https://orgsolla.stradata.com.co/' =>     'solla',
            'https://tigoune.stradata.com.co/' =>     'tigoune',
            'https://ualasds.stradata.com.co/' =>     'uala',
            'https://release.stradata.com.co/'  => 'release',
            'https://wisds.stradata.com.co/' =>     'wi',
            'https://omnilatamsds.stradata.com.co/' => 'omnilatam',
        );
    }

    function group_sites($key, $data): array
    {
        $result = array();

        foreach ($data as $val) {
            if (array_key_exists($key, $val)) {
                $result[$val[$key]][] = $val;
            } else {
                $result[""][] = $val;
            }
        }

        return $result;
    }
}
