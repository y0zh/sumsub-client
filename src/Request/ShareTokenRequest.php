<?php

declare( strict_types=1 );

namespace alexeevdv\SumSub\Request;

final class ShareTokenRequest
{
    /**
     * Sumsub user ID that will be linked to the token.
     *
     * @var string
     */
    private $applicantId;

    /**
     * A unique identifier of the partner with whom applicantId can be shared.
     *
     * @var string
     */
    private $clientId;

    /**
     * Lifespan of a token in seconds. Default value is equal to 10 mins.
     *
     * @var int|null
     */
    private $ttlInSecs;

    public function __construct( string $applicantId, string $clientId, ?int $ttlInSecs = null )
    {
        $this->applicantId = $applicantId;
        $this->clientId = $clientId;
        $this->ttlInSecs = $ttlInSecs;
    }

    public function getApplicantId(): string
    {
        return $this->applicantId;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getTtlInSecs(): ?int
    {
        return $this->ttlInSecs;
    }
}
