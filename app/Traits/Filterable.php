<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Class Filterable
 * @package App\Traits
 */
trait Filterable
{
    /**
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param array|null $input
     */
    public function scopeFilter($query, $input = null)
    {
        $input = $input && is_array($input) ? $input : request()->query();

        foreach ($input as $key => $value) {
            if ($value == ($this->ignoreFilterValue ?? 'all')) {
                continue;
            }

            $method = 'filter'.Str::studly($key);
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $query, $value);
            } elseif ($this->isFilterable($key)) {
                is_array($value) ? $query->whereIn($key, $value) : $query->where($key, $value);
            }
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isFilterable(string $key)
    {
        return property_exists($this, 'filterable') && in_array($key, $this->filterable);
    }

    /**
     * @example
     * <pre>
     *  order_by=id:desc
     *  order_by=age:desc,created_at:asc...
     * </pre>
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $value
     */
    public function filterOrderBy($query, $value)
    {
        $segments = \explode(',', $value);

        foreach ($segments as $segment) {
            list($key, $direction) = array_pad(\explode(':', $segment), 2, 'desc');
            $query->orderBy($key, $direction);
        }
    }
}
