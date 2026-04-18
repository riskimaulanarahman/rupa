<?php

namespace App\Console\Commands;

use App\Support\Branding\BrandIconGenerator;
use Illuminate\Console\Command;
use Throwable;

class GenerateBrandIcons extends Command
{
    protected $signature = 'branding:generate-icons
        {--source=auto : Source priority: auto, logo, or favicon}
        {--force : Overwrite existing icon files}';

    protected $description = 'Generate favicon and web app icons from the existing brand logo.';

    public function __construct(private readonly BrandIconGenerator $iconGenerator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $source = (string) $this->option('source');
        $force = (bool) $this->option('force');

        try {
            $result = $this->iconGenerator->generate($source, $force);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Brand icons generated successfully.');
        $this->line('Source: '.$result['source']);
        $this->newLine();

        $rows = [];
        foreach ($result['generated'] as $relativePath) {
            $rows[] = [$relativePath];
        }

        $this->table(['Generated'], $rows);

        return self::SUCCESS;
    }
}
