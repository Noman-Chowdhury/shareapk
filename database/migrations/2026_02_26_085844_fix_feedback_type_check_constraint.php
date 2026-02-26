<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old check constraint that only allowed Bug, Suggestion, Other
        DB::statement('ALTER TABLE feedback DROP CONSTRAINT IF EXISTS feedback_type_check');

        // Add new constraint that also allows Feature and Improvement
        DB::statement("ALTER TABLE feedback ADD CONSTRAINT feedback_type_check CHECK (type IN ('Bug', 'Feature', 'Improvement', 'Suggestion', 'Other'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE feedback DROP CONSTRAINT IF EXISTS feedback_type_check');
        DB::statement("ALTER TABLE feedback ADD CONSTRAINT feedback_type_check CHECK (type IN ('Bug', 'Suggestion', 'Other'))");
    }
};
