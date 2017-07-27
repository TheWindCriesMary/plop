<?php

/**
 * Bootstrap for tests.
 */

namespace PhpMyAdmin\SqlParser\Tests;

use PhpMyAdmin\SqlParser\Lexer;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\TokensList;

$GLOBALS['lang'] = 'en';

/**
 * Implements useful methods for testing.
 *
 * @category   Tests
 *
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets the token list generated by lexing this query.
     *
     * @param string $query the query to be lexed
     *
     * @return TokensList
     */
    public function getTokensList($query)
    {
        $lexer = new Lexer($query);

        return $lexer->list;
    }

    /**
     * Gets the errors as an array.
     *
     * @param Lexer|Parser $obj object containing the errors
     *
     * @return array
     */
    public function getErrorsAsArray($obj)
    {
        $ret = array();
        foreach ($obj->errors as $err) {
            $ret[] = $obj instanceof Lexer
                ? array($err->getMessage(), $err->ch, $err->pos, $err->getCode())
                : array($err->getMessage(), $err->token, $err->getCode());
        }

        return $ret;
    }

    /**
     * Gets test's input and expected output.
     *
     * @param string $name the name of the test
     *
     * @return array
     */
    public function getData($name)
    {
        /*
         * The unrestricted unserialize() is needed here as we do have
         * serialized objects in the tests. There should be no security risk as
         * the test data comes with the repository.
         */
        $data = unserialize(file_get_contents('tests/data/' . $name . '.out'));
        $data['query'] = file_get_contents('tests/data/' . $name . '.in');

        return $data;
    }

    /**
     * Runs a test.
     *
     * @param string $name the name of the test
     */
    public function runParserTest($name)
    {
        /**
         * Test's data.
         *
         * @var array
         */
        $data = $this->getData($name);

        // Lexer.
        $lexer = new Lexer($data['query']);
        $lexerErrors = $this->getErrorsAsArray($lexer);
        $lexer->errors = array();

        // Parser.
        $parser = empty($data['parser']) ? null : new Parser($lexer->list);
        $parserErrors = array();
        if ($parser !== null) {
            $parserErrors = $this->getErrorsAsArray($parser);
            $parser->errors = array();
        }

        // Testing objects.
        $this->assertEquals($data['lexer'], $lexer);
        $this->assertEquals($data['parser'], $parser);

        // Testing errors.
        $this->assertEquals($data['errors']['parser'], $parserErrors);
        $this->assertEquals($data['errors']['lexer'], $lexerErrors);
    }
}
