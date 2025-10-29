<?php

namespace Database\Seeders;

use App\DifficultyLevel;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use App\QuestionType;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $editor = User::where('email', 'editor@quizball.com')->first();

        $this->seedPremierLeague($editor);
        $this->seedChampionsLeague($editor);
        $this->seedWorldCup($editor);
        $this->seedPlayersLegends($editor);
        $this->seedClubsStadiums($editor);
        $this->seedTacticsRules($editor);
        $this->seedFootballHistory($editor);
        $this->seedTransfersRecords($editor);
    }

    protected function seedPremierLeague($editor): void
    {
        $category = Category::where('name', 'Premier League')->first();

        // Easy Text Input
        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Which city is home to Manchester United?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Manchester', 'is_correct' => true]);

        // Easy Multiple Choice
        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'What color are Liverpool\'s home shirts?',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Easy,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Red', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Blue', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'White', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Yellow', 'is_correct' => false, 'order' => 4]);

        // Medium Multiple Choice
        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Who won the Premier League Golden Boot in 2023-24?',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Medium,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Erling Haaland', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Mohamed Salah', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Harry Kane', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Cole Palmer', 'is_correct' => false, 'order' => 4]);

        // Hard Text Input
        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Which team won the first ever Premier League title in 1992-93?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Hard,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Manchester United', 'is_correct' => true]);

        // Top 5 - Easy
        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Name 5 current Premier League clubs based in London',
            'question_type' => QuestionType::TopFive,
            'difficulty' => DifficultyLevel::Easy,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Arsenal', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Chelsea', 'is_correct' => true, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Tottenham', 'is_correct' => true, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'West Ham', 'is_correct' => true, 'order' => 4]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Crystal Palace', 'is_correct' => true, 'order' => 5]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Fulham', 'is_correct' => true, 'order' => 6]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Brentford', 'is_correct' => true, 'order' => 7]);
    }

    protected function seedChampionsLeague($editor): void
    {
        $category = Category::where('name', 'Champions League')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Which club has won the most Champions League titles?',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Easy,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Real Madrid', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Barcelona', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'AC Milan', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Bayern Munich', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Who won the Champions League in 2024?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Medium,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Real Madrid', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'In which year did Liverpool win their "Miracle of Istanbul" Champions League final?',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Hard,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '2005', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '2004', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '2006', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '2007', 'is_correct' => false, 'order' => 4]);
    }

    protected function seedWorldCup($editor): void
    {
        $category = Category::where('name', 'World Cup')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Which country won the 2022 FIFA World Cup?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Argentina', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'How many World Cups has Brazil won?',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Medium,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '5', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '4', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '6', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '3', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Name 5 countries that have won the FIFA World Cup',
            'question_type' => QuestionType::TopFive,
            'difficulty' => DifficultyLevel::Medium,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Brazil', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Germany', 'is_correct' => true, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Italy', 'is_correct' => true, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Argentina', 'is_correct' => true, 'order' => 4]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'France', 'is_correct' => true, 'order' => 5]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Uruguay', 'is_correct' => true, 'order' => 6]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'England', 'is_correct' => true, 'order' => 7]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Spain', 'is_correct' => true, 'order' => 8]);
    }

    protected function seedPlayersLegends($editor): void
    {
        $category = Category::where('name', 'Players & Legends')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Which player has won the most Ballon d\'Or awards?',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Easy,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Lionel Messi', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Cristiano Ronaldo', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Michel Platini', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Johan Cruyff', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'What is Cristiano Ronaldo\'s shirt number?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '7', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Who is known as "The Egyptian King"?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Medium,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Mohamed Salah', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Which Brazilian legend is known as "The Black Pearl"?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Hard,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Pele', 'is_correct' => true]);
    }

    protected function seedClubsStadiums($editor): void
    {
        $category = Category::where('name', 'Clubs & Stadiums')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'What is the name of Barcelona\'s home stadium?',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Easy,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Camp Nou', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Santiago Bernabeu', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Wanda Metropolitano', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Mestalla', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Which stadium is known as "The Theatre of Dreams"?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Medium,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Old Trafford', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'What is the capacity of Wembley Stadium?',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Hard,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '90,000', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '80,000', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '100,000', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '75,000', 'is_correct' => false, 'order' => 4]);
    }

    protected function seedTacticsRules($editor): void
    {
        $category = Category::where('name', 'Tactics & Rules')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'How many players are on a football team on the pitch?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '11', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'What does VAR stand for?',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Medium,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Video Assistant Referee', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Video Analysis Review', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Visual Assist Referee', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Video Action Replay', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'How long is each half of a standard football match in minutes?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '45', 'is_correct' => true]);
    }

    protected function seedFootballHistory($editor): void
    {
        $category = Category::where('name', 'Football History')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'In which year was the first FIFA World Cup held?',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Medium,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '1930', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '1920', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '1940', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '1950', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Which country hosted the first World Cup?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Hard,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Uruguay', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Who scored the "Hand of God" goal?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Medium,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Diego Maradona', 'is_correct' => true]);
    }

    protected function seedTransfersRecords($editor): void
    {
        $category = Category::where('name', 'Transfers & Records')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Which player holds the record for most goals in a calendar year?',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Medium,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Lionel Messi', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Cristiano Ronaldo', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Pele', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Gerd Muller', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Which player was transferred to PSG for a world record fee of €222 million?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Neymar', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Who is the all-time top scorer in Champions League history?',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Medium,
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Cristiano Ronaldo', 'is_correct' => true]);
    }
}