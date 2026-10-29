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
        // 1. Master Tarif Upah TKBM
        if (!Schema::hasTable('m_tarif_upah')) {
            Schema::create('m_tarif_upah', function (Blueprint $table) {
                $table->id('ID_TARIF');
                $table->unsignedBigInteger('ID_AKTIFITAS')->nullable();
                $table->unsignedBigInteger('ID_KRK')->nullable();
                $table->string('NAMA_TARIF', 150);
                $table->enum('JENIS_TARIF', ['borongan', 'harian', 'toeslag', 'adm_koperasi', 'lembur'])->default('borongan');
                $table->decimal('NILAI_TARIF', 15, 2)->default(0);
                $table->decimal('PERSEN_KAS_KOPERASI', 5, 2)->default(0);
                $table->string('KETERANGAN', 255)->nullable();
                $table->boolean('IS_ACTIVE')->default(true);
                $table->timestamp('CREATED_AT')->useCurrent();
                $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
                $table->softDeletes('DELETED_AT');
            });
        }

        // 2. Tambah kolom JUMLAH_TRUCK di t_work_order_detail
        if (Schema::hasTable('t_work_order_detail') && !Schema::hasColumn('t_work_order_detail', 'JUMLAH_TRUCK')) {
            Schema::table('t_work_order_detail', function (Blueprint $table) {
                $table->integer('JUMLAH_TRUCK')->default(0)->after('HASIL');
            });
        }

        // 3. Header Transaksi Upah & Kwitansi TKBM
        if (!Schema::hasTable('t_upah_tkbm')) {
            Schema::create('t_upah_tkbm', function (Blueprint $table) {
                $table->id('ID_UPAH_TKBM');
                $table->string('NO_DOKUMEN', 50)->unique();
                $table->string('NO_KWITANSI', 100)->nullable();
                $table->unsignedBigInteger('ID_WORK_ORDER')->nullable()->index();
                $table->unsignedBigInteger('ID_CLIENT')->nullable()->index();
                $table->unsignedBigInteger('ID_KAPAL')->nullable()->index();
                $table->unsignedBigInteger('ID_DERMAGA')->nullable()->index();
                $table->string('KOMODITAS', 150)->nullable();
                $table->date('TGL_KEGIATAN_START')->nullable();
                $table->date('TGL_KEGIATAN_END')->nullable();
                $table->integer('TOTAL_TRUCK')->default(0);
                $table->decimal('TOTAL_VOLUME', 15, 3)->default(0);
                $table->decimal('SUBTOTAL_UPAH', 15, 2)->default(0);
                $table->decimal('TOTAL_ADM', 15, 2)->default(0);
                $table->decimal('TOTAL_TOESLAG', 15, 2)->default(0);
                $table->decimal('TOTAL_LEMBUR', 15, 2)->default(0);
                $table->decimal('GRAND_TOTAL', 15, 2)->default(0);
                $table->decimal('ALOKASI_BURUH', 15, 2)->default(0);
                $table->decimal('ALOKASI_KOPERASI', 15, 2)->default(0);
                $table->text('TERBILANG')->nullable();
                $table->string('REKENING_BANK', 255)->nullable()->default('(BCA) 6100091044 / (MANDIRI) 1780068844444 AN. ERNAWATI/THOHIR');
                $table->string('KOORDINATOR_NAMA', 150)->nullable()->default('H. M. THOHIR, SE');
                $table->enum('STATUS', ['draft', 'published', 'paid', 'cancelled'])->default('draft')->index();
                $table->date('TGL_BAYAR')->nullable();
                $table->string('BUKTI_BAYAR', 255)->nullable();
                $table->text('CATATAN')->nullable();
                $table->unsignedBigInteger('REF_INVOICE_ID')->nullable()->index();
                $table->unsignedBigInteger('CREATED_BY')->nullable();
                $table->unsignedBigInteger('UPDATED_BY')->nullable();
                $table->timestamp('CREATED_AT')->useCurrent();
                $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
                $table->softDeletes('DELETED_AT');
            });
        }

        // 4. Rincian Shift & Gang Harian Upah TKBM
        if (!Schema::hasTable('t_upah_tkbm_detail')) {
            Schema::create('t_upah_tkbm_detail', function (Blueprint $table) {
                $table->id('ID_UPAH_DETAIL');
                $table->unsignedBigInteger('ID_UPAH_TKBM')->index();
                $table->date('TGL_KERJA');
                $table->string('JAM_KERJA', 30)->nullable();
                $table->integer('JUMLAH_GANG')->default(1);
                $table->integer('JUMLAH_TRUCK')->default(0);
                $table->decimal('VOLUME_BORONGAN', 15, 3)->default(0);
                $table->decimal('UPAH_NOMINAL', 15, 2)->default(0);
                $table->decimal('ADM_NOMINAL', 15, 2)->default(0);
                $table->decimal('TOESLAG_NOMINAL', 15, 2)->default(0);
                $table->decimal('LEMBUR_NOMINAL', 15, 2)->default(0);
                $table->decimal('JUMLAH_UPAH', 15, 2)->default(0);
                $table->string('KETERANGAN', 255)->nullable();
                $table->timestamp('CREATED_AT')->useCurrent();
                $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
                $table->softDeletes('DELETED_AT');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_upah_tkbm_detail');
        Schema::dropIfExists('t_upah_tkbm');
        if (Schema::hasTable('t_work_order_detail') && Schema::hasColumn('t_work_order_detail', 'JUMLAH_TRUCK')) {
            Schema::table('t_work_order_detail', function (Blueprint $table) {
                $table->dropColumn('JUMLAH_TRUCK');
            });
        }
        Schema::dropIfExists('m_tarif_upah');
    }
};

