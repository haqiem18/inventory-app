<div style="max-height: 420px; overflow-y: auto; overflow-x: auto; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; background-color: #1e1e24; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; font-family: sans-serif; color: #d1d5db;">
        <thead style="position: sticky; top: 0; z-index: 10; background-color: #27272f; border-bottom: 2px solid rgba(255,255,255,0.15);">
            <tr>
                <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #ffffff;">Tanggal</th>
                <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #ffffff;">Tipe</th>
                <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #ffffff;">Cabang Asal</th>
                <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #ffffff;">Cabang Tujuan</th>
                <th style="padding: 12px 16px; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #ffffff; text-align: right;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mutations as $mutation)
                @php
                    $isVoid = ($mutation->status === 'VOID');
                    $isMasuk = !$isVoid && ($mutation->to_branch_id == $currentBranchId || $mutation->type === 'Masuk');
                    $isKeluar = !$isVoid && ($mutation->branch_id == $currentBranchId && $mutation->to_branch_id);
                @endphp
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); {{ $isVoid ? 'opacity: 0.6;' : '' }}">
                    <!-- Tanggal -->
                    <td style="padding: 12px 16px; white-space: nowrap; color: #9ca3af;">
                        {{ \Carbon\Carbon::parse($mutation->mutation_date)->format('d M Y H:i') }}
                    </td>
                    
                    <!-- Tipe -->
                    <td style="padding: 12px 16px; white-space: nowrap;">
                        @if($isVoid)
                            <span style="display: inline-block; padding: 4px 8px; font-size: 12px; font-weight: 500; border-radius: 6px; background-color: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3);">
                                VOID
                            </span>
                        @elseif($isKeluar)
                            <span style="display: inline-block; padding: 4px 8px; font-size: 12px; font-weight: 500; border-radius: 6px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">
                                Mutasi Keluar
                            </span>
                        @elseif($mutation->type === 'OUT')
                            <span style="display: inline-block; padding: 4px 8px; font-size: 12px; font-weight: 500; border-radius: 6px; background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                                OUT
                            </span>
                        @elseif($isMasuk && $mutation->to_branch_id)
                            <span style="display: inline-block; padding: 4px 8px; font-size: 12px; font-weight: 500; border-radius: 6px; background-color: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3);">
                                Mutasi Masuk
                            </span>
                        @elseif($mutation->type === 'Masuk')
                            <span style="display: inline-block; padding: 4px 8px; font-size: 12px; font-weight: 500; border-radius: 6px; background-color: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3);">
                                Masuk
                            </span>
                        @else
                            <span style="display: inline-block; padding: 4px 8px; font-size: 12px; font-weight: 500; border-radius: 6px; background-color: rgba(255, 255, 255, 0.05); color: #9ca3af; border: 1px solid rgba(255, 255, 255, 0.1);">
                                {{ ucfirst($mutation->type ?? 'Penyesuaian') }}
                            </span>
                        @endif
                    </td>
                    
                    <!-- Cabang -->
                    <td style="padding: 12px 16px; color: #e5e7eb;">{{ $mutation->branch?->name ?? '-' }}</td>
                    <td style="padding: 12px 16px; color: #e5e7eb;">{{ $mutation->toBranch?->name ?? '-' }}</td>
                    
                    <!-- Jumlah -->
                    <td style="padding: 12px 16px; text-align: right; white-space: nowrap; font-weight: 600;">
                        @if($isVoid)
                            <span style="color: #c084fc; text-decoration: line-through;">{{ number_format($mutation->quantity) }}</span>
                        @elseif($isMasuk)
                            <span style="color: #34d399;">+{{ number_format($mutation->quantity) }}</span>
                        @else
                            <span style="color: #f87171;">-{{ number_format($mutation->quantity) }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding: 32px 16px; text-align: center; color: #6b7280;">
                        Tidak ada riwayat mutasi untuk barang ini di cabang sekarang.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>