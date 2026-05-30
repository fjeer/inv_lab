<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;
use App\Models\PatrolSchedule;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /* ---- Roles ---- */
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Admin Lab',
            'description' => 'Administrator laboratorium dengan akses penuh',
        ]);

        $asistenRole = Role::create([
            'name' => 'asisten',
            'display_name' => 'Asisten Lab',
            'description' => 'Asisten laboratorium untuk patroli dan monitoring alat',
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'display_name' => 'Pengguna',
            'description' => 'Pengguna umum yang dapat melihat kondisi lab',
            'is_default' => true,
        ]);

        /* ---- Permissions ---- */
        $permissionsData = [
            // Equipment
            ['name' => 'manage-equipment', 'display_name' => 'Kelola Alat Lab', 'group' => 'equipment'],
            ['name' => 'view-equipment', 'display_name' => 'Lihat Alat Lab', 'group' => 'equipment'],
            ['name' => 'print-qrcode', 'display_name' => 'Cetak QR Code', 'group' => 'equipment'],

            // Patrol
            ['name' => 'manage-patrol-schedule', 'display_name' => 'Kelola Jadwal Patroli', 'group' => 'patrol'],
            ['name' => 'execute-patrol', 'display_name' => 'Laksanakan Patroli', 'group' => 'patrol'],
            ['name' => 'view-patrol-logs', 'display_name' => 'Lihat Log Patroli', 'group' => 'patrol'],

            // Users & Roles
            ['name' => 'manage-users', 'display_name' => 'Kelola Pengguna', 'group' => 'users'],
            ['name' => 'manage-roles', 'display_name' => 'Kelola Role & Hak Akses', 'group' => 'users'],

            // Labs
            ['name' => 'manage-labs', 'display_name' => 'Kelola Laboratorium', 'group' => 'labs'],
            ['name' => 'view-labs', 'display_name' => 'Lihat Laboratorium', 'group' => 'labs'],
            ['name' => 'view-lab-conditions', 'display_name' => 'Lihat Kondisi Lab', 'group' => 'labs'],

            // Borrowings
            ['name' => 'manage-borrowings', 'display_name' => 'Kelola Peminjaman', 'group' => 'borrowings'],
            ['name' => 'create-borrowing', 'display_name' => 'Ajukan Peminjaman', 'group' => 'borrowings'],

            // Reports
            ['name' => 'manage-damage-reports', 'display_name' => 'Kelola Laporan Kerusakan', 'group' => 'reports'],
            ['name' => 'create-damage-report', 'display_name' => 'Buat Laporan Kerusakan', 'group' => 'reports'],

            // Procurements
            ['name' => 'manage-procurements', 'display_name' => 'Kelola Pengadaan', 'group' => 'procurements'],
            ['name' => 'create-procurement', 'display_name' => 'Ajukan Pengadaan', 'group' => 'procurements'],
        ];

        $permissions = collect($permissionsData)->map(fn ($p) => Permission::create($p));

        // Admin gets all permissions
        $adminRole->permissions()->attach($permissions->pluck('id'));

        // Asisten gets specific permissions
        $asistenRole->permissions()->attach(
            $permissions->whereIn('name', [
                'view-equipment', 'execute-patrol', 'view-patrol-logs',
                'view-labs', 'view-lab-conditions',
                'manage-borrowings', 'create-borrowing',
                'create-damage-report', 'create-procurement',
            ])->pluck('id')
        );

        // User gets limited permissions
        $userRole->permissions()->attach(
            $permissions->whereIn('name', [
                'view-labs', 'view-lab-conditions',
                'create-borrowing', 'create-damage-report', 'create-procurement',
            ])->pluck('id')
        );

        /* ---- Users ---- */
        $admin = User::create([
            'name' => 'Admin Lab',
            'email' => 'admin@invlab.test',
            'password' => 'password',
            'role' => 'admin_lab',
            'role_id' => $adminRole->id,
            'nim_nip' => '198001012005011001',
            'phone' => '081234567890',
            'department' => 'Teknik Informatika',
            'is_active' => true,
        ]);

        $asisten1 = User::create([
            'name' => 'Budi Asisten',
            'email' => 'asisten@invlab.test',
            'password' => 'password',
            'role' => 'asisten_lab',
            'role_id' => $asistenRole->id,
            'nim_nip' => '2021001001',
            'phone' => '081234567891',
            'department' => 'Teknik Informatika',
            'is_active' => true,
        ]);

        $asisten2 = User::create([
            'name' => 'Sari Asisten',
            'email' => 'asisten2@invlab.test',
            'password' => 'password',
            'role' => 'asisten_lab',
            'role_id' => $asistenRole->id,
            'nim_nip' => '2021001002',
            'phone' => '081234567892',
            'department' => 'Teknik Informatika',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Mahasiswa User',
            'email' => 'user@invlab.test',
            'password' => 'password',
            'role' => 'pengguna',
            'role_id' => $userRole->id,
            'nim_nip' => '2023001001',
            'phone' => '081234567893',
            'department' => 'Teknik Informatika',
            'is_active' => true,
        ]);

        /* ---- Buildings → Rooms → Labs ---- */
        $building = Building::create([
            'name' => 'Gedung A',
            'code' => 'GA',
            'description' => 'Gedung utama Fakultas Teknik',
        ]);

        $room1 = Room::create([
            'building_id' => $building->id,
            'name' => 'Ruang 101',
            'code' => 'R101',
            'floor' => '1',
        ]);

        $room2 = Room::create([
            'building_id' => $building->id,
            'name' => 'Ruang 201',
            'code' => 'R201',
            'floor' => '2',
        ]);

        $lab1 = Laboratory::create([
            'name' => 'Lab Komputer Dasar',
            'code' => 'LKD',
            'room_id' => $room1->id,
            'location' => 'Gedung A, Lantai 1',
            'capacity' => 30,
            'description' => 'Laboratorium untuk praktikum dasar komputer',
            'responsible_person_id' => $admin->id,
            'status' => 'active',
        ]);

        $lab2 = Laboratory::create([
            'name' => 'Lab Jaringan',
            'code' => 'LJR',
            'room_id' => $room2->id,
            'location' => 'Gedung A, Lantai 2',
            'capacity' => 25,
            'description' => 'Laboratorium untuk praktikum jaringan komputer',
            'responsible_person_id' => $admin->id,
            'status' => 'active',
        ]);

        /* ---- Equipment Categories ---- */
        $catKomputer = EquipmentCategory::create(['name' => 'Komputer', 'description' => 'Perangkat komputer dan laptop']);
        $catJaringan = EquipmentCategory::create(['name' => 'Jaringan', 'description' => 'Perangkat jaringan']);
        $catElektronik = EquipmentCategory::create(['name' => 'Elektronik', 'description' => 'Perangkat elektronik umum']);

        /* ---- Equipment + Auto-Generate Items with QR ---- */
        $equipments = [
            [
                'laboratory_id' => $lab1->id,
                'category_id' => $catKomputer->id,
                'name' => 'PC Desktop',
                'code' => 'EQ-001',
                'brand' => 'Lenovo',
                'quantity' => 5,
                'condition' => 'baik',
                'status' => 'available',
            ],
            [
                'laboratory_id' => $lab1->id,
                'category_id' => $catElektronik->id,
                'name' => 'Monitor LCD',
                'code' => 'EQ-002',
                'brand' => 'Samsung',
                'quantity' => 5,
                'condition' => 'baik',
                'status' => 'available',
            ],
            [
                'laboratory_id' => $lab2->id,
                'category_id' => $catJaringan->id,
                'name' => 'Switch Managed',
                'code' => 'EQ-003',
                'brand' => 'Cisco',
                'quantity' => 3,
                'condition' => 'baik',
                'status' => 'available',
            ],
            [
                'laboratory_id' => $lab2->id,
                'category_id' => $catJaringan->id,
                'name' => 'Router',
                'code' => 'EQ-004',
                'brand' => 'MikroTik',
                'quantity' => 2,
                'condition' => 'baik',
                'status' => 'available',
            ],
        ];

        foreach ($equipments as $eqData) {
            $eq = Equipment::create($eqData);
            $eq->generateItems(); // Auto-generates individual items with QR codes
        }

        /* ---- Patrol Schedules ---- */
        $todayDay = strtolower(now()->format('l'));

        PatrolSchedule::create([
            'user_id' => $asisten1->id,
            'laboratory_id' => $lab1->id,
            'day_of_week' => $todayDay,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'status' => 'active',
            'notes' => 'Patroli pagi Lab Komputer Dasar',
        ]);

        PatrolSchedule::create([
            'user_id' => $asisten1->id,
            'laboratory_id' => $lab2->id,
            'day_of_week' => $todayDay,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'active',
            'notes' => 'Patroli pagi Lab Jaringan',
        ]);

        PatrolSchedule::create([
            'user_id' => $asisten2->id,
            'laboratory_id' => $lab1->id,
            'day_of_week' => $todayDay,
            'start_time' => '13:00',
            'end_time' => '15:00',
            'status' => 'active',
            'notes' => 'Patroli siang Lab Komputer Dasar',
        ]);
    }
}
