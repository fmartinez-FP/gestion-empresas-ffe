<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 100)->unique()->nullable()->after('id');
        });

        // Generar username para usuarios existentes basado en email
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $username = explode('@', $user->email)[0];
            // Asegurar que sea único
            $baseUsername = $username;
            $counter = 1;
            while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        // Ahora hacer el campo obligatorio
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 100)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
