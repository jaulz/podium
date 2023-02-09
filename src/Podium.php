<?php

namespace Jaulz\Podium;

use Illuminate\Support\Facades\DB;

class Podium
{
  public function getSchema()
  {
    return 'podium';
  }

  public function grant(string $role)
  {
    collect([
      'GRANT USAGE ON SCHEMA %1$s TO %2$s',
      'GRANT SELECT ON TABLE %1$s.definitions TO %2$s'
    ])->each(fn (string $statement) => DB::statement(sprintf($statement, Podium::getSchema(), $role)));
  }

  public function ungrant(string $role)
  {
  }

  public function rebalance(string $tableSchema, string $tableName, string $targetName, int $bucket)
  {
    DB::statement(
      sprintf(
        <<<SQL
SELECT podium.rebalance(:tableSchema, :tableName, :targetName, :bucket);
SQL,
        $this->getSchema()
      ),
      compact('tableSchema', 'tableName', 'targetName', 'bucket')
    );
  }
}
