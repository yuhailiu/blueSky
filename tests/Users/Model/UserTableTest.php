<?php
use Users\Model\UserTable;
require_once 'module/Users/src/Users/Model/UserTable.php';

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * UserTable test case.
 */
class UserTableTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var UserTable
     */
    private $UserTable;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated UserTableTest::setUp()
        $controller = new Zend\Mvc\Controller\AbstractController();
        $tableGateway = $controller->getServiceLocator('UserTableGateway');
        
        
        $this->UserTable = new UserTable($tableGateway);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated UserTableTest::tearDown()
        $this->UserTable = null;
        
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
     * Tests UserTable->saveUser()
     */
    public function testSaveUser()
    {
        // TODO Auto-generated UserTableTest->testSaveUser()
        $this->markTestIncomplete("saveUser test not implemented");
        
        $this->UserTable->saveUser(/* parameters */);
    }

    /**
     * Tests UserTable->fetchAll()
     */
    public function testFetchAll()
    {
        // TODO Auto-generated UserTableTest->testFetchAll()
        $this->markTestIncomplete("fetchAll test not implemented");
        
        $this->UserTable->fetchAll(/* parameters */);
    }

    /**
     * Tests UserTable->getUser()
     */
    public function testGetUser()
    {
        // TODO Auto-generated UserTableTest->testGetUser()
        $this->markTestIncomplete("getUser test not implemented");
        
        $this->UserTable->getUser(/* parameters */);
    }

    /**
     * Tests UserTable->getUserByEmail()
     */
    public function testGetUserByEmail()
    {
        // TODO Auto-generated UserTableTest->testGetUserByEmail()
        $this->markTestIncomplete("getUserByEmail test not implemented");
        
        $this->UserTable->getUserByEmail(/* parameters */);
    }

    /**
     * Tests UserTable->deleteUser()
     */
    public function testDeleteUser()
    {
        // TODO Auto-generated UserTableTest->testDeleteUser()
        $this->markTestIncomplete("deleteUser test not implemented");
        
        $this->UserTable->deleteUser(/* parameters */);
    }

    /**
     * Tests UserTable->updatePasswordById()
     */
    public function testUpdatePasswordById()
    {
        // TODO Auto-generated UserTableTest->testUpdatePasswordById()
        $this->markTestIncomplete("updatePasswordById test not implemented");
        
        $this->UserTable->updatePasswordById(/* parameters */);
    }

    /**
     * Tests UserTable->updateCaptchaById()
     */
    public function testUpdateCaptchaById()
    {
        // TODO Auto-generated UserTableTest->testUpdateCaptchaById()
        $this->markTestIncomplete("updateCaptchaById test not implemented");
        
        $this->UserTable->updateCaptchaById(/* parameters */);
    }

    /**
     * Tests UserTable->updateFailedTimesById()
     */
    public function testUpdateFailedTimesById()
    {
        // TODO Auto-generated UserTableTest->testUpdateFailedTimesById()
        $this->markTestIncomplete("updateFailedTimesById test not implemented");
        
        $this->UserTable->updateFailedTimesById(/* parameters */);
    }
}

