<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HealthQuestion;

class HealthQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            ['question' => 'Do you have a fever?', 'order' => 1, 'is_active' => true],
            ['question' => 'Are you experiencing a headache?', 'order' => 2, 'is_active' => true],
            ['question' => 'Do you have a persistent cough?', 'order' => 3, 'is_active' => true],
            ['question' => 'Are you feeling fatigued?', 'order' => 4, 'is_active' => true],
            ['question' => 'Do you have body pain?', 'order' => 5, 'is_active' => true],
            ['question' => 'Are you experiencing shortness of breath?', 'order' => 6, 'is_active' => true],
            ['question' => 'Do you have a sore throat?', 'order' => 7, 'is_active' => true],
            ['question' => 'Are you experiencing chills?', 'order' => 8, 'is_active' => true],
            ['question' => 'Do you have nausea or vomiting?', 'order' => 9, 'is_active' => true],
            ['question' => 'Are you experiencing diarrhea?', 'order' => 10, 'is_active' => true],
        ];

        foreach ($questions as $question) {
            HealthQuestion::create($question);
        }
    }
}