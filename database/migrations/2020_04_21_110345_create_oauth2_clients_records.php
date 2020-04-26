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
        $clientes = array(
            'player2App' => array(
                'id' => 'p2id',
                'secret' => 'secret',
            ),
            'facebook' => array(
                'id' => '3148215308557100',
                'secret' => Config::get('app.facebook_secret'),
                'redirect_uri' => 'http://localhost:3000/'
            ),
            'google' => array(
                'id' => '687694969410-1tvfs14oljk1vtcdantrjod1gsvfkebe.apps.googleusercontent.com',
                'secret' => Config::get('app.google_secret'),
                'redirect_uri' => 'http://localhost:3000/'
            ),
            'live' => array(
                'id' => '245c979b-5458-449a-afde-c00b4ec17be3',
                'secret' => Config::get('app.live_secret'),
                'redirect_uri' => 'http://localhost:3000/'
            )
        );

        foreach ($clientes as $key => $client) {
            $existeClient =  DB::table('oauth_clients')->where('id', '=', $client['id'])->first();
            if(!isset($existeClient)) {
                DB::table('oauth_clients')->insert([
                    'id' => $client['id'],
                    'secret' => $client['secret'],
                    'name' => $key
                ]);
            }

            $existeEndpoint =  DB::table('oauth_client_endpoints')->where('client_id', '=', $client['id'])->first();
            if(isset($client['redirect_uri']) && !isset($existeEndpoint)) {
                DB::table('oauth_client_endpoints')->insert([
                    'client_id' => $client['id'],
                    'redirect_uri' => $client['redirect_uri']
                ]);
            }
        }

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
