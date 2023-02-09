<?php

namespace Jaulz\Podium;

use Illuminate\Support\Facades\DB;

class Podium
{
  public function getSchema() {
    return 'podium';
  }

  public function rebalance(string $tableSchema, string $tableName, string $targetName, int $bucket) {
    DB::statement(
      sprintf(
        <<<SQL
SELECT podium.rebalance(:tableSchema, :tableName, :targetName, :bucket);
SQL
        ,
        $this->getSchema()
      ), compact('tableSchema', 'tableName', 'targetName', 'bucket')
    );
  }
}