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
    public function index()
    {
        $questions = Question::query()
            ->when(! auth()->user()->isAdmin(), function ($query) {
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
        if ($request->has('answers')) {
            $answers = array_filter($request->answers, function ($answer) {
                return ! empty($answer['text']);
            });
            $request->merge(['answers' => array_values($answers)]);
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|in:text_input,multiple_choice,top_5',
            'difficulty' => 'required|in:easy,medium,hard',
            'source_url' => 'required|url|max:500',
            'answers' => 'required|array|min:1',
            'answers.*.text' => 'required|string|max:500',
            'answers.*.is_correct' => 'sometimes|boolean',
            'correct_answer_id' => 'sometimes|required_if:question_type,multiple_choice',
        ]);

        // Determine if question should be auto-approved
        $user = auth()->user();
        $status = 'pending';
        $approvedBy = null;
        $approvedAt = null;

        if ($user->isAdmin() || $user->is_pre_validated) {
            $status = 'approved';
            $approvedBy = $user->id;
            $approvedAt = now();
        }

        $question = Question::create([
            'category_id' => $request->category_id,
            'created_by' => auth()->id(),
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'difficulty' => $request->difficulty,
            'source_url' => $request->source_url,
            'is_active' => true,
            'status' => $status,
            'approved_by' => $approvedBy,
            'approved_at' => $approvedAt,
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

        $message = $status === 'approved'
            ? 'Question created and approved automatically'
            : 'Question created successfully. Waiting for admin approval.';

        return redirect()->route('questions.index')
            ->with('success', $message);
    }

    public function edit(Question $question)
    {
        if (! auth()->user()->isAdmin() && $question->created_by !== auth()->id()) {
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
        if (! auth()->user()->isAdmin() && $question->created_by !== auth()->id()) {
            abort(403);
        }

        // Filter out empty answer fields before validation
        if ($request->has('answers')) {
            $answers = array_filter($request->answers, function ($answer) {
                return ! empty($answer['text']);
            });
            $request->merge(['answers' => array_values($answers)]);
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|in:text_input,multiple_choice,top_5',
            'difficulty' => 'required|in:easy,medium,hard',
            'source_url' => 'required|url|max:500',
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
            'source_url' => $request->source_url,
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
        if (! auth()->user()->isAdmin() && $question->created_by !== auth()->id()) {
            abort(403);
        }

        $question->delete();

        return redirect()->route('questions.index')
            ->with('success', 'Question deleted successfully');
    }

    public function approve(Question $question)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $question->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // Increment creator's approved count
        $creator = $question->creator;
        $creator->increment('approved_questions_count');

        // Auto pre-validate if reaches 10 approved questions
        if ($creator->approved_questions_count >= 10 && ! $creator->is_pre_validated) {
            $creator->update(['is_pre_validated' => true]);
        }

        return redirect()->back()
            ->with('success', 'Question approved successfully');
    }

    public function reject(Request $request, Question $question)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $question->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'is_active' => false,
        ]);

        return redirect()->back()
            ->with('success', 'Question rejected');
    }

    public function pending()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $questions = Question::where('status', 'pending')
            ->with(['category', 'creator', 'answers'])
            ->latest()
            ->paginate(20);

        return view('questions.pending', ['questions' => $questions]);
    }
}
