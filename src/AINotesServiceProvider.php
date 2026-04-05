<?php

namespace Nahid\AINotesPackage;

use Illuminate\Support\ServiceProvider;
use Nahid\AINotesPackage\Actions\GenerateEmbedding;
use Nahid\AINotesPackage\Actions\SearchNotes;
use Nahid\AINotesPackage\Contracts\AIProvider;
use Nahid\AINotesPackage\Drivers\OllamaProvider;
use Nahid\AINotesPackage\Drivers\OpenAIProvider;

class AINotesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ai-notes.php', 'ai-notes');

        $this->app->bind(AIProvider::class, function () {
            return match (config('ai-notes.driver', 'ollama')) {
                'openai' => new OpenAIProvider(),
                'ollama' => new OllamaProvider(),
                default  => throw new \InvalidArgumentException(
                    'Unknown AI driver: ' . config('ai-notes.driver')
                ),
            };
        });

        $this->app->bind(AINotesManager::class, function ($app) {
            $provider = $app->make(AIProvider::class);
            return new AINotesManager(
                $provider,
                new SearchNotes(new GenerateEmbedding($provider))
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ai-notes.php' => config_path('ai-notes.php'),
            ], 'ai-notes-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'ai-notes-migrations');

            $this->commands([Console\InstallCommand::class]);
        }
    }
}