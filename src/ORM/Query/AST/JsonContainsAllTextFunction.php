<?php

namespace Vrok\DoctrineAddons\ORM\Query\AST;

/**
 * Implements support for the postgres-only "?&" operator on jsonb fields to
 * search for all of the given texts. The second argument must be an array
 * parameter, it is expanded to a Postgres array.
 *
 * @see https://www.postgresql.org/docs/current/functions-json.html#FUNCTIONS-JSONB-OP-TABLE
 */
class JsonContainsAllTextFunction extends JsonContainsAnyTextFunction
{
    protected const string OPERATOR = '??&';
}
