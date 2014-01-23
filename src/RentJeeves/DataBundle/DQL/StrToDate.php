<?php
namespace RentJeeves\DataBundle\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class StrToDate extends FunctionNode
{
    public $dateString = null;
    public $dateFormat = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->dateString = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->dateFormat = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'STR_TO_DATE(' .
            $sqlWalker->walkStringPrimary($this->dateString) . ', ' .
            $sqlWalker->walkStringPrimary($this->dateFormat) .
        ')';
    }
}
