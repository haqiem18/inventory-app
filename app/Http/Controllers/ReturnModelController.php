<?php

namespace App\Http\Controllers;

use App\Models\ReturnModel;
use Illuminate\Http\Request;

class ReturnModelController extends Controller
{
    public function print(ReturnModel $record)
    {
        $record->load('items.product', 'branch');

        return view('return-models.print-surat-jalan', [
            'record' => $record
        ]);
    }
}