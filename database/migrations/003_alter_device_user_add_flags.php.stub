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
        Schema::table('device_user', function (Blueprint $table) {
            $table->timestamp('reported_as_rogue_at')->nullable()->index();
            $table->text('note')->nullable();
            $table->text('admin_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_user', function (Blueprint $table) {
            $table->dropColumn(['reported_as_rogue_at', 'note','admin_note']);
        });
    }
};
