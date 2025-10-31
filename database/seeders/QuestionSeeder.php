<?php

namespace Database\Seeders;

use App\Enums\DifficultyLevel;
use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\User;
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
        $category = Category::where('name', 'Πρέμιερ Λιγκ')->first();

        // Easy Text Input
        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Σε ποια πόλη έχει έδρα η Μάντσεστερ Γιουνάιτεντ;',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
            'source_url' => 'https://www.manutd.com/en/club/faqs',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Μάντσεστερ', 'is_correct' => true]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Manchester', 'is_correct' => true]);

        // Easy Multiple Choice
        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Τι χρώμα είναι οι φανέλες της Λίβερπουλ στο γήπεδό της;',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Easy,
            'source_url' => 'https://www.liverpoolfc.com/history/kits',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Κόκκινο', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Μπλε', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Άσπρο', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Κίτρινο', 'is_correct' => false, 'order' => 4]);

        // Medium Multiple Choice
        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιος κέρδισε το Χρυσό Παπούτσι της Πρέμιερ Λιγκ το 2023-24;',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Medium,
            'source_url' => 'https://www.premierleague.com/stats/top/players/goals',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Έρλινγκ Χάαλαντ', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Μοχάμεντ Σαλάχ', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Χάρι Κέιν', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Κόουλ Πάλμερ', 'is_correct' => false, 'order' => 4]);

        // Hard Text Input
        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποια ομάδα κέρδισε τον πρώτο τίτλο της Πρέμιερ Λιγκ το 1992-93;',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Hard,
            'source_url' => 'https://www.premierleague.com/history',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Μάντσεστερ Γιουνάιτεντ', 'is_correct' => true]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Manchester United', 'is_correct' => true]);

        // Top 5 - Easy
        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ονόμασε 5 ομάδες της Πρέμιερ Λιγκ που βρίσκονται στο Λονδίνο',
            'question_type' => QuestionType::TopFive,
            'difficulty' => DifficultyLevel::Easy,
            'source_url' => 'https://www.premierleague.com/clubs',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Άρσεναλ', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Arsenal', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Τσέλσι', 'is_correct' => true, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Chelsea', 'is_correct' => true, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Τότεναμ', 'is_correct' => true, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Tottenham', 'is_correct' => true, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Γουέστ Χαμ', 'is_correct' => true, 'order' => 4]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'West Ham', 'is_correct' => true, 'order' => 4]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Κρίσταλ Πάλας', 'is_correct' => true, 'order' => 5]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Crystal Palace', 'is_correct' => true, 'order' => 5]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Φούλαμ', 'is_correct' => true, 'order' => 6]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Fulham', 'is_correct' => true, 'order' => 6]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Μπρέντφορντ', 'is_correct' => true, 'order' => 7]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Brentford', 'is_correct' => true, 'order' => 7]);
    }

    protected function seedChampionsLeague($editor): void
    {
        $category = Category::where('name', 'Τσάμπιονς Λιγκ')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποια ομάδα έχει κερδίσει τους περισσότερους τίτλους του Τσάμπιονς Λιγκ;',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Easy,
            'source_url' => 'https://www.uefa.com/uefachampionsleague/history/',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Ρεάλ Μαδρίτης', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Μπαρτσελόνα', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Μίλαν', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Μπάγερν Μονάχου', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιος κέρδισε το Τσάμπιονς Λιγκ το 2024;',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Medium,
            'source_url' => 'https://www.uefa.com/uefachampionsleague/match/2040441/',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Ρεάλ Μαδρίτης', 'is_correct' => true]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Real Madrid', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποια χρονιά η Λίβερπουλ κέρδισε τον τελικό του Τσάμπιονς Λιγκ με το "Θαύμα της Κωνσταντινούπολης";',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Hard,
            'source_url' => 'https://www.uefa.com/uefachampionsleague/match/1057684/',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '2005', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '2004', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '2006', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '2007', 'is_correct' => false, 'order' => 4]);
    }

    protected function seedWorldCup($editor): void
    {
        $category = Category::where('name', 'Παγκόσμιο Κύπελλο')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποια χώρα κέρδισε το Παγκόσμιο Κύπελλο της FIFA το 2022;',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
            'source_url' => 'https://www.fifa.com/tournaments/mens/worldcup/qatar2022',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Αργεντινή', 'is_correct' => true]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Argentina', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Πόσα Παγκόσμια Κύπελλα έχει κερδίσει η Βραζιλία;',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Medium,
            'source_url' => 'https://www.fifa.com/tournaments/mens/worldcup/history',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '5', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '4', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '6', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '3', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ονόμασε 5 χώρες που έχουν κερδίσει το Παγκόσμιο Κύπελλο της FIFA',
            'question_type' => QuestionType::TopFive,
            'difficulty' => DifficultyLevel::Medium,
            'source_url' => 'https://www.fifa.com/tournaments/mens/worldcup/history',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Βραζιλία', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Brazil', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Γερμανία', 'is_correct' => true, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Germany', 'is_correct' => true, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Ιταλία', 'is_correct' => true, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Italy', 'is_correct' => true, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Αργεντινή', 'is_correct' => true, 'order' => 4]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Argentina', 'is_correct' => true, 'order' => 4]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Γαλλία', 'is_correct' => true, 'order' => 5]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'France', 'is_correct' => true, 'order' => 5]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Ουρουγουάη', 'is_correct' => true, 'order' => 6]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Uruguay', 'is_correct' => true, 'order' => 6]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Αγγλία', 'is_correct' => true, 'order' => 7]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'England', 'is_correct' => true, 'order' => 7]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Ισπανία', 'is_correct' => true, 'order' => 8]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Spain', 'is_correct' => true, 'order' => 8]);
    }

    protected function seedPlayersLegends($editor): void
    {
        $category = Category::where('name', 'Παίκτες & Θρύλοι')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιος παίκτης έχει κερδίσει τις περισσότερες Χρυσές Μπάλες;',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Easy,
            'source_url' => 'https://www.francefootball.fr/ballon-dor',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Λιονέλ Μέσι', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Κριστιάνο Ρονάλντο', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Μισέλ Πλατινί', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Γιόχαν Κρόιφ', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιος είναι ο αριθμός φανέλας του Κριστιάνο Ρονάλντο;',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
            'source_url' => 'https://www.realmadrid.com/en-US/football/squad',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '7', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιος είναι γνωστός ως "Ο Αιγύπτιος Βασιλιάς";',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Medium,
            'source_url' => 'https://www.liverpoolfc.com/team/first-team/player/mohamed-salah',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Μοχάμεντ Σαλάχ', 'is_correct' => true]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Mohamed Salah', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιος Βραζιλιάνος θρύλος είναι γνωστός ως "Το Μαύρο Μαργαριτάρι";',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Hard,
            'source_url' => 'https://www.fifa.com/tournaments/mens/worldcup/awards/golden-ball',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Πελέ', 'is_correct' => true]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Pele', 'is_correct' => true]);
    }

    protected function seedClubsStadiums($editor): void
    {
        $category = Category::where('name', 'Ομάδες & Γήπεδα')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιο είναι το όνομα του γηπέδου της Μπαρτσελόνα;',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Easy,
            'source_url' => 'https://www.fcbarcelona.com/en/club/facilities/camp-nou',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Καμπ Νόου', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Σαντιάγο Μπερναμπέου', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Βάντα Μετροπολιτάνο', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Μεστάγια', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιο γήπεδο είναι γνωστό ως "Το Θέατρο των Ονείρων";',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Medium,
            'source_url' => 'https://www.manutd.com/en/old-trafford',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Ολντ Τράφορντ', 'is_correct' => true]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Old Trafford', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποια είναι η χωρητικότητα του γηπέδου Γουέμπλεϊ;',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Hard,
            'source_url' => 'https://www.wembleystadium.com/the-stadium',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '90.000', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '80.000', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '100.000', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '75.000', 'is_correct' => false, 'order' => 4]);
    }

    protected function seedTacticsRules($editor): void
    {
        $category = Category::where('name', 'Τακτικές & Κανόνες')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Πόσοι παίκτες υπάρχουν σε μια ποδοσφαιρική ομάδα στο γήπεδο;',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
            'source_url' => 'https://www.fifa.com/technical/football-laws/laws-of-the-game',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '11', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Τι σημαίνει το VAR;',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Medium,
            'source_url' => 'https://www.fifa.com/technical/refereeing/var',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Βοηθός Διαιτητή Βίντεο', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Ανάλυση Βίντεο Επανεξέτασης', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Οπτικός Βοηθός Διαιτητή', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Επανάληψη Βίντεο Δράσης', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Πόσα λεπτά διαρκεί κάθε ημίχρονο σε έναν κανονικό ποδοσφαιρικό αγώνα;',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
            'source_url' => 'https://www.fifa.com/technical/football-laws/laws-of-the-game',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '45', 'is_correct' => true]);
    }

    protected function seedFootballHistory($editor): void
    {
        $category = Category::where('name', 'Ιστορία Ποδοσφαίρου')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποια χρονιά διεξήχθη το πρώτο Παγκόσμιο Κύπελλο της FIFA;',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Medium,
            'source_url' => 'https://www.fifa.com/tournaments/mens/worldcup/1930uruguay',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '1930', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '1920', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '1940', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => '1950', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποια χώρα φιλοξένησε το πρώτο Παγκόσμιο Κύπελλο;',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Hard,
            'source_url' => 'https://www.fifa.com/tournaments/mens/worldcup/1930uruguay',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Ουρουγουάη', 'is_correct' => true]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Uruguay', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιος σκόραρε το γκολ με το "Χέρι του Θεού";',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Medium,
            'source_url' => 'https://www.fifa.com/tournaments/mens/worldcup/1986mexico',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Ντιέγκο Μαραντόνα', 'is_correct' => true]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Diego Maradona', 'is_correct' => true]);
    }

    protected function seedTransfersRecords($editor): void
    {
        $category = Category::where('name', 'Μεταγραφές & Ρεκόρ')->first();

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιος παίκτης κατέχει το ρεκόρ περισσότερων γκολ σε ημερολογιακό έτος;',
            'question_type' => QuestionType::MultipleChoice,
            'difficulty' => DifficultyLevel::Medium,
            'source_url' => 'https://www.fifa.com/news/messi-breaks-goalscoring-record',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Λιονέλ Μέσι', 'is_correct' => true, 'order' => 1]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Κριστιάνο Ρονάλντο', 'is_correct' => false, 'order' => 2]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Πελέ', 'is_correct' => false, 'order' => 3]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Γκερντ Μίλερ', 'is_correct' => false, 'order' => 4]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιος παίκτης μεταγράφηκε στην PSG για παγκόσμιο ρεκόρ €222 εκατομμυρίων;',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Easy,
            'source_url' => 'https://www.psg.fr/equipes/first-team/content/neymar-jr-joins-paris-saint-germain',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Νεϊμάρ', 'is_correct' => true]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Neymar', 'is_correct' => true]);

        $q = Question::create([
            'category_id' => $category->id,
            'created_by' => $editor->id,
            'question_text' => 'Ποιος είναι ο πρώτος σκόρερ όλων των εποχών στην ιστορία του Τσάμπιονς Λιγκ;',
            'question_type' => QuestionType::TextInput,
            'difficulty' => DifficultyLevel::Medium,
            'source_url' => 'https://www.uefa.com/uefachampionsleague/history/rankings/players/goals_scored/',
        ]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Κριστιάνο Ρονάλντο', 'is_correct' => true]);
        Answer::create(['question_id' => $q->id, 'answer_text' => 'Cristiano Ronaldo', 'is_correct' => true]);
    }
}
