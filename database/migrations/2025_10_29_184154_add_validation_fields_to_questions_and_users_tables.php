<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add validation fields to questions table
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('is_active');
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            $table->string('source_url', 500)->after('rejection_reason');
        });

        // Add pre-validation flag to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_pre_validated')->default(false)->after('role');
            $table->integer('approved_questions_count')->default(0)->after('is_pre_validated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status', 'approved_by', 'approved_at', 'rejection_reason', 'source_url']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_pre_validated', 'approved_questions_count']);
        });
    }
};
