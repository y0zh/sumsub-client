<?php

declare( strict_types=1 );

namespace alexeevdv\SumSub\Response;

final class ShareTokenResponse
{
    /**
     * A newly generated share token for the client.
     *
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @param string $token
     * @param string $userId
     */
    public function __construct( $token, $clientId )
    {
        $this->token = $token;
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->userId;
    }
}
