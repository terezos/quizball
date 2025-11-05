<?php

namespace App\Console\Commands;

use App\Enums\DifficultyLevel;
use App\Enums\QuestionType;
use App\Enums\SportEnum;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportQuestionsFromJson extends Command
{
    protected $signature = 'questions:import
                            {file : The JSON file to import (basketball_questions.json or footballs_questions.json)}
                            {--user-id=1 : The user ID to assign as creator}';

    protected $description = 'Import questions from JSON files and create categories if they do not exist';

    /**
     * @throws \Throwable
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $filename = $this->argument('file');
        $userId = $this->option('user-id');

        $filepath = base_path($filename);

        if (!File::exists($filepath)) {
            $this->error("File not found: {$filepath}");
            return 1;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }

        $sport = $this->determineSport($filename);
        if (!$sport) {
            $this->error("Could not determine sport from filename. Use 'basketball_questions.json' or 'footballs_questions.json'");
            return 1;
        }

        $this->info("Importing questions from: {$filename}");
        $this->info("Sport: {$sport->label()}");
        $this->info("Created by user: {$user->email}");

        $jsonContent = File::get($filepath);
        $questions = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Invalid JSON format: " . json_last_error_msg());
            return 1;
        }

        $this->info("Found " . count($questions) . " questions to import");

        $progressBar = $this->output->createProgressBar(count($questions));
        $progressBar->start();

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        DB::beginTransaction();

        try {
            foreach ($questions as $questionData) {
                try {
                    $category = $this->getOrCreateCategory($questionData['category'], $sport);

                    $difficulty = $this->mapDifficulty($questionData['difficulty']);
                    $questionType = $this->mapQuestionType($questionData['type']);

                    $question = Question::create([
                        'category_id' => $category->id,
                        'created_by' => $user->id,
                        'question_text' => $questionData['question'],
                        'question_type' => $questionType,
                        'difficulty' => $difficulty,
                        'source_url' => $questionData['source'] ?? null,
                        'is_active' => true,
                    ]);

                    foreach ($questionData['answers'] as $index => $answerData) {
                        Answer::create([
                            'question_id' => $question->id,
                            'answer_text' => $answerData['answer'],
                            'is_correct' => $answerData['is_correct'],
                            'order' => $index,
                        ]);
                    }

                    $imported++;
                } catch (\Exception $e) {
                    $errors++;
                    $this->newLine();
                    $this->error("Error importing question: " . $e->getMessage());
                }

                $progressBar->advance();
            }

            DB::commit();
            $progressBar->finish();

            $this->newLine(2);
            $this->info("Import completed!");
            $this->info("Imported: {$imported}");
            if ($errors > 0) {
                $this->error("Errors: {$errors}");
            }

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            return 1;
        }
    }

    protected function determineSport(string $filename): ?SportEnum
    {
        if (str_contains($filename, 'basketball')) {
            return SportEnum::Basketball;
        }
        if (str_contains($filename, 'football')) {
            return SportEnum::Football;
        }
        return null;
    }

    protected function getOrCreateCategory(string $categoryName, SportEnum $sport): Category
    {
        $category = Category::where('name', $categoryName)
            ->where('sport', $sport->value)
            ->first();

        if (!$category) {
            $maxOrder = Category::where('sport', $sport->value)->max('order') ?? 0;

            $category = Category::create([
                'name' => $categoryName,
                'sport' => $sport->value,
                'icon' => $sport->icon(),
                'order' => $maxOrder + 1,
                'is_active' => true,
            ]);

            $this->newLine();
            $this->info("Created new category: {$categoryName} ({$sport->label()})");
        }

        return $category;
    }

    protected function mapDifficulty(int $difficulty): DifficultyLevel
    {
        return match($difficulty) {
            1 => DifficultyLevel::Easy,
            3 => DifficultyLevel::Hard,
            default => DifficultyLevel::Medium,
        };
    }

    protected function mapQuestionType(string $type): QuestionType
    {
        return match($type) {
            'multiple_choice' => QuestionType::MultipleChoice,
            'top_5' => QuestionType::TopFive,
            'text_input_with_image' => QuestionType::TextInputWithImage,
            default => QuestionType::TextInput,
        };
    }
}
