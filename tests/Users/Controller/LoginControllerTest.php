<?php
use Users\Controller\LoginController;
//require_once 'module/Users/src/Users/Controller/LoginController.php';

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * LoginController test case.
 */
class LoginControllerTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var LoginController
     */
    private $LoginController;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated LoginControllerTest::setUp()
        
        //$this->LoginController = new LoginController(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated LoginControllerTest::tearDown()
        $this->LoginController = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }
    
    public function testIndexAction()
    {
    	// TODO Auto-generated IndexControllerTest->testIndexAction()
        $this->getRequest()
        ->setMethod('POST')
        ->setPost(new Parameters(array('argument' => 'value')));
        $this->dispatch('/');
        
    	$this->markTestIncomplete("indexAction test not implemented");
    
    	$this->IndexController->indexAction(/* parameters */);
    }

}

