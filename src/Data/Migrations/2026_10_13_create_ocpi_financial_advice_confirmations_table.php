<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Financial advice confirmations PTPs send us (payments module, DirectPayment
 * extension): what was captured on the card for a session, found by its
 * authorization_reference, with the EFT data the invoice needs.
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
        Schema::create(config('ocpi.database.table.prefix').'financial_advice_confirmations', function (Blueprint $table) {
            $table->id();

            // the PTP that sent it
            $table->foreignId('party_id')
                ->constrained(
                    table: config('ocpi.database.table.prefix').'parties',
                    indexName: 'financial_advice_confirmations_party_id',
                )
                ->cascadeOnDelete();

            // the OCPI financial advice confirmation id
            $table->string('confirmation_id', 36);

            // the reference the PTP gave the session in START_SESSION
            $table->string('authorization_reference', 36);

            $table->string('currency', 3);
            $table->decimal('total_cost_excl_vat', 12, 4);
            $table->decimal('total_cost_incl_vat', 12, 4)->nullable();

            $table->string('capture_status_code', 20)->comment('SUCCESS, PARTIAL_SUCCESS, FAILED');
            $table->string('capture_status_message')->nullable();

            // the payment lines for the invoice, e.g. card brand, masked number, amount
            $table->json('eft_data')->nullable();

            $table->timestamp('last_updated');
            $table->timestamps();

            $table->unique(['party_id', 'confirmation_id'], 'financial_advice_confirmations_unique');
            $table->index('authorization_reference', 'financial_advice_confirmations_authorization_reference');
            $table->index(['party_id', 'last_updated'], 'financial_advice_confirmations_party_last_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('ocpi.database.table.prefix').'financial_advice_confirmations');
    }
};
