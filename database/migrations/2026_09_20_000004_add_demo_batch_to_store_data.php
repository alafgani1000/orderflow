<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('demo_batch_id')->nullable()->after('notes')->index();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('demo_batch_id')->nullable()->after('tracking_token')->index();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('demo_batch_id')->nullable()->after('notes')->index();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('demo_batch_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('demo_batch_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('demo_batch_id');
        });
    }
};
