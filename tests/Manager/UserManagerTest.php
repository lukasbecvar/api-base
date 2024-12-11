<?php

namespace App\Tests\Manager;

use App\Entity\User;
use Monolog\Test\TestCase;
use App\Manager\LogManager;
use App\Manager\UserManager;
use App\Util\VisitorInfoUtil;
use App\Manager\ErrorManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class UserManagerTest
 *
 * Test cases for user manager
 *
 * @package App\Tests\Manager
 */
class UserManagerTest extends TestCase
{
    private UserManager $userManager;
    private LogManager & MockObject $logManagerMock;
    private ErrorManager & MockObject $errorManagerMock;
    private UserRepository & MockObject $userRepositoryMock;
    private VisitorInfoUtil & MockObject $visitorInfoUtilMock;
    private EntityManagerInterface & MockObject $entityManagerMock;

    protected function setUp(): void
    {
        // mock dependencies
        $this->logManagerMock = $this->createMock(LogManager::class);
        $this->errorManagerMock = $this->createMock(ErrorManager::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->visitorInfoUtilMock = $this->createMock(VisitorInfoUtil::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        // create user manager instance
        $this->userManager = new UserManager(
            $this->logManagerMock,
            $this->errorManagerMock,
            $this->userRepositoryMock,
            $this->visitorInfoUtilMock,
            $this->entityManagerMock
        );
    }

    /**
     * Test get user id by email
     *
     * @return void
     */
    public function testGetUserIdByEmail(): void
    {
        $email = 'test@test.com';
        $userId = 1;

        // mock user entity
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('getId')->willReturn($userId);

        // mock repository response
        $this->userRepositoryMock->expects($this->once())->method('findByEmail')->with($email)
            ->willReturn($user);

        // call tested method
        $result = $this->userManager->getUserIdByEmail($email);

        // assert result
        $this->assertSame($userId, $result);
    }

    /**
     * Test is user exists
     *
     * @return void
     */
    public function testcheckIfUserEmailAlreadyRegistered(): void
    {
        // call tested method
        $checkIfUserEmailAlreadyRegistered = $this->userManager->checkIfUserEmailAlreadyRegistered('test@test.test');

        // assert result
        $this->assertIsBool($checkIfUserEmailAlreadyRegistered);
    }

    /**
     * Test register user
     *
     * @return void
     */
    public function testRegisterUser(): void
    {
        // testing user data
        $email = 'test@test.com';
        $firstName = 'John';
        $lastName = 'Doe';
        $password = 'secure_password';
        $ipAddress = '127.0.0.1';
        $userAgent = 'TestAgent';

        // mock repository to simulate no existing user
        $this->userRepositoryMock->expects($this->once())->method('findByEmail')
            ->with($email)->willReturn(null);

        // mock get visitor info
        $this->visitorInfoUtilMock->expects($this->once())->method('getIP')
            ->willReturn($ipAddress);
        $this->visitorInfoUtilMock->expects($this->once())->method('getUserAgent')
            ->willReturn($userAgent);

        // expect entity manager to persist and flush
        $this->entityManagerMock->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(User::class));
        $this->entityManagerMock->expects($this->once())->method('flush');

        // expect save log call
        $this->logManagerMock->expects($this->once())->method('saveLog')->with(
            'user-manager',
            'new user registered: ' . $email,
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->userManager->registerUser($email, $firstName, $lastName, $password);
    }

    /**
     * Test delete user
     *
     * @return void
     */
    public function testDeleteUser(): void
    {
        $userId = 1;
        $email = 'test@test.com';

        // mock user entity
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('getEmail')->willReturn($email);

        // mock repository to return the user
        $this->userRepositoryMock->expects($this->any())
            ->method('find')
            ->with($userId)
            ->willReturn($user);

        // expect entity manager to remove and flush the user
        $this->entityManagerMock->expects($this->once())
            ->method('remove')
            ->with($user);
        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        // expect log manager to save the log
        $this->logManagerMock->expects($this->once())->method('saveLog')->with(
            'user-manager',
            'user deleted: ' . $email,
            LogManager::LEVEL_INFO
        );

        // call the tested method
        $this->userManager->deleteUser($userId);
    }

    /**
     * Test update user status
     *
     * @return void
     */
    public function testUpdateUserStatus(): void
    {
        $userId = 1;
        $email = 'test@test.test';
        $newStatus = 'inactive';

        // mock user retrieval
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('getEmail')->willReturn($email);

        // expect set status call
        $user->expects($this->once())->method('setStatus')->with($newStatus);

        // mock repository to return the user
        $this->userRepositoryMock->expects($this->any())->method('find')->with($userId)
            ->willReturn($user);

        // mock entity manager to persist the changes
        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        // expect save log call
        $this->logManagerMock->expects($this->once())->method('saveLog')->with(
            name: 'user-manager',
            message: 'user: ' . $email . ' updated status to: ' . $newStatus,
            level: LogManager::LEVEL_INFO
        );

        // call the updateUserStatus method
        $this->userManager->updateUserStatus($userId, $newStatus);
    }

    /**
     * Test get user status
     *
     * @return void
     */
    public function testGetUserStatus(): void
    {
        // mock user retrieval
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('getStatus')->willReturn('active');

        // mock repository to return the user
        $this->userRepositoryMock->expects($this->any())->method('find')->with(1)
            ->willReturn($user);

        // call tested method
        $result = $this->userManager->getUserStatus(1);

        // assert result
        $this->assertSame('active', $result);
    }

    /**
     * Test reset user password
     *
     * @return void
     */
    public function testResetUserPassword(): void
    {
        // testing user data
        $id = 1;
        $email = 'test@test.com';

        // mock existing user
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('getEmail')->willReturn($email);

        // mock repository to return the user twice
        $this->userRepositoryMock->expects($this->any())->method('find')->with($id)
            ->willReturn($user);

        // expect entity manager to persist and flush
        $this->entityManagerMock->expects($this->once())->method('flush');

        // expect save log call
        $this->logManagerMock->expects($this->once())->method('saveLog')->with(
            'user-manager',
            'user password reset: ' . $email,
            LogManager::LEVEL_INFO
        );

        // call tested method
        $result = $this->userManager->resetUserPassword($id);

        // assert result
        $this->assertIsString($result);
        $this->assertEquals(16, strlen($result));
    }

    /**
     * Test check if user has role
     *
     * @return void
     */
    public function testCheckIfUserHasRole(): void
    {
        // testing user data
        $id = 1;
        $role = 'ROLE_ADMIN';

        // mock repository to simulate existing user
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('getRoles')->willReturn([$role]);
        $this->userRepositoryMock->expects($this->once())->method('find')
            ->with($id)->willReturn($user);

        // call tested method
        $this->assertTrue($this->userManager->checkIfUserHasRole($id, $role));
    }

    /**
     * Test add role to user
     *
     * @return void
     */
    public function testAddRoleToUser(): void
    {
        $id = 1;
        $email = 'test@test.com';
        $role = 'ROLE_ADMIN';

        // mock existing user
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('getEmail')->willReturn($email);
        $user->expects($this->once())->method('getRoles')->willReturn([]);
        $user->expects($this->once())->method('addRole')->with($role);

        // mock repository to return the user twice
        $this->userRepositoryMock->expects($this->any())->method('find')->with($id)
            ->willReturn($user);

        // expect entity manager to persist and flush
        $this->entityManagerMock->expects($this->once())->method('flush');

        // expect save log call
        $this->logManagerMock->expects($this->once())->method('saveLog')->with(
            'user-manager',
            'user role added: ' . $email . ' - ' . $role,
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->userManager->addRoleToUser($id, $role);
    }

    /**
     * Test remove role from user
     *
     * @return void
     */
    public function testRemoveRoleFromUser(): void
    {
        $id = 1;
        $email = 'test@test.com';
        $role = 'ROLE_ADMIN';

        // mock existing user
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('getEmail')->willReturn($email);
        $user->expects($this->once())->method('getRoles')->willReturn([$role]);
        $user->expects($this->once())->method('removeRole')->with($role);

        // mock repository to return the user twice
        $this->userRepositoryMock->expects($this->any())->method('find')->with($id)
            ->willReturn($user);

        // expect entity manager to persist and flush
        $this->entityManagerMock->expects($this->once())->method('flush');

        // expect save log call
        $this->logManagerMock->expects($this->once())->method('saveLog')->with(
            'user-manager',
            'user role removed: ' . $email . ' - ' . $role,
            LogManager::LEVEL_INFO
        );

        // call tested method
        $this->userManager->removeRoleFromUser($id, $role);
    }
}