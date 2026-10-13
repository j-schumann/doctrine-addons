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
 * Shared implementation of the PostgreSQL-only "?|" and "?&" operators on jsonb fields. The second
 * argument must be an array parameter, it is expanded to a PostgreSQL array.
 */
abstract class AbstractJsonContainsTextsFunction extends FunctionNode
{
    /**
     * The doubled ? escapes it, else it is interpreted as a parameter placeholder.
     */
    protected const string OPERATOR = '';

    private ArithmeticExpression $json;
    private ArithmeticExpression $texts;

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->json = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->texts = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            '(%s %s ARRAY[%s]::text[])',
            $this->json->dispatch($sqlWalker),
            static::OPERATOR,
            $this->texts->dispatch($sqlWalker)
        );
    }
}
