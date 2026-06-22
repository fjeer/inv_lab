<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;
use App\Models\DamageReport;
use App\Models\Equipment;
use App\Models\EquipmentItem;
use App\Models\LabBorrowing;
use App\Models\Laboratory;
use App\Models\Procurement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends BaseApiController
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = [
            'total_equipment' => Equipment::count(),
            'total_stok' => EquipmentItem::count(),
            'total_laboratories' => Laboratory::count(),
            'active_borrowings' => LabBorrowing::whereIn('status', ['pending', 'approved'])->count(),
            'pending_procurements' => Procurement::where('status', 'submitted')->count(),
            'equipment_baik' => Equipment::where('condition', 'baik')->count(),
            'equipment_rusak' => Equipment::whereIn('condition', ['rusak_ringan', 'rusak_berat'])->count(),
            'total_users' => User::count(),
        ];

        $data = [
            'stats' => $stats,
        ];

        if ($user->isAdmin() || $user->isAsisten()) {
            $data['recent_activities'] = ActivityLog::with('user')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($log) => [
                    'id' => $log->id,
                    'user_name' => $log->user?->name ?? 'System',
                    'action' => $log->action,
                    'description' => $log->description,
                    'time' => $log->created_at->diffForHumans(),
                ]);

            $data['urgent_damage_reports'] = DamageReport::with('equipment')
                ->whereNotIn('status', ['repaired', 'closed'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($report) => [
                    'id' => $report->id,
                    'equipment_name' => $report->equipment?->name ?? 'Unknown',
                    'damage_type' => $report->damage_type,
                    'description' => $report->description,
                    'status' => $report->status,
                    'date' => $report->created_at->format('d M Y'),
                ]);
        }

        if ($user->isPengguna()) {
            $stats['my_borrowings'] = LabBorrowing::where('user_id', $user->id)->count();
            $stats['my_procurements'] = Procurement::where('requested_by', $user->id)->count();
        }

        return $this->sendSuccess($data, 'Data dashboard berhasil dimuat');
    }
}
