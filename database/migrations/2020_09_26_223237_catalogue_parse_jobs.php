<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CatalogueParseJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogue_parse_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('jobId');
            $table->text('status',50);
            $table->text('message')->nullable(true);
            $table->timestamp('done')->useCurrent();
        });
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
