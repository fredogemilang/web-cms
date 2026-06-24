<?php

namespace App\Console\Commands;

use App\Models\Domicile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportDomiciles extends Command
{
    protected $signature = 'domicile:import {--force : Overwrite existing database entries}';
    protected $description = 'Import provinces and regencies from wilayah.id API into the local database.';

    public function handle(): int
    {
        $this->info('Starting regional domiciles import from wilayah.id API...');

        try {
            $response = Http::timeout(15)->retry(3, 1000)->get('https://wilayah.id/api/provinces.json');

            if ($response->failed()) {
                $this->error('Failed to fetch provinces from the API.');
                return self::FAILURE;
            }

            $provinces = $response->json()['data'] ?? [];

            if (empty($provinces)) {
                $this->warn('No provinces found in the API response.');
                return self::SUCCESS;
            }

            $this->info('Found ' . count($provinces) . ' provinces. Importing and fetching regencies...');

            $bar = $this->output->createProgressBar(count($provinces));
            $bar->start();

            foreach ($provinces as $prov) {
                // Insert/update province
                Domicile::updateOrCreate(
                    ['code' => $prov['code']],
                    [
                        'name' => $prov['name'],
                        'parent_code' => null,
                        'type' => 'province',
                    ]
                );

                // Fetch regencies under this province
                try {
                    $regResponse = Http::timeout(15)->retry(3, 1000)->get("https://wilayah.id/api/regencies/{$prov['code']}.json");

                    if ($regResponse->successful()) {
                        $regencies = $regResponse->json()['data'] ?? [];

                        foreach ($regencies as $reg) {
                            Domicile::updateOrCreate(
                                ['code' => $reg['code']],
                                [
                                    'name' => $reg['name'],
                                    'parent_code' => $prov['code'],
                                    'type' => 'regency',
                                ]
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    $this->error("\nFailed to fetch regencies for province: " . $prov['name'] . " (" . $e->getMessage() . ")");
                }

                $bar->advance();
            }

            $bar->finish();
            $this->info("\nDomiciles import completed successfully!");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("\nAn error occurred during import: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
