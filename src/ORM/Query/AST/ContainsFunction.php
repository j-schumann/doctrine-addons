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
 * Implements support for the PostgreSQL-only "@>" operator on jsonb fields or arrays, to search for
 * elements.
 *
 * @see https://www.postgresql.org/docs/current/functions-array.html
 * @see https://www.postgresql.org/docs/current/datatype-json.html#JSON-CONTAINMENT
 */
class ContainsFunction extends FunctionNode
{
    private ArithmeticExpression $haystack;
    private ArithmeticExpression $needle;

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->haystack = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->needle = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            '(%s @> %s)',
            $this->haystack->dispatch($sqlWalker),
            $this->needle->dispatch($sqlWalker)
        );
    }
}
