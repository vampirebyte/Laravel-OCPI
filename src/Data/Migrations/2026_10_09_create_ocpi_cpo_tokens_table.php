<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens eMSPs push to us, the CPO (Tokens receiver interface) - used to
 * authorize their drivers at our charge points.
 */
return new class extends Migration
{
    /**
     * Get the migration connection name.
     */
    public function getConnection(): ?string
    {
        return config('ocpi.database.connection');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('ocpi.database.table.prefix').'cpo_tokens', function (Blueprint $table) {
            $table->id();

            // the eMSP that pushed the token
            $table->foreignId('party_id')
                ->constrained(
                    table: config('ocpi.database.table.prefix').'parties',
                    indexName: 'cpo_tokens_party_id',
                )
                ->cascadeOnDelete();

            // OCPI country code and party id of the eMSP issuing the token
            $table->string('country_code', 2);
            $table->string('party_code', 3);

            $table->string('uid', 36);
            $table->string('type', 20)->comment('RFID, APP_USER, AD_HOC_USER, OTHER');
            $table->string('contract_id', 36)->comment('2.1.1 auth_id');
            $table->string('visual_number', 64)->nullable();
            $table->string('issuer', 64);
            $table->string('group_id', 36)->nullable();
            $table->boolean('valid');
            $table->string('whitelist', 20)->comment('ALWAYS, ALLOWED, ALLOWED_OFFLINE, NEVER');
            $table->string('language', 2)->nullable();
            $table->timestamp('last_updated');

            // the Token as last received
            $table->json('object');

            $table->timestamps();

            $table->unique(['country_code', 'party_code', 'uid', 'type'], 'cpo_tokens_unique');
            $table->index('uid', 'cpo_tokens_uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('ocpi.database.table.prefix').'cpo_tokens');
    }
};
