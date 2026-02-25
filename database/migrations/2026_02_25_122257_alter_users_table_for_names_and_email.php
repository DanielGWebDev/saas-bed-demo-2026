<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');  // Replace with given_name & family_name
            $table->string('given_name', 64);
            $table->string('family_name', 64)->nullable();
            $table->string('email', 128)->change();
            $table->string('password', 64)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['given_name', 'family_name']); // Replace with name
            $table->string('name');
            $table->string('email')->change();
            $table->string('password')->change();
        });
    }
};
