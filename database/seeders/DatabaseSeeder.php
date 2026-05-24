<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Users
        User::create([
            'name' => 'Admin Lab',
            'email' => 'admin@invlab.test',
            'password' => Hash::make('password'),
            'role' => 'admin_lab',
            'nim_nip' => '198501012020011001',
            'phone' => '081234567890',
            'department' => 'Teknik Informatika',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Asisten Lab 1',
            'email' => 'asisten@invlab.test',
            'password' => Hash::make('password'),
            'role' => 'asisten_lab',
            'nim_nip' => '20210001',
            'phone' => '081234567891',
            'department' => 'Teknik Informatika',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@invlab.test',
            'password' => Hash::make('password'),
            'role' => 'pengguna',
            'nim_nip' => '20220015',
            'phone' => '081234567892',
            'department' => 'Teknik Informatika',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Siti Rahayu',
            'email' => 'siti@invlab.test',
            'password' => Hash::make('password'),
            'role' => 'pengguna',
            'nim_nip' => '20220030',
            'phone' => '081234567893',
            'department' => 'Sistem Informasi',
            'is_active' => true,
        ]);

        // Categories
        $categories = [];
        foreach ([
            ['name' => 'Komputer & Laptop', 'slug' => 'komputer-laptop', 'description' => 'PC Desktop, Laptop, dan perangkat komputasi'],
            ['name' => 'Jaringan', 'slug' => 'jaringan', 'description' => 'Router, Switch, Kabel, dan perangkat jaringan'],
            ['name' => 'Elektronika', 'slug' => 'elektronika', 'description' => 'Arduino, Sensor, Multimeter, dan komponen elektronik'],
            ['name' => 'Multimedia', 'slug' => 'multimedia', 'description' => 'Kamera, Projector, Speaker, dan perangkat multimedia'],
            ['name' => 'Peralatan Umum', 'slug' => 'peralatan-umum', 'description' => 'Meja, kursi, whiteboard, dan perlengkapan umum'],
        ] as $cat) {
            $categories[] = EquipmentCategory::create($cat);
        }

        // Laboratories
        $labs = [];
        $admin = User::where('role', 'admin_lab')->first();
        $asisten = User::where('role', 'asisten_lab')->first();
        foreach ([
            ['name' => 'Lab Pemrograman', 'code' => 'LAB-PRG', 'location' => 'Gedung A Lt. 2 R.201', 'capacity' => 40, 'responsible_person_id' => $admin->id, 'status' => 'active', 'description' => 'Laboratorium untuk praktikum pemrograman dan pengembangan software'],
            ['name' => 'Lab Jaringan', 'code' => 'LAB-NET', 'location' => 'Gedung A Lt. 3 R.301', 'capacity' => 30, 'responsible_person_id' => $asisten->id, 'status' => 'active', 'description' => 'Laboratorium jaringan komputer dan administrasi sistem'],
            ['name' => 'Lab Multimedia', 'code' => 'LAB-MUL', 'location' => 'Gedung B Lt. 1 R.102', 'capacity' => 35, 'responsible_person_id' => $admin->id, 'status' => 'active', 'description' => 'Laboratorium multimedia, desain grafis, dan video editing'],
            ['name' => 'Lab Elektronika', 'code' => 'LAB-ELK', 'location' => 'Gedung C Lt. 1 R.101', 'capacity' => 25, 'responsible_person_id' => $asisten->id, 'status' => 'active', 'description' => 'Laboratorium elektronika dasar dan mikrokontroler'],
        ] as $lab) {
            $labs[] = Laboratory::create($lab);
        }

        // Equipment for Lab Pemrograman
        $pc_items = ['PC Desktop Core i7', 'PC Desktop Core i5', 'Monitor LED 24"'];
        $idx = 1;
        foreach ($pc_items as $name) {
            Equipment::create([
                'laboratory_id' => $labs[0]->id,
                'category_id' => $categories[0]->id,
                'name' => $name,
                'code' => 'PRG-' . str_pad($idx, 3, '0', STR_PAD_LEFT),
                'brand' => $idx <= 2 ? 'HP' : 'LG',
                'model' => $idx <= 2 ? 'ProDesk 400 G7' : '24MK430H',
                'year_acquired' => 2023,
                'price' => $idx <= 2 ? 12000000 : 2500000,
                'quantity' => $idx <= 2 ? 20 : 20,
                'condition' => 'baik',
                'status' => 'available',
                'description' => 'Unit ' . $name . ' untuk Lab Pemrograman',
            ]);
            $idx++;
        }

        // Equipment for Lab Jaringan
        foreach ([
            ['name' => 'Router Cisco 2901', 'code' => 'NET-001', 'brand' => 'Cisco', 'model' => '2901', 'price' => 15000000, 'qty' => 5],
            ['name' => 'Switch Managed 24 Port', 'code' => 'NET-002', 'brand' => 'Cisco', 'model' => 'SG350-28', 'price' => 8000000, 'qty' => 8],
            ['name' => 'UTP Crimping Tool', 'code' => 'NET-003', 'brand' => 'AMP', 'model' => 'Standard', 'price' => 250000, 'qty' => 15],
            ['name' => 'LAN Tester', 'code' => 'NET-004', 'brand' => 'Fluke', 'model' => 'LinkIQ', 'price' => 5000000, 'qty' => 5],
        ] as $eq) {
            Equipment::create([
                'laboratory_id' => $labs[1]->id,
                'category_id' => $categories[1]->id,
                'name' => $eq['name'],
                'code' => $eq['code'],
                'brand' => $eq['brand'],
                'model' => $eq['model'],
                'year_acquired' => 2022,
                'price' => $eq['price'],
                'quantity' => $eq['qty'],
                'condition' => 'baik',
                'status' => 'available',
            ]);
        }

        // Equipment for Lab Multimedia
        foreach ([
            ['name' => 'iMac 27"', 'code' => 'MUL-001', 'brand' => 'Apple', 'model' => 'iMac 2021', 'price' => 32000000, 'qty' => 10, 'cat' => 0],
            ['name' => 'Projector HD', 'code' => 'MUL-002', 'brand' => 'Epson', 'model' => 'EB-X51', 'price' => 8500000, 'qty' => 2, 'cat' => 3],
            ['name' => 'Kamera DSLR', 'code' => 'MUL-003', 'brand' => 'Canon', 'model' => 'EOS 80D', 'price' => 18000000, 'qty' => 5, 'cat' => 3],
        ] as $eq) {
            Equipment::create([
                'laboratory_id' => $labs[2]->id,
                'category_id' => $categories[$eq['cat']]->id,
                'name' => $eq['name'],
                'code' => $eq['code'],
                'brand' => $eq['brand'],
                'model' => $eq['model'],
                'year_acquired' => 2023,
                'price' => $eq['price'],
                'quantity' => $eq['qty'],
                'condition' => 'baik',
                'status' => 'available',
            ]);
        }

        // Equipment for Lab Elektronika
        foreach ([
            ['name' => 'Arduino Uno R3', 'code' => 'ELK-001', 'brand' => 'Arduino', 'model' => 'Uno R3', 'price' => 150000, 'qty' => 30],
            ['name' => 'Multimeter Digital', 'code' => 'ELK-002', 'brand' => 'Sanwa', 'model' => 'CD800a', 'price' => 750000, 'qty' => 15],
            ['name' => 'Oscilloscope Digital', 'code' => 'ELK-003', 'brand' => 'Siglent', 'model' => 'SDS1104X', 'price' => 7500000, 'qty' => 5],
            ['name' => 'Power Supply DC', 'code' => 'ELK-004', 'brand' => 'UNI-T', 'model' => 'UTP3305', 'price' => 2000000, 'qty' => 10, 'condition' => 'rusak_ringan'],
        ] as $eq) {
            Equipment::create([
                'laboratory_id' => $labs[3]->id,
                'category_id' => $categories[2]->id,
                'name' => $eq['name'],
                'code' => $eq['code'],
                'brand' => $eq['brand'],
                'model' => $eq['model'],
                'year_acquired' => 2021,
                'price' => $eq['price'],
                'quantity' => $eq['qty'],
                'condition' => $eq['condition'] ?? 'baik',
                'status' => 'available',
            ]);
        }
    }
}
