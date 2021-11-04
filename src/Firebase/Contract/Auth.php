<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use DateInterval;
use Firebase\Auth\Token\Exception\ExpiredToken;
use Firebase\Auth\Token\Exception\InvalidSignature;
use Firebase\Auth\Token\Exception\InvalidToken;
use Firebase\Auth\Token\Exception\IssuedInTheFuture;
use Firebase\Auth\Token\Exception\UnknownKey;
use InvalidArgumentException;
use Kreait\Firebase\Auth\ActionCodeSettings;
use Kreait\Firebase\Auth\CreateActionLink\FailedToCreateActionLink;
use Kreait\Firebase\Auth\CreateSessionCookie\FailedToCreateSessionCookie;
use Kreait\Firebase\Auth\DeleteUsersResult;
use Kreait\Firebase\Auth\SendActionLink\FailedToSendActionLink;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception;
use Kreait\Firebase\Exception\Auth\ExpiredOobCode;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Request;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\Provider;
use Kreait\Firebase\Value\Uid;
use Lcobucci\JWT\Token;
use Psr\Http\Message\UriInterface;
use Traversable;

interface Auth
{
    /**
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function getUser(Uid|string $uid): UserRecord;

    /**
     * @param array<Uid|string> $uids
     *
     * @throws Exception\FirebaseException
     * @throws Exception\AuthException
     *
     * @phpstan-return array<string, UserRecord|null>
     */
    public function getUsers(array $uids): array;

    /**
     * @throws Exception\FirebaseException
     * @throws Exception\AuthException
     *
     * @return Traversable<UserRecord>
     */
    public function listUsers(int $maxResults = 1000, int $batchSize = 1000): Traversable;

    /**
     * Creates a new user with the provided properties.
     *
     * @param array<string, mixed>|Request\CreateUser $properties
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function createUser(array|Request\CreateUser $properties): UserRecord;

    /**
     * Updates the given user with the given properties.
     *
     * @param array<string, mixed>|Request\UpdateUser $properties
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function updateUser(Uid|string $uid, array|Request\UpdateUser $properties): UserRecord;

    /**
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function createUserWithEmailAndPassword(Email|string $email, ClearTextPassword|string $password): UserRecord;

    /**
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function getUserByEmail(Email|string $email): UserRecord;

    /**
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function getUserByPhoneNumber(string|\Stringable $phoneNumber): UserRecord;

    /**
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function createAnonymousUser(): UserRecord;

    /**
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function changeUserPassword(Uid|string $uid, ClearTextPassword|string $newPassword): UserRecord;

    /**
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function changeUserEmail(Uid|string $uid, Email|string $newEmail): UserRecord;

    /**
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function enableUser(Uid|string $uid): UserRecord;

    /**
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function disableUser(Uid|string $uid): UserRecord;

    /**
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function deleteUser(Uid|string $uid): void;

    /**
     * @param iterable<Uid|string> $uids
     * @param bool $forceDeleteEnabledUsers Whether to force deleting accounts that are not in disabled state. If false, only disabled accounts will be deleted, and accounts that are not disabled will be added to the errors.
     *
     * @throws Exception\AuthException
     */
    public function deleteUsers(iterable $uids, bool $forceDeleteEnabledUsers = false): DeleteUsersResult;

