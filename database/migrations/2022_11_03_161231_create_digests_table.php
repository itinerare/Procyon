<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('digests', function (Blueprint $table) {
            $table->id();

            // The name and URL of the feed.
            $table->string('name');
            $table->string('url');

            // Generated digest content.
            $table->longText('text');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('digests');
    }
};
