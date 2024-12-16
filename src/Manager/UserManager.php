<?php

namespace App\Manager;

use DateTime;
use Exception;
use App\Entity\User;
use App\Util\VisitorInfoUtil;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\ByteString;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserManager
 *
 * The manager for user related functionality
 *
 * @package App\Manager
 */
class UserManager
{
    private LogManager $logManager;
    private EmailManager $emailManager;
    private ErrorManager $errorManager;
    private UserRepository $userRepository;
    private VisitorInfoUtil $visitorInfoUtil;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        LogManager $logManager,
        EmailManager $emailManager,
        ErrorManager $errorManager,
        UserRepository $userRepository,
        VisitorInfoUtil $visitorInfoUtil,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->logManager = $logManager;
        $this->emailManager = $emailManager;
        $this->errorManager = $errorManager;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->visitorInfoUtil = $visitorInfoUtil;
    }

    /**
     * Get user repository
     *
     * @return UserRepository The user repository
     */
    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    /**
     * Check if user id exist in database
     *
     * @param int $id The id of the user
     *
     * @return bool True if user exists, false otherwise
     */
    public function checkIfUserIdExistInDatabase(int $id): bool
    {
        return $this->userRepository->find($id) !== null;
    }

    /**
     * Check if user email already registered in database
     *
     * @param string $email The email address of the user
     *
     * @return bool True if user exists, false otherwise
     */
    public function checkIfUserEmailAlreadyRegistered(string $email): bool
    {
        return $this->userRepository->findByEmail($email) !== null;
    }

    /**
     * Get user id by email
     *
     * @param string $email The email address of the user
     *
     * @return int The user id or null if user does not exist
     */
    public function getUserIdByEmail(string $email): int
    {
        // get user object
        $user = $this->userRepository->findByEmail($email);

        // get user id
        $id = $user !== null ? $user->getId() : null;

        // check if user id found
        if ($id === null) {
            $this->errorManager->handleError(
                'Error retrieving user id by email: ' . $email,
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        return $id;
    }

    /**
     * Get user email by id
     *
     * @param int $id The user id
     *
     * @return string The user email or null if user not found
     */
    public function getUserEmailById(int $id): string
    {
        // get user object
        $user = $this->userRepository->find($id);

        // get user email
        $email = $user !== null ? $user->getEmail() : null;

        // check if user email found
        if ($email === null) {
            $this->errorManager->handleError(
                'Error retrieving user email by id: ' . $id,
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        return $email;
    }

    /**
     * Get users list
     *
     * @return array<array<string, array<string>|int|string|null>> The users list
     */
    public function getUsersList(): array
    {
        $list = [];

        // get users
        $users = $this->userRepository->findAll();

        // add all users to list
        foreach ($users as $user) {
            $registerTime = $user->getRegisterTime();
            $lastLoginTime = $user->getLastLoginTime();

            // format time values
            if ($registerTime != null) {
                $registerTime = $registerTime->format('Y-m-d H:i:s');
            }
            if ($lastLoginTime != null) {
                $lastLoginTime = $lastLoginTime->format('Y-m-d H:i:s');
            }

            $list[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'first-name' => $user->getFirstName(),
                'last-name' => $user->getLastName(),
                'roles' => $user->getRoles(),
                'register-time' => $registerTime ?? 'Never',
                'last-login-time' => $lastLoginTime ?? 'Never',
                'ip-address' => $user->getIpAddress(),
                'browser' => $this->visitorInfoUtil->getBrowserShortify($user->getUserAgent() ?? 'Unknown'),
                'status' => $user->getStatus()
            ];
        }

        return $list;
    }

    /**
     * Register new user to database
     *
     * @param string $email The email address of the user
     * @param string $firstName The first name of the user
     * @param string $lastName The last name of the user
     * @param string $password The password of the user
     *
     * @return void
     */
    public function registerUser(string $email, string $firstName, string $lastName, string $password): void
    {
        // validate input data
        $email = trim($email);
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $password = trim($password);

        // validate input data length
        if (strlen($email) < 2 || strlen($email) > 255) {
            $this->errorManager->handleError(
                message: 'invalid email address length (must be between 2 and 255 characters)',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }
        if (strlen($firstName) < 2 || strlen($firstName) > 255) {
            $this->errorManager->handleError(
                message: 'invalid first name length (must be between 2 and 255 characters)',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }
        if (strlen($lastName) < 2 || strlen($lastName) > 255) {
            $this->errorManager->handleError(
                message: 'invalid last name length (must be between 2 and 255 characters)',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }
        if (strlen($password) < 6 || strlen($password) > 255) {
            $this->errorManager->handleError(
                message: 'invalid password length (must be between 6 and 255 characters)',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // check if user email is already registered
        if ($this->checkIfUserEmailAlreadyRegistered($email)) {
            $this->errorManager->handleError(
                message: 'user: ' . $email . ' already exists',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // get visitor info
        $ipAddress = $this->visitorInfoUtil->getIP();
        $userAgent = $this->visitorInfoUtil->getUserAgent();

        // check if user info is valid
        if ($ipAddress == null || $userAgent == null) {
            $this->errorManager->handleError(
                message: 'invalid user info: ip address or user agent is null',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // create user entity
        $user = new User();

        // hash password
        $passwordHash = $this->passwordHasher->hashPassword($user, $password);

        // set user properties
        $user->setEmail($email)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRoles(['ROLE_USER'])
            ->setPassword($passwordHash)
            ->setRegisterTime(new DateTime())
            ->setLastLoginTime(new DateTime())
            ->setIpAddress($ipAddress)
            ->setUserAgent($userAgent)
            ->setStatus('active');

        // save user to database
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to register user',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // send email to user
        $this->emailManager->sendEmail($email, 'User registration', [
            'subject' => 'User registration',
            'message' => 'Your user account has been created. First name: ' . $firstName . ', last name: ' . $lastName,
        ]);

        // log action to database
        $this->logManager->saveLog(
            name: 'user-manager',
            message: 'new user registered: ' . $email,
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Get user info
     *
     * @param int $id The user id
     *
     * @return array<string, array<int, string>|string|bool> The user info
     */
    public function getUserInfo(int $id): array
    {
        $this->logManager->saveLog(
            name: 'user-manager',
            message: 'get user info with id: ' . $id,
            level: LogManager::LEVEL_INFO
        );

        // get user
        $user = $this->userRepository->find($id);

        // check if user exists
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'user not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // get user info
        $email = $user->getEmail();
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
        $roles = $user->getRoles();
        $registerTime = $user->getRegisterTime();
        $lastLoginTime = $user->getLastLoginTime();
        $ipAddress = $user->getIpAddress();
        $userAgent = $user->getUserAgent();
        $status = $user->getStatus();

        // check if user info found
        if (
            $email === null || $firstName === null || $lastName === null || $roles === null
            || $registerTime === null || $lastLoginTime === null || $ipAddress === null
            || $userAgent === null || $status === null
        ) {
            $this->errorManager->handleError(
                message: 'user id: ' . $id . ' info not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // return user status
        return [
            'email' => $email,
            'first-name' => $firstName,
            'last-name' => $lastName,
            'roles' => $roles,
            'register-time' => $registerTime->format('Y-m-d H:i:s'),
            'last-login-time' => $lastLoginTime->format('Y-m-d H:i:s'),
            'ip-address' => $ipAddress,
            'user-agent' => $userAgent,
            'status' => $status,
            'is-active' => $status === 'active',
        ];
    }

    /**
     * Update user password
     *
     * @param int $id User id to password update
     * @param string $newPassword New password
     *
     * @return void
     */
    public function updateUserPassword(int $id, string $newPassword): void
    {
        // get user by id
        $user = $this->userRepository->find($id);

        // check if user found
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'User not found!',
                code: JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        // hash password
        $newPassword = $this->passwordHasher->hashPassword($user, $newPassword);

        // update user password
        $user->setPassword($newPassword);

        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'Error while updating user password!',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // get email by user id
        $email = $this->getUserEmailById($id);

        // log event
        $this->logManager->saveLog(
            name: 'user-manager',
            message: 'User: ' . $email . ' password changed',
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Update user data on login
     *
     * @param string $identifier The user identifier
     *
     * @return void
     */
    public function updateUserDataOnLogin(string $identifier): void
    {
        // get user by identifier
        $user = $this->userRepository->findOneBy(['email' => $identifier]);

        // check if user exists
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'user not found with identifier: ' . $identifier,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // get visitor info
        $ipAddress = $this->visitorInfoUtil->getIP() ?? 'Unknown';
        $userAgent = $this->visitorInfoUtil->getUserAgent() ?? 'Unknown';

        // update user data
        $user->setLastLoginTime(new DateTime());
        $user->setIpAddress($ipAddress);
        $user->setUserAgent($userAgent);

        // save user to database
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to flush user entity update',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }
    }

    /**
     * Delete user by email
     *
     * @param int $id The user id
     *
     * @return void
     */
    public function deleteUser(int $id): void
    {
        // get user
        $user = $this->userRepository->find($id);

        // get user email by id
        $email = $this->getUserEmailById($id);

        // check if user exists
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'user not found: ' . $email,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // delete user
        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to delete user: ' . $email,
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // log action to database
        $this->logManager->saveLog(
            name: 'user-manager',
            message: 'user deleted: ' . $email,
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Update user status
     *
     * @param int $id The user id
     * @param string $status The new user status
     *
     * @return void
     */
    public function updateUserStatus(int $id, string $status): void
    {
        // get user by id
        $user = $this->userRepository->find($id);

        // check if user found
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'user id: ' . $id . ' not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // get old user status
        $oldStatus = $user->getStatus();

        // update user status
        $user->setStatus($status);

        // flush changes to database
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'user id: ' . $id . ' could not be updated',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // get user email by id
        $email = $this->getUserEmailById($id);

        // send email to user
        $this->emailManager->sendEmail($email, 'User status change', [
            'subject' => 'User status change',
            'message' => 'Your user status has been changed to: ' . $status,
        ]);

        // log action
        $this->logManager->saveLog(
            name: 'user-manager',
            message: 'user: ' . $email . ' updated status to: ' . $status . ' old status was: ' . $oldStatus,
            level: LogManager::LEVEL_INFO
        );
    }

    /**
     * Get user status
     *
     * @param int $id The user id
     *
     * @return string The user status
     */
    public function getUserStatus(int $id): string
    {
        // get user
        $user = $this->userRepository->find($id);

        // check if user exists
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'user id: ' . $id . ' not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // get user status
        $status = $user->getStatus();

        // check if user status found
        if ($status === null) {
            $this->errorManager->handleError(
                message: 'user id: ' . $id . ' status not found',
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        return $status;
    }

    /**
     * Reset user password
     *
     * @param int $id The user id
     *
     * @return string
     */
    public function resetUserPassword(int $id): string
    {
        // get user
        $user = $this->userRepository->find($id);

        // check if user exists
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'user not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // generate new password
        $password = ByteString::fromRandom(16);

        // hash password
        $passwordHash = $this->passwordHasher->hashPassword($user, $password);

        // update user password
        $user->setPassword($passwordHash);

        // save user to database
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to flush user entity update',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // get user email by id
        $email = $this->getUserEmailById($id);

        // send email to user
        $this->emailManager->sendEmail($email, 'Password reset', [
            'subject' => 'Password reset',
            'message' => 'Your password has been reset. New password is: ' . $password,
        ]);

        // log action to database
        $this->logManager->saveLog(
            name: 'user-manager',
            message: 'user password reset: ' . $email,
            level: LogManager::LEVEL_INFO,
        );

        // return new password
        return $password;
    }

    /**
     * Check if user has specific role
     *
     * @param int $id The user id
     * @param string $role The role to check
     *
     * @return bool True if user has role, false otherwise
     */
    public function checkIfUserHasRole(int $id, string $role): bool
    {
        // validate role format
        $role = strtoupper($role);
        if (!str_starts_with($role, 'ROLE_')) {
            $role = 'ROLE_' . $role;
        }

        // get user
        $user = $this->userRepository->find($id);

        // check if user exists
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'user not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // check if user has role
        return in_array($role, $user->getRoles());
    }

    /**
     * Add role to specific user
     *
     * @param int $id The user id
     * @param string $role The role to add
     *
     * @return void
     */
    public function addRoleToUser(int $id, string $role): void
    {
        // validate role format
        $role = strtoupper($role);
        if (!str_starts_with($role, 'ROLE_')) {
            $role = 'ROLE_' . $role;
        }

        // get user
        $user = $this->userRepository->find($id);

        // check if user exists
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'User not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // check if user has role
        if ($this->checkIfUserHasRole($id, $role)) {
            $this->errorManager->handleError(
                message: 'User already has role: ' . $role,
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // add role to user
        $user->addRole($role);

        // save user to database
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to flush user entity update',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // get user email by id
        $email = $this->getUserEmailById($id);

        // log action to database
        $this->logManager->saveLog(
            name: 'user-manager',
            message: 'user role added: ' . $email . ' - ' . $role,
            level: LogManager::LEVEL_INFO,
        );
    }

    /**
     * Remove role from specific user
     *
     * @param int $id The id of the user
     * @param string $role The role to remove
     *
     * @return void
     */
    public function removeRoleFromUser(int $id, string $role): void
    {
        // validate role format
        $role = strtoupper($role);
        if (!str_starts_with($role, 'ROLE_')) {
            $role = 'ROLE_' . $role;
        }

        // get user
        $user = $this->userRepository->find($id);

        // check if user exists
        if ($user === null) {
            $this->errorManager->handleError(
                message: 'user not found with id: ' . $id,
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        // check if user has role
        if (!$this->checkIfUserHasRole($id, $role)) {
            $this->errorManager->handleError(
                message: 'user does not have role: ' . $role,
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // remove role from user
        $user->removeRole($role);

        // save user to database
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->errorManager->handleError(
                message: 'error to flush user entity update',
                code: JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                exceptionMessage: $e->getMessage()
            );
        }

        // get user email by id
        $email = $this->getUserEmailById($id);

        // log action to database
        $this->logManager->saveLog(
            name: 'user-manager',
            message: 'user role removed: ' . $email . ' - ' . $role,
            level: LogManager::LEVEL_INFO,
        );
    }
}
