<?php

namespace Nuwave\Lighthouse\Support\Traits;

use Nuwave\Lighthouse\Schema\Values\ArgumentValue;
use Nuwave\Lighthouse\Support\Database\QueryFilter;

trait HandlesQueryFilter
{
    /**
     * Inject query filter into field.
     *
     * @param ArgumentValue $argument
     * @param array         $filter
     *
     * @return ArgumentValue
     */
    protected function injectFilter(ArgumentValue $argument, array $filter)
    {
        $argName = $argument->getArgName();
        $query = QueryFilter::getInstance($argument);

        // Here we create a composed key made up of argument name and key.
        $key = $argName.'.'.$this->queryFilterKey($argument);

        $query->setFilter($key, array_merge([
            'key' => $argName,
        ], $filter));

        $argument->getField()->injectArg(
            'query.filter',
            $query
        );

        return $argument;
    }

    /**
     * Inject query filter into field.
     *
     * @param ArgumentValue $argument
     * @param array         $filter
     *
     * @return ArgumentValue
     */
    protected function injectKeyedFilter(ArgumentValue $argument, array $filter)
    {
        $key = $this->queryFilterKey($argument);
        $query = QueryFilter::getInstance($argument);
        $matchedFilter = $query->getFilter($key, ['resolveArgs' => []]);
        $matchedFilter['resolveArgs'] = array_merge(
            $matchedFilter['resolveArgs'],
            [$argument->getArgName()]
        );

        $query->setFilter($key, array_merge($matchedFilter, $filter));

        $argument->getField()->injectArg(
            'query.filter',
            $query
        );

        return $argument;
    }

    /**
     * Get key for query filter.
     *
     * @param ArgumentValue $argument
     *
     * @return string
     */
    protected function queryFilterKey(ArgumentValue $argument)
    {
        return $this->directiveArgValue(
            'key',
            $argument->getArgName()
        );
    }

    /**
     * Get directive for query filter.
     *
     * @param ArgumentValue $argument
     *
     * @return mixed
     * @deprecated
     */
    protected function queryFilterDirective(ArgumentValue $argument)
    {
        return collect($argument->getArg()->directives)->first(function ($arg) {
            return $arg->name->value == $this->name();
        });
    }
}
