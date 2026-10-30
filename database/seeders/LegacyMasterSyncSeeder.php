<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PDO;

class LegacyMasterSyncSeeder extends Seeder
{
    /**
     * Run the database seeds to migrate missing master data from legacy ktkbm DB.
     */
    public function run(): void
    {
        $legacyDb = env('DB_LEGACY_DATABASE', 'ktkbm');
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $username = config('database.connections.mysql.username', 'ktkbm');
        $password = config('database.connections.mysql.password', '');

        $this->command->info("Connecting to legacy database '{$legacyDb}' on {$host}:{$port}...");

        try {
            $pdoLegacy = new PDO(
                "mysql:host={$host};port={$port};dbname={$legacyDb};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (\Exception $e) {
            $this->command->error("Failed to connect to legacy database: " . $e->getMessage());
            return;
        }

        // Ordered by foreign key dependency
        $masterTables = [
            'm_pelabuhan' => 'ID_PELABUHAN',
            'm_satuan'    => 'ID_SATUAN',
            'm_dermaga'   => 'ID_DERMAGA',
            'm_kapal'     => 'ID_KAPAL',
            'm_alat'      => 'ID_ALAT',
            'm_catatan'   => 'ID_CATATAN',
            'm_krk'       => 'ID_KRK',
            'm_client'    => 'ID_CLIENT',
            'm_aktifitas' => 'ID_AKTIFITAS',
        ];

        DB::transaction(function () use ($pdoLegacy, $masterTables) {
            $summary = [];

            foreach ($masterTables as $table => $primaryKey) {
                // Get existing IDs in current Laravel database
                $existingIds = DB::table($table)->pluck($primaryKey)->all();

                // Fetch all rows from legacy database
                $stmt = $pdoLegacy->query("SELECT * FROM `{$table}` ORDER BY `{$primaryKey}` ASC");
                $legacyRows = $stmt->fetchAll();

                $toInsert = [];
                foreach ($legacyRows as $row) {
                    $id = $row[$primaryKey];
                    if (!in_array($id, $existingIds)) {
                        $toInsert[] = $row;
                    }
                }

                if (!empty($toInsert)) {
                    // Chunk insert to avoid payload limits
                    foreach (array_chunk($toInsert, 50) as $chunk) {
                        DB::table($table)->insert($chunk);
                    }
                    $summary[$table] = count($toInsert);
                    $this->command->info("✓ Table {$table}: Inserted " . count($toInsert) . " new records.");
                } else {
                    $summary[$table] = 0;
                    $this->command->line("- Table {$table}: All records up-to-date (0 inserted).");
                }
            }

            $this->command->newLine();
            $this->command->info("Master Data Sync Completed Successfully!");
            $this->command->table(
                ['Table', 'New Records Inserted'],
                collect($summary)->map(fn ($count, $tbl) => [$tbl, $count])->toArray()
            );
        });
    }
}

