<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // La table est déjà créée par la migration par défaut de Laravel (create_users_table).
        // On ne la crée que si elle n'existe pas, pour ne pas faire échouer le déploiement.
        if (Schema::hasTable('password_reset_tokens')) {
            return;
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        // Rien à faire : la table appartient à la migration par défaut de Laravel,
        // la supprimer ici casserait la réinitialisation du mot de passe en cas de rollback.
    }
};