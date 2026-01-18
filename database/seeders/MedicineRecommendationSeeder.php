<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicineRecommendation;

class MedicineRecommendationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recommendations = [
            [
                'min_score' => 0,
                'max_score' => 15,
                'disease_name' => 'No major issue',
                'medicine_name' => 'No medicine needed',
                'advice' => 'Maintain a healthy lifestyle. This is not a diagnosis. Please consult a doctor if symptoms persist.',
            ],
            [
                'min_score' => 16,
                'max_score' => 30,
                'disease_name' => 'Mild viral infection',
                'medicine_name' => 'Paracetamol',
                'advice' => 'Stay hydrated and rest. This is not a diagnosis. Please consult a doctor if symptoms worsen.',
            ],
            [
                'min_score' => 31,
                'max_score' => 50,
                'disease_name' => 'Flu or fever',
                'medicine_name' => 'Ibuprofen',
                'advice' => 'Take prescribed medication and monitor symptoms. This is not a diagnosis. Please consult a doctor.',
            ],
            [
                'min_score' => 51,
                'max_score' => 70,
                'disease_name' => 'Severe infection',
                'medicine_name' => 'Antibiotics (doctor prescribed)',
                'advice' => 'Seek immediate medical attention. This is not a diagnosis. Please consult a doctor.',
            ],
            [
                'min_score' => 71,
                'max_score' => 999999, // Very high number to catch all scores above 70
                'disease_name' => 'Critical condition',
                'medicine_name' => 'Emergency medical care required',
                'advice' => 'URGENT: Seek immediate emergency medical attention. This is not a diagnosis. Please consult a doctor immediately.',
            ],
        ];

        foreach ($recommendations as $recommendation) {
            MedicineRecommendation::create($recommendation);
        }
    }
}