<?php

declare(strict_types=1);

namespace alexeevdv\SumSub\Request;

final class AccessTokenRequest
{
    /**
     * An external user ID which will be bound to the token.
     *
     * @var string
     */
    private $userId;

    /**
     * A name of the level configured in the dashboard.
     *
     * @var string
     */
    private $levelName;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $phone;

    /**
     * Lifespan of a token in seconds. Default value is equal to 10 mins.
     *
     * @var int|null
     */
    private $ttlInSecs;

    /**
     * @param string $userId
     * @param string $levelName
     * @param string|null $email
     * @param string|null $phone
     * @param int|null $ttlInSecs
     */
    public function __construct( string $userId, string $levelName, ?string $email = null, ?string $phone = null, ?int $ttlInSecs = null )
    {
        $this->userId       = $userId;
        $this->levelName    = $levelName;
        $this->ttlInSecs    = $ttlInSecs;
        $this->email        = $email;
        $this->phone        = $phone;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getLevelName(): string
    {
        return $this->levelName;
    }

    public function getTtlInSecs(): ?int
    {
        return $this->ttlInSecs;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }
}
