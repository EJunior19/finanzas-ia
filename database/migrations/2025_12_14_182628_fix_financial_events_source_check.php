<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE financial_events
            DROP CONSTRAINT IF EXISTS financial_events_source_check
        ");

        DB::statement("
            ALTER TABLE financial_events
            ADD CONSTRAINT financial_events_source_check
            CHECK (source IN ('ai', 'manual'))
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE financial_events
            DROP CONSTRAINT IF EXISTS financial_events_source_check
        ");

        DB::statement("
            ALTER TABLE financial_events
            ADD CONSTRAINT financial_events_source_check
            CHECK (source IN ('ai'))
        ");
    }
};

