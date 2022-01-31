<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\ORM\Query\AST;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * @see https://gist.github.com/galiazzi/5e5f04f9753ba4d8a9b972c87dc2a805
 *
 * @author Dayan C. Galiazzi - https://gist.github.com/galiazzi
 */
class CastFunction extends FunctionNode
{
    private $expr1;
    private $expr2;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->expr1 = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->expr2 = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        $type = trim($this->expr2->dispatch($sqlWalker), "'");

        return sprintf(
            'CAST(%s as %s)',
            $this->expr1->dispatch($sqlWalker),
            $type
        );
    }
}