    /**
     * @param ActionCodeSettings|array<string, string|bool|null>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getEmailActionLink(string $type, Email|string $email, array|ActionCodeSettings $actionCodeSettings = null): string;

    /**
     * @param ActionCodeSettings|array<string, string|bool|null>|null $actionCodeSettings
     *
     * @throws UserNotFound
     * @throws FailedToSendActionLink
     */
    public function sendEmailActionLink(string $type, Email|string $email, ActionCodeSettings|array $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * @param ActionCodeSettings|array<string, string|bool|null>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getEmailVerificationLink(Email|string $email, ActionCodeSettings|array $actionCodeSettings = null): string;

    /**
     * @param ActionCodeSettings|array<string, string|bool|null>|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendEmailVerificationLink(Email|string $email, ActionCodeSettings|array $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * @param ActionCodeSettings|array<string, string|bool|null>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getPasswordResetLink(Email|string $email, ActionCodeSettings|array $actionCodeSettings = null): string;

    /**
     * @param ActionCodeSettings|array<string, string|bool|null>|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendPasswordResetLink(Email|string $email, ActionCodeSettings|array $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * @param ActionCodeSettings|array<string, string|bool|null>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getSignInWithEmailLink(Email|string $email, ActionCodeSettings|array $actionCodeSettings = null): string;

    /**
     * @param ActionCodeSettings|array<string, string|bool|null>|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendSignInWithEmailLink(Email|string $email, ActionCodeSettings|array $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * Sets additional developer claims on an existing user identified by the provided UID.
     *
     * @see https://firebase.google.com/docs/auth/admin/custom-claims
     *
     * @param array<string, mixed>|null $claims
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function setCustomUserClaims(Uid|string $uid, ?array $claims): void;

    /**
     * @param array<string, mixed> $claims
     */
    public function createCustomToken(Uid|string $uid, array $claims = []): Token;

    public function parseToken(string $tokenString): Token;

    /**
     * Creates a new Firebase session cookie with the given lifetime.
     *
     * The session cookie JWT will have the same payload claims as the provided ID token.
     *
     * @param Token|string $idToken The Firebase ID token to exchange for a session cookie
     *
     * @throws InvalidArgumentException if the token or TTL is invalid
     * @throws FailedToCreateSessionCookie
     */
    public function createSessionCookie(Token|string $idToken, DateInterval|int $ttl): string;

    /**
     * Verifies a JWT auth token. Returns a Promise with the tokens claims. Rejects the promise if the token
     * could not be verified. If checkRevoked is set to true, verifies if the session corresponding to the
     * ID token was revoked. If the corresponding user's session was invalidated, a RevokedToken
     * exception is thrown. If not specified the check is not applied.
     *
     * NOTE: Allowing time inconsistencies might impose a security risk. Do this only when you are not able
     * to fix your environment's time to be consistent with Google's servers. This parameter is here
     * for backwards compatibility reasons, and will be removed in the next major version. You
     * shouldn't rely on it.
     *
     * @param Token|string $idToken the JWT to verify
     * @param bool $checkIfRevoked whether to check if the ID token is revoked
     *
     * @throws InvalidArgumentException if the token could not be parsed
     * @throws InvalidToken if the token could be parsed, but is invalid for any reason (invalid signature, expired, time errors)
     * @throws InvalidSignature if the signature doesn't match
     * @throws ExpiredToken if the token is expired
     * @throws IssuedInTheFuture if the token is issued in the future
     * @throws UnknownKey if the token's kid header doesnt' contain a known key
     * @throws RevokedIdToken if the token has been revoked
     */
    public function verifyIdToken(Token|string $idToken, bool $checkIfRevoked = false): Token;

    /**
     * Verifies the given password reset code.
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-verify-password-reset-code
     *
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function verifyPasswordResetCode(string $oobCode): void;

    /**
     * Verifies the given password reset code and returns the associated user's email address.
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-verify-password-reset-code
     *
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function verifyPasswordResetCodeAndReturnEmail(string $oobCode): Email;

    /**
     * Applies the password reset requested via the given OOB code.
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-confirm-reset-password
     *
     * @param string $oobCode the email action code sent to the user's email for resetting the password
     * @param bool $invalidatePreviousSessions Invalidate sessions initialized with the previous credentials
     *
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws UserDisabled
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function confirmPasswordReset(string $oobCode, ClearTextPassword|string $newPassword, bool $invalidatePreviousSessions = true): void;

    /**
     * Applies the password reset requested via the given OOB code and returns the associated user's email address.
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-confirm-reset-password
     *
     * @param string $oobCode the email action code sent to the user's email for resetting the password
     * @param bool $invalidatePreviousSessions Invalidate sessions initialized with the previous credentials
     *
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws UserDisabled
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function confirmPasswordResetAndReturnEmail(string $oobCode, ClearTextPassword|string $newPassword, bool $invalidatePreviousSessions = true): Email;

    /**
     * Revokes all refresh tokens for the specified user identified by the uid provided.
     * In addition to revoking all refresh tokens for a user, all ID tokens issued
     * before revocation will also be revoked on the Auth backend. Any request with an
     * ID token generated before revocation will be rejected with a token expired error.
     *
     * @param Uid|string $uid the user whose tokens are to be revoked
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function revokeRefreshTokens(Uid|string $uid): void;

    /**
     * @param Provider[]|string[]|string $provider
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function unlinkProvider(Uid|string $uid, array|string $provider): UserRecord;

    /**
     * @param array<string, mixed>|null $claims
     *
     * @throws FailedToSignIn
     */
    public function signInAsUser(UserRecord|Uid|string $user, ?array $claims = null): SignInResult;

    /**
     * @throws FailedToSignIn
     */
    public function signInWithCustomToken(Token|string $token): SignInResult;

    /**
     * @throws FailedToSignIn
     */
    public function signInWithRefreshToken(string $refreshToken): SignInResult;

    /**
     * @throws FailedToSignIn
     */
    public function signInWithEmailAndPassword(string|Email $email, string|ClearTextPassword $clearTextPassword): SignInResult;

    /**
     * @throws FailedToSignIn
     */
    public function signInWithEmailAndOobCode(string|Email $email, string $oobCode): SignInResult;

    /**
     * @throws FailedToSignIn
     */
    public function signInAnonymously(): SignInResult;

    public function signInWithTwitterOauthCredential(string $accessToken, string $oauthTokenSecret, ?string $redirectUrl = null, ?string $linkingIdToken = null): SignInResult;

    public function signInWithGoogleIdToken(string $idToken, ?string $redirectUrl = null, ?string $linkingIdToken = null): SignInResult;

    public function signInWithFacebookAccessToken(string $accessToken, ?string $redirectUrl = null, ?string $linkingIdToken = null): SignInResult;

    public function signInWithAppleIdToken(string $idToken, ?string $rawNonce = null, ?string $redirectUrl = null, ?string $linkingIdToken = null): SignInResult;

    /**
     * @see https://cloud.google.com/identity-platform/docs/reference/rest/v1/accounts/signInWithIdp
     *
     * @throws FailedToSignIn
     */
    public function signInWithIdpAccessToken(Provider|string $provider, string $accessToken, UriInterface|string $redirectUrl = null, ?string $oauthTokenSecret = null, ?string $linkingIdToken = null, ?string $rawNonce = null): SignInResult;

    /**
     * @throws FailedToSignIn
     */
    public function signInWithIdpIdToken(Provider|string $provider, Token|string $idToken, UriInterface|string $redirectUrl = null, ?string $linkingIdToken = null, ?string $rawNonce = null): SignInResult;
}
