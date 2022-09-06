<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;


class MonitoreoImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        return $rows;
    }
    
}
