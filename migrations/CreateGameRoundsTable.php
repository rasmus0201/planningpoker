<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder as Schema;

class CreateGameRoundsTable extends Migration
{
    private Schema $schema;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('game_rounds');
    }
}
