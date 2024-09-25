<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\ORM\Query\AST;

use Doctrine\ORM\Query\AST\ArithmeticExpression;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * @see https://gist.github.com/galiazzi/5e5f04f9753ba4d8a9b972c87dc2a805
 *
 * @author Dayan C. Galiazzi - https://gist.github.com/galiazzi
 */
class CastFunction extends FunctionNode
{
    private ArithmeticExpression $expr1;
    private Node $expr2;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->expr1 = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->expr2 = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $type = trim($this->expr2->dispatch($sqlWalker), "'");

        return sprintf(
            'CAST(%s as %s)',
            $this->expr1->dispatch($sqlWalker),
            $type
        );
    }
}
