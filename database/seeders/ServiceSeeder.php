<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Katalog layanan dan harga awal sesuai spesifikasi.
     *
     * @return array<int, array{
     *     name: string, category: string, pricing_type: string, unit: string,
     *     price: int, estimated_days: ?int, is_same_day: bool
     * }>
     */
    public function data(): array
    {
        return [
            // Layanan berbasis kilogram
            ['name' => 'Cuci Lipat Reguler', 'category' => 'Cuci Lipat', 'pricing_type' => Service::PRICING_TYPE_KG, 'unit' => Service::UNIT_KG, 'price' => 3000, 'estimated_days' => 3, 'is_same_day' => false],
            ['name' => 'Cuci Lipat Express', 'category' => 'Cuci Lipat', 'pricing_type' => Service::PRICING_TYPE_KG, 'unit' => Service::UNIT_KG, 'price' => 6000, 'estimated_days' => 1, 'is_same_day' => false],
            ['name' => 'Cuci Lipat Kilat', 'category' => 'Cuci Lipat', 'pricing_type' => Service::PRICING_TYPE_KG, 'unit' => Service::UNIT_KG, 'price' => 8000, 'estimated_days' => 0, 'is_same_day' => true],
            ['name' => 'Setrika Reguler', 'category' => 'Setrika', 'pricing_type' => Service::PRICING_TYPE_KG, 'unit' => Service::UNIT_KG, 'price' => 4000, 'estimated_days' => 3, 'is_same_day' => false],
            ['name' => 'Setrika Express', 'category' => 'Setrika', 'pricing_type' => Service::PRICING_TYPE_KG, 'unit' => Service::UNIT_KG, 'price' => 8000, 'estimated_days' => 1, 'is_same_day' => false],
            ['name' => 'Setrika Kilat', 'category' => 'Setrika', 'pricing_type' => Service::PRICING_TYPE_KG, 'unit' => Service::UNIT_KG, 'price' => 10000, 'estimated_days' => 0, 'is_same_day' => true],
            ['name' => 'Cuci Setrika Reguler', 'category' => 'Cuci Setrika', 'pricing_type' => Service::PRICING_TYPE_KG, 'unit' => Service::UNIT_KG, 'price' => 5000, 'estimated_days' => 3, 'is_same_day' => false],
            ['name' => 'Cuci Setrika Express', 'category' => 'Cuci Setrika', 'pricing_type' => Service::PRICING_TYPE_KG, 'unit' => Service::UNIT_KG, 'price' => 10000, 'estimated_days' => 1, 'is_same_day' => false],
            ['name' => 'Cuci Setrika Kilat', 'category' => 'Cuci Setrika', 'pricing_type' => Service::PRICING_TYPE_KG, 'unit' => Service::UNIT_KG, 'price' => 12000, 'estimated_days' => 0, 'is_same_day' => true],
            // Layanan berbasis unit
            ['name' => 'Bedcover Besar', 'category' => 'Barang khusus', 'pricing_type' => Service::PRICING_TYPE_UNIT, 'unit' => Service::UNIT_PCS, 'price' => 25000, 'estimated_days' => 3, 'is_same_day' => false],
            ['name' => 'Bedcover Kecil/Sedang', 'category' => 'Barang khusus', 'pricing_type' => Service::PRICING_TYPE_UNIT, 'unit' => Service::UNIT_PCS, 'price' => 20000, 'estimated_days' => 3, 'is_same_day' => false],
            ['name' => 'Seprei', 'category' => 'Barang khusus', 'pricing_type' => Service::PRICING_TYPE_UNIT, 'unit' => Service::UNIT_PCS, 'price' => 12000, 'estimated_days' => 3, 'is_same_day' => false],
            ['name' => 'Seprei Set', 'category' => 'Barang khusus', 'pricing_type' => Service::PRICING_TYPE_UNIT, 'unit' => Service::UNIT_PCS, 'price' => 15000, 'estimated_days' => 3, 'is_same_day' => false],
            ['name' => 'Selimut Besar', 'category' => 'Barang khusus', 'pricing_type' => Service::PRICING_TYPE_UNIT, 'unit' => Service::UNIT_PCS, 'price' => 15000, 'estimated_days' => 3, 'is_same_day' => false],
            ['name' => 'Selimut Kecil/Sedang', 'category' => 'Barang khusus', 'pricing_type' => Service::PRICING_TYPE_UNIT, 'unit' => Service::UNIT_PCS, 'price' => 12000, 'estimated_days' => 3, 'is_same_day' => false],
        ];
    }

    public function run(): void
    {
        foreach ($this->data() as $service) {
            Service::query()->updateOrCreate(
                ['name' => $service['name']],
                $service,
            );
        }
    }
}
