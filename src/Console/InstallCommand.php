<?php

namespace Nahid\AINotesPackage\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature   = 'ai-notes:install';
    protected $description = 'Install laravel-ai-notes';

    public function handle(): void
    {
        $this->info('Installing laravel-ai-notes...');

        $this->call('vendor:publish', ['--tag' => 'ai-notes-config',     '--force' => false]);
        $this->call('vendor:publish', ['--tag' => 'ai-notes-migrations', '--force' => false]);

        $this->newLine();
        $this->info('✅ Done! Next steps:');
        $this->line('  1. php artisan migrate');
        $this->line('  2. Set AI_DRIVER=ollama in .env');
        $this->line('  3. ollama pull llama3.2 && ollama pull nomic-embed-text');
        $this->line('  4. php artisan queue:work');
        $this->newLine();
    }
}