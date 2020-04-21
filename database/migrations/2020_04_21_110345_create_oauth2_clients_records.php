<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauth2ClientsRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $clientId = 'p2id';
        $clientSecret = 'secret';
        $clientName = 'player2App';
        $redirect_uri = 'http://localhost:3000';

        // DB::table('oauth_clients')->insert([
        //     'id' => $clientId,
        //     'secret' => $clientSecret,
        //     'name' => $clientName
        // ]);

        DB::table('oauth_client_endpoints')->insert([
            'client_id' => $clientId,
            'redirect_uri' => $redirect_uri
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
