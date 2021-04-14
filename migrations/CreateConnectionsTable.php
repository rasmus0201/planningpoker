<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder as Schema;

class CreateConnectionsTable extends Migration
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
        $this->schema->create('connections', function (Blueprint $table) {
            $table->integer('id')->primary()->comment('This is the connection\'s resource_id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('game_id')->unsigned()->nullable();
            $table->string('client_id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('connections');
    }
}
