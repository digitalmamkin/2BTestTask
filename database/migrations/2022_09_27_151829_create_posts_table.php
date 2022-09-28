<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blog_id')->nullable()->default(null);
            $table->foreign('blog_id')->references('id')->on('blogs');
            $table->string('url');
            $table->string('title')->nullable()->default(null);
            $table->longText('content')->nullable()->default(null);
            $table->integer('read_time')->nullable()->default(null);
            $table->date('post_date')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
