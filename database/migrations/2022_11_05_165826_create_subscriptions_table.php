<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->string('url');

            $table->timestamps();
            $table->date('last_entry')->nullable()->default(null);
        });

        Schema::table('digests', function (Blueprint $table) {
            $table->integer('subscription_id')->unsigned()->index()->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('subscriptions');

        Schema::table('digests', function (Blueprint $table) {
            $table->dropColumn('subscription_id');
        });
    }
};
