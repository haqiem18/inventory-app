<?php

namespace App\Filament\Widgets;

use App\Models\StockMutation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StockOutBarChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected function getHeading(): ?string
    {
        return 'Barang Keluar (7 Hari Terakhir)';
    }

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));

        $data = $days->map(function ($date) {
            $query = StockMutation::where('type', 'OUT')->whereDate('mutation_date', $date);
            
            if (Auth::check() && Auth::user()->role === 'admin_cabang') {
                $query->where('branch_id', Auth::user()->branch_id);
            }

            return $query->sum('quantity');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total Qty Keluar',
                    'data' => $data->toArray(),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $days->map(fn ($date) => Carbon::parse($date)->translatedFormat('D, d M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}