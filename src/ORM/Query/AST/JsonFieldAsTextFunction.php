<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\ORM\Query\AST;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Implements support for the postgres-only "->>" operator on json(b) fields to
 * return a field as text.
 *
 * @todo implement support for "#>>" to return the value at a given path as string
 *
 * @see https://www.postgresql.org/docs/current/functions-json.html#FUNCTIONS-JSON-OP-TABLE
 */
class JsonFieldAsTextFunction extends FunctionNode
{
    private $expr1;
    private $expr2;

    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->expr1 = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->expr2 = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            '(%s->>%s)',
            $this->expr1->dispatch($sqlWalker),
            $this->expr2->dispatch($sqlWalker)
        );
    }
}
