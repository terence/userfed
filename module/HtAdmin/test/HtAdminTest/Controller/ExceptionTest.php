<?php
/**
 * Test return data when system has error.
 */
namespace HtAdminTest\Controller;

use HtAdmin\Test\AbstractHttpControllerTestCase;

class ExceptionTest extends AbstractHttpControllerTestCase
{
    /**
     * Ensure method triggerException always return to user with json format.
     */
    public function testRestApiDbException()
    {
        $this->loginAdmin();
        $user = $this->createUserWithInternalAuth();
        /* mock error in database.
         * Method triggerException when db has error.
         * That why we place mockDbException here.
         */
        $this->mockDatabaseException('User');
        $url = $this->fromRoute('rest-api/user', array('id' => $user->getUserId()));
        $this->dispatch($url, 'DELETE');
        $this->assertResponseStatusCode(500);
        $this->assertResponseIsJson();
    }
}
