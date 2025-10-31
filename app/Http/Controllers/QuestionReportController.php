<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Notification;
use App\Models\Question;
use App\Models\QuestionReport;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionReportController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Question $question): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $isAuthenticated = auth()->check();
        $reporterName = $isAuthenticated ? auth()->user()->display_name : 'Guest';

        // Create the report
        $report = QuestionReport::create([
            'question_id' => $question->id,
            'user_id' => $isAuthenticated ? auth()->id() : null,
            'guest_identifier' => ! $isAuthenticated ? $request->ip() : null,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Send notifications to admins
        $admins = User::where('role', UserRole::Admin)->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'question_report',
                'title' => 'New Question Report',
                'message' => "A question has been reported by {$reporterName}",
                'data' => [
                    'report_id' => $report->id,
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'reporter_id' => $isAuthenticated ? auth()->id() : null,
                    'is_guest_report' => ! $isAuthenticated,
                ],
            ]);
        }

        // Send notification to question creator only if authenticated user
        if ($isAuthenticated && $question->created_by && $question->created_by !== auth()->id()) {
            Notification::create([
                'user_id' => $question->created_by,
                'type' => 'question_report',
                'title' => 'Your Question Was Reported',
                'message' => 'One of your questions has been reported by a user',
                'data' => [
                    'report_id' => $report->id,
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'reporter_id' => auth()->id(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Question reported successfully',
        ]);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', QuestionReport::class);

        $query = QuestionReport::with(['question', 'user', 'resolver']);

        // Filter by status if provided
        if ($request->has('status') && in_array($request->status, ['pending', 'resolved'])) {
            $query->where('status', $request->status);
        }

        $reports = $query
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.reports.index', compact('reports'));
    }

    public function resolve(Request $request, QuestionReport $report): JsonResponse
    {
        $this->authorize('update', $report);

        $report->update([
            'status' => 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report marked as resolved',
        ]);
    }
}
