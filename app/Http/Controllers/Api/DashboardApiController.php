<?php

namespace App\Http\Controllers\Api;

use App\Models\Equipment;
use App\Models\LabBorrowing;
use App\Models\Laboratory;
use App\Models\Procurement;
use App\Models\ActivityLog;
use App\Models\DamageReport;
use Illuminate\Http\Request;

class DashboardApiController extends BaseApiController
{
    public function stats(Request $request)
    {
        $user = $request->user();
        $stats = [
            'total_equipment' => Equipment::count(),
            'total_laboratories' => Laboratory::count(),
            'active_borrowings' => LabBorrowing::whereIn('status', ['pending', 'approved'])->count(),
            'pending_procurements' => Procurement::where('status', 'pending')->count(),
            'equipment_baik' => Equipment::where('condition', 'baik')->count(),
            'equipment_rusak' => Equipment::whereIn('condition', ['rusak_ringan', 'rusak_berat'])->count(),
            'total_users' => \App\Models\User::count(),
        ];

        $data = [
            'stats' => $stats,
        ];

        if ($user->role === 'admin_lab' || $user->role === 'asisten_lab') {
            // Recent Activities
            $data['recent_activities'] = ActivityLog::with('user')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'user_name' => $log->user?->name ?? 'System',
                        'action' => $log->action,
                        'description' => $log->description,
                        'time' => $log->created_at->diffForHumans(),
                    ];
                });

            // Urgent Damage Reports
            $data['urgent_damage_reports'] = DamageReport::with('equipment')
                ->whereNotIn('status', ['repaired', 'closed'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'equipment_name' => $report->equipment?->name ?? 'Unknown',
                        'damage_type' => $report->damage_type,
                        'description' => $report->description,
                        'status' => $report->status,
                        'date' => $report->created_at->format('d M Y'),
                    ];
                });
        }

        if ($user->role === 'pengguna') {
            $stats['my_borrowings'] = LabBorrowing::where('user_id', $user->id)->count();
            $stats['my_procurements'] = Procurement::where('requested_by', $user->id)->count();
        }

        return $this->sendSuccess($data, 'Data dashboard berhasil dimuat');
    }
}
