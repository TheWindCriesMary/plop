<?php
/**
 * Tests for DbSearch.php
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */

require_once 'libraries/database_interface.inc.php';
require_once 'test/PMATestCase.php';

use PMA\libraries\DbSearch;
use PMA\libraries\Theme;

/**
 * Tests for database search.
 *
 * @package PhpMyAdmin-test
 */
class DbSearchTest extends PMATestCase
{
    /**
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     * @return void
     */
    protected function setUp()
    {
        $this->object = new DbSearch('pma_test');
        $GLOBALS['server'] = 0;
        $GLOBALS['db'] = 'pma';
        $GLOBALS['collation_connection'] = 'utf-8';

        //mock DBI
        $dbi = $this->getMockBuilder('PMA\libraries\DatabaseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $dbi->expects($this->any())
            ->method('getColumns')
            ->with('pma', 'table1')
            ->will($this->returnValue(array(
                array('Field' => 'column1'),
                array('Field' => 'column2'),
            )));

        $dbi->expects($this->any())
            ->method('escapeString')
            ->will($this->returnArgument(0));

        $GLOBALS['dbi'] = $dbi;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     * @return void
     */
    protected function tearDown()
    {
        unset($this->object);
    }

    /**
     * Call protected functions by setting visibility to public.
     *
     * @param string $name   method name
     * @param array  $params parameters for the invocation
     *
     * @return the output from the protected method.
     */
    private function _callProtectedFunction($name, $params)
    {
        $class = new ReflectionClass('PMA\libraries\DbSearch');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($this->object, $params);
    }

    /**
     * Test for generating where clause for different search types
     *
     * @dataProvider searchTypes
     */
    public function testGetWhereClause($type, $expected)
    {
        $_REQUEST['criteriaSearchType'] = $type;
        $_REQUEST['criteriaSearchString'] = 'search string';

        $this->object = new DbSearch('pma_test');
        $this->assertEquals(
            $expected,
            $this->_callProtectedFunction(
                '_getWhereClause',
                array('table1')
            )
        );
    }

    /**
     * Data provider for testGetWhereClause
     *
     * @return array
     */
    public function searchTypes()
    {
        return array(
            array(
                '1',
                " WHERE (CONVERT(`column1` USING utf8) LIKE '%search%' OR CONVERT(`column2` USING utf8) LIKE '%search%')  OR  (CONVERT(`column1` USING utf8) LIKE '%string%' OR CONVERT(`column2` USING utf8) LIKE '%string%')"
            ),
            array(
                '2',
                " WHERE (CONVERT(`column1` USING utf8) LIKE '%search%' OR CONVERT(`column2` USING utf8) LIKE '%search%')  AND  (CONVERT(`column1` USING utf8) LIKE '%string%' OR CONVERT(`column2` USING utf8) LIKE '%string%')"
            ),
            array(
                '3',
                " WHERE (CONVERT(`column1` USING utf8) LIKE '%search string%' OR CONVERT(`column2` USING utf8) LIKE '%search string%')"
            ),
            array(
                '4',
                " WHERE (CONVERT(`column1` USING utf8) LIKE 'search string' OR CONVERT(`column2` USING utf8) LIKE 'search string')"
            ),
            array(
                '5',
                " WHERE (CONVERT(`column1` USING utf8) REGEXP 'search string' OR CONVERT(`column2` USING utf8) REGEXP 'search string')"
            ),
        );
    }

    /**
     * Test for _getSearchSqls
     *
     * @return void
     */
    public function testGetSearchSqls()
    {
        $this->assertEquals(
            array (
                'select_columns' => 'SELECT *  FROM `pma`.`table1` WHERE FALSE',
                'select_count' => 'SELECT COUNT(*) AS `count` FROM `pma`.`table1` ' .
                    'WHERE FALSE',
                'delete' => 'DELETE FROM `pma`.`table1` WHERE FALSE'
            ),
            $this->_callProtectedFunction(
                '_getSearchSqls',
                array('table1')
            )
        );
    }

    /**
     * Test for getSearchResults
     *
     * @return void
     */
    public function testGetSearchResults()
    {
        $this->assertEquals(
            '<br /><table class="data"><caption class="tblHeaders">Search results '
            . 'for "<i></i>" :</caption></table>',
            $this->object->getSearchResults()
        );
    }

    /**
     * Test for _getResultsRow
     *
     * @param string $each_table    Tables on which search is to be performed
     * @param array  $newsearchsqls Contains SQL queries
     * @param string $output        Expected HTML output
     *
     * @return void
     *
     * @dataProvider providerForTestGetResultsRow
     */
    public function testGetResultsRow(
        $each_table, $newsearchsqls, $output
    ) {

        $this->assertEquals(
            $output,
            $this->_callProtectedFunction(
                '_getResultsRow',
                array($each_table, $newsearchsqls, 2)
            )
        );
    }

    /**
     * Data provider for testGetResultRow
     *
     * @return array provider for testGetResultsRow
     */
    public function providerForTestGetResultsRow()
    {
        return array(
            array(
                'table1',
                array(
                    'SELECT *  FROM `pma`.`table1` WHERE FALSE',
                    'SELECT COUNT(*) AS `count` FROM `pma`.`table1` WHERE FALSE',
                    'select_count' => 2,
                    'select_columns' => 'column1',
                    'delete' => 'column2'
                ),
                '<tr class="noclick"><td>2 matches in <strong>table1</strong>'
                . '</td><td><a name="browse_search"  class="ajax browse_results" '
                . 'href="sql.php?db=pma&amp;table'
                . '=table1&amp;goto=db_sql.php&amp;pos=0&amp;is_js_confirmed=0&amp;'
                . 'server=0&amp;lang=en&amp;'
                . 'collation_connection=utf-8" '
                . 'data-browse-sql="column1" data-table-name="table1" '
                . '>Browse</a></td><td>'
                . '<a name="delete_search" class="ajax delete_results" href'
                . '="sql.php?db=pma&amp;table=table1&amp;goto=db_sql.php&amp;pos=0'
                . '&amp;is_js_confirmed=0&amp;server=0&amp;'
                . 'lang=en&amp;collation_connection=utf-8" '
                . 'data-delete-sql="column2" '
                . 'data-table-name="table1" '
                . '>Delete</a></td></tr>'
            )
        );
    }

    /**
     * Test for getSelectionForm
     *
     * @return void
     */
    public function testGetSelectionForm()
    {
        $form = $this->object->getSelectionForm();
        $this->assertContains('<form', $form);
        $this->assertContains('<a id="togglesearchformlink">', $form);
        $this->assertContains('criteriaSearchType', $form);
    }

    /**
     * Test for getResultDivs
     *
     * @return void
     */
    public function testGetResultDivs()
    {
        $this->assertEquals(
            '<!-- These two table-image and table-link elements display the '
            . 'table name in browse search results  --><div id="table-info">'
            . '<a class="item" id="table-link" ></a></div><div id="browse-results">'
            . '<!-- this browse-results div is used to load the browse and delete '
            . 'results in the db search --></div><br class="clearfloat" />'
            . '<div id="sqlqueryform"><!-- this sqlqueryform div is used to load the'
            . ' delete form in the db search --></div><!--  toggle query box link-->'
            . '<a id="togglequerybox"></a>',
            $this->_callProtectedFunction(
                'getResultDivs',
                array()
            )
        );
    }

}
