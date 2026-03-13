<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicine;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Paracetamol 500mg', 'description' => 'Pain reliever and fever reducer.', 'price' => 49.00, 'stock_quantity' => 200, 'category' => 'Analgesic', 'is_active' => true],
            ['name' => 'Ibuprofen 200mg', 'description' => 'NSAID for pain and inflammation.', 'price' => 79.00, 'stock_quantity' => 150, 'category' => 'Analgesic', 'is_active' => true],
            ['name' => 'Cetirizine 10mg', 'description' => 'Antihistamine for allergies.', 'price' => 59.00, 'stock_quantity' => 120, 'category' => 'Antihistamine', 'is_active' => true],
            ['name' => 'Amoxicillin 500mg', 'description' => 'Broad-spectrum antibiotic.', 'price' => 129.00, 'stock_quantity' => 80, 'category' => 'Antibiotic', 'is_active' => true],
            ['name' => 'Azithromycin 250mg', 'description' => 'Macrolide antibiotic.', 'price' => 199.00, 'stock_quantity' => 60, 'category' => 'Antibiotic', 'is_active' => true],
            ['name' => 'Omeprazole 20mg', 'description' => 'Proton pump inhibitor for acid reflux.', 'price' => 89.00, 'stock_quantity' => 100, 'category' => 'Gastro', 'is_active' => true],
            ['name' => 'Multivitamin Tablets', 'description' => 'Daily multivitamin supplement.', 'price' => 149.00, 'stock_quantity' => 300, 'category' => 'Supplement', 'is_active' => true],
            ['name' => 'Cough Syrup (100ml)', 'description' => 'Expectorant cough syrup.', 'price' => 99.00, 'stock_quantity' => 90, 'category' => 'Respiratory', 'is_active' => true],
        ];

        foreach ($data as $item) {
            Medicine::create($item);
        }
    }
}
