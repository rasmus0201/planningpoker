<?php

use App\Models\{GameParticipant, GameRound};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(GameRound::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(GameParticipant::class)->constrained()->cascadeOnDelete();
            $table->string('vote');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['game_round_id', 'game_participant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_votes');
    }
};
