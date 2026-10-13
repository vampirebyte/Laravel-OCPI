<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payment terminals of PTPs (payments module, DirectPayment extension): the
 * PTP owns and creates them, we, the CPO, activate them and assign them our
 * locations / EVSEs. A row without terminal_id is our activation request,
 * waiting for the PTP to create the terminal.
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
        Schema::create(config('ocpi.database.table.prefix').'payment_terminals', function (Blueprint $table) {
            $table->id();

            // the PTP the terminal belongs to
            $table->foreignId('party_id')
                ->constrained(
                    table: config('ocpi.database.table.prefix').'parties',
                    indexName: 'payment_terminals_party_id',
                )
                ->cascadeOnDelete();

            // the OCPI terminal id, set by the PTP - null while an activation request waits for it
            $table->string('terminal_id', 36)->nullable();

            // the terminal TID - what an activation request is matched on
            $table->string('serial_number', 36)->nullable();

            // the Terminal object, with the locations / EVSEs assigned to it
            $table->json('object');
            $table->timestamp('last_updated');

            $table->timestamp('activation_requested_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();

            $table->timestamps();

            $table->unique(['party_id', 'terminal_id'], 'payment_terminals_unique');
            $table->index(['party_id', 'serial_number'], 'payment_terminals_serial_number');
            $table->index(['party_id', 'last_updated'], 'payment_terminals_party_last_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('ocpi.database.table.prefix').'payment_terminals');
    }
};
