<?php

namespace App\Http\Controllers;

use App\Models\Sites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SitesController extends Controller
{
    public function get_sites()
    {
        # Obtenemos todos los casos con el status especificado
        $result_sites = Sites::all();

        Log::debug('App\Controllers\SitesController::get_sites {' . Auth::user()->username . '} - Proccess Success: ' . json_encode($result_sites));
        return response()->json($result_sites);
    }
}
