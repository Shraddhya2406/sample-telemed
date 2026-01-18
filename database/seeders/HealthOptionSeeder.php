<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HealthOption;
use App\Models\HealthQuestion;

class HealthOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = HealthQuestion::all();

        foreach ($questions as $question) {
            $options = [
                ['option_text' => 'No', 'score' => 0],
                ['option_text' => 'Mild', 'score' => 2],
                ['option_text' => 'Moderate', 'score' => 5],
                ['option_text' => 'Severe', 'score' => 8],
            ];

            foreach ($options as $option) {
                HealthOption::create([
                    'health_question_id' => $question->id,
                    'option_text' => $option['option_text'],
                    'score' => $option['score'],
                ]);
            }
        }
    }
}