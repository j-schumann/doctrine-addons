<?php

namespace Vrok\DoctrineAddons\ORM\Query\AST;

use Doctrine\ORM\Query\AST\ArithmeticExpression;
use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * @see https://gist.github.com/galiazzi/5e5f04f9753ba4d8a9b972c87dc2a805
 *
 * @author Dayan C. Galiazzi - https://gist.github.com/galiazzi
 */
class CastFunction extends FunctionNode
{
    private ArithmeticExpression $expression;
    private Node $type;

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->expression = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->type = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            'CAST(%s as %s)',
            $this->expression->dispatch($sqlWalker),
            trim($this->type->dispatch($sqlWalker), "'")
        );
    }
}
