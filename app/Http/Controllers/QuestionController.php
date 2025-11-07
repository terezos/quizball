<?php

namespace App\Http\Controllers;

use App\Enums\DifficultyLevel;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use App\Enums\UserRole;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $categoriesBySport = Category::where('is_active', true)
            ->orderBy('sport')
            ->orderBy('name')
            ->get()
            ->groupBy('sport');

        $editors = Question::select('created_by')
            ->distinct()
            ->with('creator:id,name')
            ->get()
            ->pluck('creator')
            ->sortBy('name')->mapWithKeys(function ($user) {
                return [$user->id => $user->name];
            });

        $questions = Question::query()
            ->when(! auth()->user()->isAdmin(), function ($query) {
                $query->where('created_by', auth()->id());
            })
            ->when($request->has('categories') && is_array($request->categories), function ($query) use ($request) {
                $query->whereIn('category_id', $request->categories);
            })
            ->when($request->get('created_by'), function ($query) use ($request) {
                $query->where('created_by', (int) $request->get('created_by'));
            })
            ->with(['category', 'creator', 'answers'])
            ->latest()
            ->paginate(10)
            ->appends($request->only(['categories', 'created_by']));

        return view('questions.index', [
            'questions' => $questions,
            'categoriesBySport' => $categoriesBySport,
            'editors' => $editors,
        ]);
    }

    public function create()
    {
        $categoriesBySport = Category::where('is_active', true)
            ->orderBy('sport')
            ->orderBy('name')
            ->get()
            ->groupBy('sport');

        return view('questions.create', [
            'categoriesBySport' => $categoriesBySport,
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
            'question_type' => 'required|in:text_input,multiple_choice,top_5,text_input_with_image',
            'difficulty' => 'required|in:easy,medium,hard',
            'source_url' => 'required|url|max:500',
            'image' => 'required_if:question_type,text_input_with_image|nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'answers' => 'required|array|min:1',
            'answers.*.text' => 'required|string|max:500',
            'answers.*.is_correct' => 'sometimes|boolean',
            'correct_answer_id' => 'sometimes|required_if:question_type,multiple_choice',
        ]);

        // Handle image upload
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('questions', 'public');
            $imageUrl = 'storage/'.$imagePath;
        }

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
            'image_url' => $imageUrl,
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
                'text_input', 'text_input_with_image', 'top_5' => (bool) ($answerData['is_correct'] ?? true),
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

        $categoriesBySport = Category::where('is_active', true)
            ->orderBy('sport')
            ->orderBy('name')
            ->get()
            ->groupBy('sport');

        return view('questions.edit', [
            'question' => $question->load('answers'),
            'categoriesBySport' => $categoriesBySport,
            'questionTypes' => QuestionType::cases(),
            'difficulties' => DifficultyLevel::cases(),
        ]);
    }

    public function update(Request $request, Question $question)
    {
        if (! auth()->user()->isAdmin() && $question->created_by !== auth()->id()) {
            abort(403);
        }

        // Prevent editing questions in active games
        if (! $question->canBeEdited()) {
            return back()->with('error', 'Αυτή η ερώτηση χρησιμοποιείται σε ενεργά παιχνίδια και δεν μπορεί να επεξεργαστεί.');
        }

        if ($request->has('answers')) {
            $answers = array_filter($request->answers, function ($answer) {
                return ! empty($answer['text']);
            });
            $request->merge(['answers' => array_values($answers)]);
        }

        $status = $question->status;
        if ($question->status == QuestionStatus::Rejected) {
            if ((auth()->user()->isAdmin() || auth()->user()->is_pre_validated)) {
                $status = QuestionStatus::Approved;
                $approvedBy = auth()->user()->id;
                $approvedAt = now();
            } else {
                $status = QuestionStatus::Pending;
                $approvedBy = null;
                $approvedAt = null;
            }
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|in:text_input,multiple_choice,top_5,text_input_with_image',
            'difficulty' => 'required|in:easy,medium,hard',
            'source_url' => 'required|url|max:500',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'answers' => 'required|array|min:1',
            'answers.*.text' => 'required|string|max:500',
            'answers.*.is_correct' => 'sometimes|boolean',
            'correct_answer_id' => 'sometimes|required_if:question_type,multiple_choice',
        ]);

        // Handle image upload
        $imageUrl = $question->image_url;
        if ($request->hasFile('image')) {
            if ($question->image_url && \Storage::disk('public')->exists(str_replace('storage/', '', $question->image_url))) {
                \Storage::disk('public')->delete(str_replace('storage/', '', $question->image_url));
            }
            $imagePath = $request->file('image')->store('questions', 'public');
            $imageUrl = 'storage/'.$imagePath;
        } elseif ($request->question_type !== 'text_input_with_image') {
            if ($question->image_url && \Storage::disk('public')->exists(str_replace('storage/', '', $question->image_url))) {
                \Storage::disk('public')->delete(str_replace('storage/', '', $question->image_url));
            }
            $imageUrl = null;
        }

        $question->update([
            'category_id' => $request->category_id,
            'question_text' => $request->question_text,
            'image_url' => $imageUrl,
            'question_type' => $request->question_type,
            'difficulty' => $request->difficulty,
            'source_url' => $request->source_url,
            'status' => $status ?? $question->status,
            'approved_by' => $approvedBy ?? $question->approved_by,
            'approved_at' => $approvedAt ?? $question->approved_at,
        ]);

        $question->answers()->delete();

        foreach ($request->answers as $index => $answerData) {
            $isCorrect = match ($request->question_type) {
                'text_input', 'text_input_with_image', 'top_5' => (bool) ($answerData['is_correct'] ?? true),
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

        if ($status == QuestionStatus::Approved) {
            $creator = $question->creator;
            $creator->increment('approved_questions_count');

            if ($creator->approved_questions_count >= 10 && ! $creator->is_pre_validated) {
                $creator->update(['is_pre_validated' => true]);
            }
        }

        if ($status == QuestionStatus::Pending) {
            $admins = User::where('role', UserRole::Admin)->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'question',
                    'title' => 'Η Ερώτηση Ανανεώθηκε. Χρειάζεται Έγκριση',
                    'message' => 'Μια ερώτηση έχει ανανεωθεί από τον/την δημιουργό της και χρειάζεται έγκριση από τον διαχειριστή.',
                    'data' => [
                        'report_id' => $question->id,
                        'question_id' => $question->id,
                        'question_text' => $question->question_text,
                        'reporter_id' => auth()->id(),
                        'is_guest_report' => false,
                    ],
                ]);
            }

            return redirect()->route('questions.index')
                ->with('success', 'Question updated successfully. Waiting for admin approval.');
        }

        return redirect()->route('questions.index')
            ->with('success', 'Question updated successfully');
    }

    public function destroy(Question $question)
    {
        if (! auth()->user()->isAdmin() && $question->created_by !== auth()->id()) {
            abort(403);
        }

        // Prevent deleting questions in active games
        if (! $question->canBeDeleted()) {
            return back()->with('error', 'Αυτή η ερώτηση χρησιμοποιείται σε ενεργά παιχνίδια και δεν μπορεί να διαγραφεί.');
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
