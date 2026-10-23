<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The role a party gives each endpoint we store for it - from OCPI 2.2 its
 * version details list it, SENDER or RECEIVER: we push only to a RECEIVER.
 * 2.1.1 endpoints have none.
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
        Schema::table(config('ocpi.database.table.prefix').'parties', function (Blueprint $table) {
            $table->json('endpoint_roles')->nullable()->after('endpoints')->comment('Role (SENDER / RECEIVER) of each stored endpoint, by module - OCPI 2.2+');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('ocpi.database.table.prefix').'parties', function (Blueprint $table) {
            $table->dropColumn('endpoint_roles');
        });
    }
};
