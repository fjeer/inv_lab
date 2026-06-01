<?php

namespace App\Http\Controllers;

use App\Models\DamageReport;
use App\Models\Equipment;
use App\Models\EquipmentItem;
use App\Models\LabBorrowing;
use App\Models\Laboratory;
use App\Models\PatrolSchedule;
use App\Models\Procurement;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match (true) {
            $user->isAdmin() => $this->adminDashboard(),
            $user->isAsisten() => $this->asistenDashboard($user),
            default => $this->penggunaDashboard($user),
        };
    }

    private function adminDashboard()
    {
        $stats = [
            'total_labs' => Laboratory::count(),
            'total_equipment' => Equipment::count(),
            'total_stok' => EquipmentItem::count(),
            'total_users' => User::count(),
            'equipment_baik' => Equipment::where('condition', 'baik')->count(),
            'equipment_rusak' => Equipment::whereIn('condition', ['rusak_ringan', 'rusak_berat'])->count(),
            'pending_borrowings' => LabBorrowing::where('status', 'pending')->count(),
            'open_damage_reports' => DamageReport::whereNotIn('status', ['closed', 'repaired'])->count(),
            'pending_procurements' => Procurement::whereIn('status', ['submitted', 'in_review'])->count(),
            'active_patrol_schedules' => PatrolSchedule::active()->count(),
        ];

        $recentBorrowings = LabBorrowing::with(['user', 'laboratory'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $recentDamageReports = DamageReport::with(['equipment', 'reporter'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentBorrowings', 'recentDamageReports'));
    }

    private function asistenDashboard(User $user)
    {
        $stats = [
            'total_equipment' => Equipment::count(),
            'total_stok' => EquipmentItem::count(),
            'equipment_baik' => Equipment::where('condition', 'baik')->count(),
            'equipment_rusak' => Equipment::whereIn('condition', ['rusak_ringan', 'rusak_berat'])->count(),
            'pending_borrowings' => LabBorrowing::where('status', 'pending')->count(),
            'open_damage_reports' => DamageReport::whereNotIn('status', ['closed', 'repaired'])->count(),
        ];

        $recentBorrowings = LabBorrowing::with(['user', 'laboratory'])
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->limit(5)
            ->get();



        // Patrol schedules for today assigned to this asisten (Quick Start)
        $todayPatrols = PatrolSchedule::with('laboratory')
            ->forUser($user->id)
            ->forToday()
            ->active()
            ->orderBy('start_time')
            ->get();

        return view('dashboard.asisten', compact('stats', 'recentBorrowings', 'todayPatrols'));
    }

    private function penggunaDashboard(User $user)
    {
        $stats = [
            'my_borrowings' => LabBorrowing::where('user_id', $user->id)->count(),
            'pending_borrowings' => LabBorrowing::where('user_id', $user->id)->where('status', 'pending')->count(),
            'my_damage_reports' => DamageReport::where('reported_by', $user->id)->count(),
            'my_procurements' => Procurement::where('requested_by', $user->id)->count(),
        ];

        $myBorrowings = LabBorrowing::with('laboratory')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(5)
            ->get();



        return view('dashboard.pengguna', compact('stats', 'myBorrowings'));
    }
}
