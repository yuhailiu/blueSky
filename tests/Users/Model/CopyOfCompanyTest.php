<?php
use Users\Model\Company;
use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
require_once 'module/Users/src/Users/Model/Company.php';

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Company test case.
 */
class copyCompanyTest extends AbstractControllerTestCase
{

    /**
     *
     * @var Company
     */
    private $Company;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->setApplicationConfig('/Users/liuyuhai/Sites/ttruus/config/test/application.config.php');
        parent::setUp();
        
        // TODO Auto-generated CompanyTest::setUp()
        
        $this->Company = new Company(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated CompanyTest::tearDown()
        $this->Company = null;
        
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
     * Tests Company->hasEmployees()
     */
    public function testHasEmployees()
    {
        // TODO Auto-generated CompanyTest->testHasEmployees()
        //$this->markTestIncomplete("hasEmployees test not implemented");
        
        $this->Company->hasEmployees(/* parameters */);
    }
}

