<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogMonitoreo extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa',
        'ruta_consult',
        'ruta_list',
        'ruta_notify',
        'company_id',
        'num_row_searched',
        'status',
    ];

    public static function create_log($data)
    {
        if (empty($data)) return false;

        # verificamos que no exista duplicidad de datos que
        $logs = LogMonitoreo::where('empresa', $data['empresa'])
        ->where('ruta_consult', $data['ruta_consult'])
        ->where('ruta_list', $data['ruta_list'])
        ->where('ruta_notify', $data['ruta_notify'])
        ->get()
        ->first();

        if (empty($logs)) {
            LogMonitoreo::create([
                'empresa' => $data['empresa'],
                'ruta_consult' => $data['ruta_consult'],
                'ruta_list' => $data['ruta_list'],
                'ruta_notify' => $data['ruta_notify'],
                'company_id' => $data['company_id'],
                'num_row_searched' => $data['num_row_searched'],
                'status' => 0,
            ]);
        }

        return true;
    }
}
