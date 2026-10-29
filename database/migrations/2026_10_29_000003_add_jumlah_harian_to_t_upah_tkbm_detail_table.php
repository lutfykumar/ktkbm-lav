<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('t_upah_tkbm_detail') && !Schema::hasColumn('t_upah_tkbm_detail', 'JUMLAH_HARIAN')) {
            Schema::table('t_upah_tkbm_detail', function (Blueprint $table) {
                $table->integer('JUMLAH_HARIAN')->default(0)->after('JUMLAH_GANG');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('t_upah_tkbm_detail') && Schema::hasColumn('t_upah_tkbm_detail', 'JUMLAH_HARIAN')) {
            Schema::table('t_upah_tkbm_detail', function (Blueprint $table) {
                $table->dropColumn('JUMLAH_HARIAN');
            });
        }
    }
};

