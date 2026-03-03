<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Outlet; // Add this import
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a Super Admin user
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'role' => 'Super Admin',
            'password' => bcrypt('password'),
            'outlet_id' => null, // Super Admin has access to all outlets
        ]);

        $outlets = Outlet::all();

        foreach ($outlets as $outlet) {
            // Create an Admin user for each outlet
            User::factory()->create([
                'name' => 'Admin ' . $outlet->name,
                'email' => 'admin_' . strtolower(str_replace(' ', '', $outlet->name)) . '@example.com',
                'role' => 'Admin',
                'password' => bcrypt('password'),
                'pin' => bcrypt('0000'),
                'outlet_id' => $outlet->id,
            ]);

            // Create a Manager user for each outlet (default PIN: 1234)
            User::factory()->create([
                'name' => 'Manager ' . $outlet->name,
                'email' => 'manager_' . strtolower(str_replace(' ', '', $outlet->name)) . '@example.com',
                'role' => 'Manager',
                'password' => bcrypt('password'),
                'pin' => bcrypt('1234'),
                'outlet_id' => $outlet->id,
            ]);

            // Create a Cashier user for each outlet (default PIN: 1234)
            User::factory()->create([
                'name' => 'Cashier ' . $outlet->name,
                'email' => 'cashier_' . strtolower(str_replace(' ', '', $outlet->name)) . '@example.com',
                'role' => 'Cashier',
                'password' => bcrypt('password'),
                'pin' => bcrypt('1234'),
                'outlet_id' => $outlet->id,
            ]);

            // Create a Viewer user for each outlet
            User::factory()->create([
                'name' => 'Viewer ' . $outlet->name,
                'email' => 'viewer_' . strtolower(str_replace(' ', '', $outlet->name)) . '@example.com',
                'role' => 'Viewer',
                'password' => bcrypt('password'),
                'outlet_id' => $outlet->id,
            ]);
        }
    }
}
