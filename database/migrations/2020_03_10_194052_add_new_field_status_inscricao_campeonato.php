<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldStatusInscricaoCampeonato extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campeonato_usuarios', function (Blueprint $table) {
            $table->enum('status_inscricao',['confirmada','pagamento_pendente','aprovacao_pendente'])->default('confirmada')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
