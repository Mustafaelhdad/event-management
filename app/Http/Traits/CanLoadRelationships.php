<?php

namespace App\Http\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Model;

trait CanLoadRelationships
{
  public function loadRelationships(
    Model|EloquentBuilder|QueryBuilder $for,
    ?array $relations = null
  ) {
    $relations = $relations ?? $this->relations ?? [];

    if ($for instanceof Model) {
      // If it's a single model, use `load()`
      $for->load(array_filter($relations, fn($relation) => $this->shouldIncludeRelation($relation)));
    } elseif ($for instanceof EloquentBuilder) {
      // If it's an Eloquent Builder, use `with()`
      foreach ($relations as $relation) {
        if ($this->shouldIncludeRelation($relation)) {
          $for->with($relation);
        }
      }
    }

    return $for;
  }

  protected function shouldIncludeRelation(string $relation): bool
  {
    $include = request()->query('include');

    if (!$include) {
      return false;
    }

    $relations = array_map('trim', explode(',', $include));

    return in_array($relation, $relations);
  }
}
