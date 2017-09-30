<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscribedUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if (!Schema::hasTable('subscribed_users')) {
            Schema::create('subscribed_users', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('telegram_id')->unsigned();
                $table->string('username', 100);
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->timestamps();
                $table->timestamp('deleted_at')->nullable()->default(null);
                $table->index('deleted_at');

                $table->unique(['id', 'telegram_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscribed_users');
    }
}
