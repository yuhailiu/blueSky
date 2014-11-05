<?php
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
require_once 'module/Users/src/Users/Controller/IndexController.php';

require_once 'Zend/Test/PHPUnit/Controller/AbstractHttpControllerTestCase.php';

/**
 * IndexController test case.
 */
class IndexControllerTest extends AbstractHttpControllerTestCase
{

    /**
     *
     * @var IndexController
     */
    private $IndexController;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated IndexControllerTest::setUp()
        
        $this->IndexController = new IndexController(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated IndexControllerTest::tearDown()
        $this->IndexController = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests IndexController->indexAction()
     */
    public function testIndexAction()
    {
        // TODO Auto-generated IndexControllerTest->testIndexAction()
        $this->markTestIncomplete("indexAction test not implemented");
        
        $this->IndexController->indexAction(/* parameters */);
    }
}

