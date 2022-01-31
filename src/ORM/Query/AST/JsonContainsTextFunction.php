<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\ORM\Query\AST;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Implements support for the postgres-only "?" operator on jsonb fields to
 * search for text.
 *
 * @see https://www.postgresql.org/docs/current/functions-json.html#FUNCTIONS-JSONB-OP-TABLE
 *
 * "?" operator is not included in martin-georgiev/postgresql-for-doctrine or
 *  opsway/doctrine-dbal-postgresql so we implement it ourselves.
 */
class JsonContainsTextFunction extends FunctionNode
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
            // double ? to escape it, else interpreted as param placeholder
            '(%s ?? %s)',
            $this->expr1->dispatch($sqlWalker),
            $this->expr2->dispatch($sqlWalker)
        );
    }
}
