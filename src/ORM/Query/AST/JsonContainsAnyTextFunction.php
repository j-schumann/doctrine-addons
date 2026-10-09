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
 * Implements support for the postgres-only "?|" operator on jsonb fields to
 * search for any of the given texts. The second argument must be an array
 * parameter, it is expanded to a Postgres array.
 *
 * @see https://www.postgresql.org/docs/current/functions-json.html#FUNCTIONS-JSONB-OP-TABLE
 */
class JsonContainsAnyTextFunction extends FunctionNode
{
    protected const string OPERATOR = '??|';

    private ArithmeticExpression $expr1;
    private ArithmeticExpression $expr2;

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->expr1 = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->expr2 = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            // double ? to escape it, else interpreted as param placeholder
            '(%s %s ARRAY[%s]::text[])',
            $this->expr1->dispatch($sqlWalker),
            static::OPERATOR,
            $this->expr2->dispatch($sqlWalker)
        );
    }
}
