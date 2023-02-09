<?php

namespace Jaulz\Limax\Traits;

use Illuminate\Database\Eloquent\Builder;

trait IsOrderableTrait
{
  /**
   * Boot the trait.
   */
  public static function bootIsOrderableTrait()
  {
  }

  /**
   * Initialize the trait
   *
   * @return void
   */
  public function initializeIsOrderableTrait()
  {
  }

  /**
   * Scope a query to be ordered.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @param  string  $column
   * @param  string  $direction
   * @return void
   */
  public function scopeOrdered(Builder $query,
  string $column = 'order',
  string $direction = 'asc')
  {
    return $query->orderBy($column, $direction);
  }
}
