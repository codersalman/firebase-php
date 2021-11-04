<?php

declare(strict_types=1);

namespace Kreait\Firebase\Request;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\Uid;
use Kreait\Firebase\Value\Url;
use Stringable;

/**
 * @codeCoverageIgnore
 */
trait EditUserTrait
{
    protected ?Uid $uid = null;
    protected ?Email $email = null;
    protected ?string $displayName = null;
    protected ?bool $emailIsVerified = null;
    protected ?string $phoneNumber = null;
    protected ?Url $photoUrl = null;
    protected ?bool $markAsEnabled = null;
    protected ?bool $markAsDisabled = null;
    protected ?ClearTextPassword $clearTextPassword = null;

    /**
     * @param array<string, mixed> $properties
     *
     * @throws InvalidArgumentException when invalid properties have been provided
     */
    protected static function withEditableProperties(self $request, array $properties): static
    {
        foreach ($properties as $key => $value) {
            switch (\mb_strtolower((string) \preg_replace('/[^a-z]/i', '', $key))) {
                case 'uid':
                case 'localid':
                    $request = $request->withUid($value);

                    break;
                case 'email':
                    $request = $request->withEmail($value);

                    break;
                case 'unverifiedemail':
                    $request = $request->withUnverifiedEmail($value);

                    break;
                case 'verifiedemail':
                    $request = $request->withVerifiedEmail($value);

                    break;
                case 'emailverified':
                    if ($value === true) {
                        $request = $request->markEmailAsVerified();
                    } elseif ($value === false) {
                        $request = $request->markEmailAsUnverified();
                    }

                    break;
                case 'displayname':
                    $request = $request->withDisplayName($value);

                    break;
                case 'phone':
                case 'phonenumber':
                    $request = $request->withPhoneNumber($value);

                    break;
                case 'photo':
                case 'photourl':
                    $request = $request->withPhotoUrl($value);

                    break;
                case 'disableuser':
                case 'disabled':
                case 'isdisabled':
                    if ($value === true) {
                        $request = $request->markAsDisabled();
                    } elseif ($value === false) {
                        $request = $request->markAsEnabled();
                    }

                    break;
                case 'enableuser':
                case 'enabled':
                case 'isenabled':
                    if ($value === true) {
                        $request = $request->markAsEnabled();
                    } elseif ($value === false) {
                        $request = $request->markAsDisabled();
                    }

                    break;
                case 'password':
                case 'cleartextpassword':
                    $request = $request->withClearTextPassword($value);

                    break;
            }
        }

        return $request;
    }

    public function withUid(Stringable|string $uid): static
    {
        $request = clone $this;
        $request->uid = new Uid((string) $uid);

        return $request;
    }

    public function withEmail(Email|string $email): static
    {
        $request = clone $this;
        $request->email = $email instanceof Email ? $email : new Email($email);

        return $request;
    }

    public function withVerifiedEmail(Email|string $email): static
    {
        $request = clone $this;
        $request->email = $email instanceof Email ? $email : new Email($email);
        $request->emailIsVerified = true;

        return $request;
    }

    public function withUnverifiedEmail(Email|string $email): static
    {
        $request = clone $this;
        $request->email = $email instanceof Email ? $email : new Email($email);
        $request->emailIsVerified = false;

        return $request;
    }

    public function withDisplayName(string $displayName): static
    {
        $request = clone $this;
        $request->displayName = $displayName;

        return $request;
    }

    public function withPhoneNumber(Stringable|string|null $phoneNumber): static
    {
        $phoneNumber = $phoneNumber !== null ? (string) $phoneNumber : null;

        $request = clone $this;
        $request->phoneNumber = $phoneNumber;

        return $request;
    }

    public function withPhotoUrl(Url|string $url): static
    {
        $request = clone $this;
        $request->photoUrl = $url instanceof Url ? $url : Url::fromValue($url);

        return $request;
    }

    public function markAsDisabled(): static
    {
        $request = clone $this;
        $request->markAsEnabled = null;
        $request->markAsDisabled = true;

        return $request;
    }

    public function markAsEnabled(): static
    {
        $request = clone $this;
        $request->markAsDisabled = null;
        $request->markAsEnabled = true;

        return $request;
    }

    public function markEmailAsVerified(): static
    {
        $request = clone $this;
        $request->emailIsVerified = true;

        return $request;
    }

    public function markEmailAsUnverified(): static
    {
        $request = clone $this;
        $request->emailIsVerified = false;

        return $request;
    }

    public function withClearTextPassword(ClearTextPassword|string $clearTextPassword): static
    {
        $request = clone $this;
        $request->clearTextPassword = $clearTextPassword instanceof ClearTextPassword
            ? $clearTextPassword
            : new ClearTextPassword($clearTextPassword);

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    public function prepareJsonSerialize(): array
    {
        $disableUser = null;
        if ($this->markAsDisabled) {
            $disableUser = true;
        } elseif ($this->markAsEnabled) {
            $disableUser = false;
        }

        return \array_filter([
            'localId' => $this->uid?->__toString(),
            'disableUser' => $disableUser,
            'displayName' => $this->displayName,
            'email' => $this->email?->__toString(),
            'emailVerified' => $this->emailIsVerified,
            'phoneNumber' => $this->phoneNumber,
            'photoUrl' => $this->photoUrl?->__toString(),
            'password' => $this->clearTextPassword?->__toString(),
        ], static fn ($value) => $value !== null);
    }

    public function hasUid(): bool
    {
        return (bool) $this->uid;
    }
}
