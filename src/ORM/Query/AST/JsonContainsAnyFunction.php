<?php

namespace Vrok\DoctrineAddons\ORM\Query\AST;

use Doctrine\ORM\Query\AST\ArithmeticExpression;
use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * Implements support for the PostgreSQL-only "@> ANY" operator on jsonb fields, to search for any
 * of the given JSON values. The second argument must be an array parameter of JSON strings, it is
 * expanded to a PostgreSQL array.
 *
 * @see https://www.postgresql.org/docs/current/functions-comparisons.html#FUNCTIONS-COMPARISONS-ANY-SOME
 * @see https://www.postgresql.org/docs/current/datatype-json.html#JSON-CONTAINMENT
 */
class JsonContainsAnyFunction extends FunctionNode
{
    private ArithmeticExpression $json;
    private ArithmeticExpression $values;

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->json = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->values = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            '(%s @> ANY(ARRAY[%s]::jsonb[]))',
            $this->json->dispatch($sqlWalker),
            $this->values->dispatch($sqlWalker)
        );
    }
}
