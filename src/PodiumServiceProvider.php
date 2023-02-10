<?php

namespace Jaulz\Podium;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Support\Fluent;
use Jaulz\Podium\Facades\Podium;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

class PodiumServiceProvider extends PackageServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->extendBlueprint();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('podium')
            ->hasConfigFile('podium')
            ->hasMigration('create_podium_extension')
            ->hasMigration('grant_usage_on_podium_extension')
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishMigrations()
                    ->publishConfigFile()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('jaulz/podium');
            });
    }

    public function extendBlueprint()
    {
      Blueprint::macro('podium', function (
        string $targetName = 'order',
        array $groupBy = [],
        string $defaultRank = 'last',
        string $schema = null
      ) {
        /** @var \Illuminate\Database\Schema\Blueprint $this */
        $prefix = $this->prefix;
        $tableName = $this->table;
        $schema = $schema ?? config('podium.table_schema') ?? 'public';
  
        $command = $this->addCommand(
          'podium',
          compact('prefix', 'tableName', 'groupBy', 'targetName', 'schema', 'defaultRank')
        );
      });
  
      PostgresGrammar::macro('compilePodium', function (
        Blueprint $blueprint,
        Fluent $command
      ) {
        /** @var \Illuminate\Database\Schema\Grammars\PostgresGrammar $this */
        $prefix = $command->prefix;
        $tableName = $command->tableName;
        $schema = $command->schema;
        $groupBy = $command->groupBy;
        $targetName = $command->targetName;
        $defaultRank = $command->defaultRank;
  
        return [
          sprintf(
            <<<SQL
    SELECT %s.create(%s, %s, %s, (SELECT ARRAY(SELECT jsonb_array_elements_text(%s::jsonb))), %s);
  SQL
            ,
            Podium::getSchema(),
            $this->quoteString($schema),
            $this->quoteString($prefix . $tableName),
            $this->quoteString($targetName),
            $this->quoteString(json_encode($groupBy)),
            $this->quoteString($defaultRank),
          ),
        ];
      });
    }
}