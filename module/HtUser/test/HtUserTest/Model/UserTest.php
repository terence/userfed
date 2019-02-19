<?php

namespace HtUserTest\Model;

use HtApplication\Test\AbstractHttpControllerTestCase;

class UserTest extends AbstractHttpControllerTestCase
{
    public function testGeneratePassword()
    {
        $sl = $this->getApplicationServiceLocator();
        $user = $this->createUserWithInternalAuth();
        $newPassword = $user->generatePassword();
        /**
         * Check to ensure password has been changed
         */
        /* @var $internal \HtAuthentication\Model\Adapter\Internal */
        $internal = $sl->get('AuthAccount\Internal');
        $hashPassword = $internal->createHashPassword($newPassword);
        $internal->loadByUser($user);
        $this->assertSame($hashPassword, $internal->getPassword());
    }
}
