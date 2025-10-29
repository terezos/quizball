<?php

namespace App\Http\Controllers;

use App\DifficultyLevel;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\QuestionType;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'editor']);
    }

    public function index()
    {
        $questions = Question::query()
            ->when(!auth()->user()->isAdmin(), function ($query) {
                $query->where('created_by', auth()->id());
            })
            ->with(['category', 'creator', 'answers'])
            ->latest()
            ->paginate(20);

        return view('questions.index', ['questions' => $questions]);
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('questions.create', [
            'categories' => $categories,
            'questionTypes' => QuestionType::cases(),
            'difficulties' => DifficultyLevel::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|in:text_input,multiple_choice,top_5',
            'difficulty' => 'required|in:easy,medium,hard',
            'answers' => 'required|array|min:1',
            'answers.*.text' => 'required|string|max:500',
            'answers.*.is_correct' => 'sometimes|boolean',
            'correct_answer_id' => 'sometimes|required_if:question_type,multiple_choice',
        ]);

        $question = Question::create([
            'category_id' => $request->category_id,
            'created_by' => auth()->id(),
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'difficulty' => $request->difficulty,
            'is_active' => true,
        ]);

        foreach ($request->answers as $index => $answerData) {
            $isCorrect = match ($request->question_type) {
                'text_input', 'top_5' => (bool) ($answerData['is_correct'] ?? true),
                'multiple_choice' => $request->correct_answer_id == $index,
                default => false,
            };

            Answer::create([
                'question_id' => $question->id,
                'answer_text' => $answerData['text'],
                'is_correct' => $isCorrect,
                'order' => $index + 1,
            ]);
        }

        return redirect()->route('questions.index')
            ->with('success', 'Question created successfully');
    }

    public function edit(Question $question)
    {
        if (!auth()->user()->isAdmin() && $question->created_by !== auth()->id()) {
            abort(403);
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('questions.edit', [
            'question' => $question->load('answers'),
            'categories' => $categories,
            'questionTypes' => QuestionType::cases(),
            'difficulties' => DifficultyLevel::cases(),
        ]);
    }

    public function update(Request $request, Question $question)
    {
        if (!auth()->user()->isAdmin() && $question->created_by !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|in:text_input,multiple_choice,top_5',
            'difficulty' => 'required|in:easy,medium,hard',
            'answers' => 'required|array|min:1',
            'answers.*.text' => 'required|string|max:500',
            'answers.*.is_correct' => 'sometimes|boolean',
            'correct_answer_id' => 'sometimes|required_if:question_type,multiple_choice',
        ]);

        $question->update([
            'category_id' => $request->category_id,
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'difficulty' => $request->difficulty,
        ]);

        $question->answers()->delete();

        foreach ($request->answers as $index => $answerData) {
            $isCorrect = match ($request->question_type) {
                'text_input', 'top_5' => (bool) ($answerData['is_correct'] ?? true),
                'multiple_choice' => $request->correct_answer_id == $index,
                default => false,
            };

            Answer::create([
                'question_id' => $question->id,
                'answer_text' => $answerData['text'],
                'is_correct' => $isCorrect,
                'order' => $index + 1,
            ]);
        }

        return redirect()->route('questions.index')
            ->with('success', 'Question updated successfully');
    }

    public function destroy(Question $question)
    {
        if (!auth()->user()->isAdmin() && $question->created_by !== auth()->id()) {
            abort(403);
        }

        $question->delete();

        return redirect()->route('questions.index')
            ->with('success', 'Question deleted successfully');
    }
}