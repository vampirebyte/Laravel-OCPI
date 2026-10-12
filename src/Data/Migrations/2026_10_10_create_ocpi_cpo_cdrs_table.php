<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CDRs we, the CPO, send to eMSPs (Cdrs sender interface): stored as sent,
 * so the eMSP can pull them and an unaccepted one can be sent again.
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
        Schema::create(config('ocpi.database.table.prefix').'cpo_cdrs', function (Blueprint $table) {
            $table->id();

            // the eMSP the CDR is for
            $table->foreignId('party_id')
                ->constrained(
                    table: config('ocpi.database.table.prefix').'parties',
                    indexName: 'cpo_cdrs_party_id',
                )
                ->cascadeOnDelete();

            // the OCPI CDR id
            $table->string('cdr_id', 39);

            // what the host application made the CDR from, e.g. its session id
            $table->string('reference')->nullable();

            $table->string('currency', 3);
            $table->decimal('total_cost_excl_vat', 12, 4);
            $table->decimal('total_cost_incl_vat', 12, 4)->nullable();

            // the CDR as sent - it never changes - in the shape of each OCPI version
            $table->json('object_2_1_1');
            $table->json('object_2_2_1');

            // when the eMSP accepted it, null while it still has to be sent
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->unique(['party_id', 'cdr_id'], 'cpo_cdrs_unique');
            $table->index(['party_id', 'updated_at'], 'cpo_cdrs_party_updated');
            $table->index('reference', 'cpo_cdrs_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('ocpi.database.table.prefix').'cpo_cdrs');
    }
};
