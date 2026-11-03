<?php

namespace App\Http\Controllers;

use App\Models\ReturnModel;
use Illuminate\Http\Request;

class ReturnModelController extends Controller
{
    public function print(ReturnModel $record)
    {
        // Muat relasi items, produk pada items, dan branch agar data lengkap
        $record->load('items.product', 'branch');

        return view('filament.resources.return-models.print-surat-jalan', [
            'record' => $record
        ]);
    }
}