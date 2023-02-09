<?php

namespace Jaulz\Podium;

use Illuminate\Support\Facades\DB;

class Podium
{
  public static string $schema = 'podium';

  public function rebalance(string $tableSchema, string $tableName, string $targetName, int $bucket) {
    DB::statement(
      sprintf(
        <<<SQL
SELECT podium.rebalance(:tableSchema, :tableName, :targetName, :bucket);
SQL
        ,
        Podium::$schema
      ), compact('tableSchema', 'tableName', 'targetName', 'bucket')
    );
  }
}