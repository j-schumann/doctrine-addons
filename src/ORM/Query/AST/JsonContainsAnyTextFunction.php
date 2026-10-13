<?php

namespace Vrok\DoctrineAddons\ORM\Query\AST;

/**
 * Implements support for the PostgreSQL-only "?|" operator on jsonb fields to search for any of
 * the given texts.
 *
 * @see https://www.postgresql.org/docs/current/functions-json.html#FUNCTIONS-JSONB-OP-TABLE
 */
class JsonContainsAnyTextFunction extends AbstractJsonContainsTextsFunction
{
    protected const string OPERATOR = '??|';
}
