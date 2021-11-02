<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Firebase\Auth\Token\Domain\Generator;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\Value\Uid;
use Lcobucci\JWT\Token;

final class DisabledLegacyCustomTokenGenerator implements Generator
{
    public function __construct(private string $reason)
    {
    }

    /**
     * @param Uid|string $uid
     * @param array<string, mixed> $claims
     */
    public function createCustomToken($uid, array $claims = []): Token
    {
        throw new RuntimeException($this->reason);
    }
}
