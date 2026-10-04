<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('business_address')->nullable()->after('phone');
            $table->string('business_bank_name', 100)->nullable()->after('business_address');
            $table->string('business_bank_account', 100)->nullable()->after('business_bank_name');
            $table->string('business_bank_holder')->nullable()->after('business_bank_account');
            $table->timestamp('onboarding_completed_at')->nullable()->after('privacy_version');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'business_address',
                'business_bank_name',
                'business_bank_account',
                'business_bank_holder',
                'onboarding_completed_at',
            ]);
        });
    }
};
