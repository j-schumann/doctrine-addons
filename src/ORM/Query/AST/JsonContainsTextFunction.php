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
 * Implements support for the PostgreSQL-only "?" operator on jsonb fields to search for text.
 *
 * @see https://www.postgresql.org/docs/current/functions-json.html#FUNCTIONS-JSONB-OP-TABLE
 */
class JsonContainsTextFunction extends FunctionNode
{
    private ArithmeticExpression $json;
    private ArithmeticExpression $text;

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->json = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->text = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            // double ? to escape it, else interpreted as param placeholder
            '(%s ?? %s)',
            $this->json->dispatch($sqlWalker),
            $this->text->dispatch($sqlWalker)
        );
    }
}
