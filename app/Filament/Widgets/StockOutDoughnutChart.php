<?php

namespace App\Filament\Widgets;

use App\Models\StockMutation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;

class StockOutDoughnutChart extends ChartWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): ?string
    {
        return 'Persentase Keperluan Barang Keluar';
    }

    protected function getData(): array
    {
        $query = function ($subType) {
            $q = StockMutation::where('type', 'OUT')->where('sub_type', $subType);
            if (Auth::check() && Auth::user()->role === 'admin_cabang') {
                $q->where('branch_id', Auth::user()->branch_id);
            }
            return $q->sum('quantity');
        };

        return [
            'datasets' => [
                [
                    'label' => 'Total Qty',
                    'data' => [$query('produksi'), $query('penjualan'), $query('retur'), $query('mutasi')],
                    'backgroundColor' => ['#ef4444', '#10b981', '#f59e0b', '#8b5cf6'],
                ],
            ],
            'labels' => ['Produksi', 'Penjualan', 'Retur', 'Mutasi Cabang'],
        ];
    }

    protected function getOptions(): ?array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => Js::from("function(context) {
                            let label = context.label || '';
                            let value = context.parsed || 0;
                            let total = context.chart._metasets[context.datasetIndex].total;
                            let percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                            return label + ': ' + value + ' (' + percentage + ')';
                        }"),
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}