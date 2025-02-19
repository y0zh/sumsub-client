<?php

declare(strict_types=1);

namespace alexeevdv\SumSub;

use alexeevdv\SumSub\Exception\BadResponseException,
    alexeevdv\SumSub\Exception\TransportException;
use alexeevdv\SumSub\Request\AccessTokenRequest,
    alexeevdv\SumSub\Request\ShareTokenRequest,
    alexeevdv\SumSub\Request\ApplicantDataRequest,
    alexeevdv\SumSub\Request\ApplicantStatusRequest,
    alexeevdv\SumSub\Request\DocumentImageRequest,
    alexeevdv\SumSub\Request\InspectionChecksRequest,
    alexeevdv\SumSub\Request\RequestSignerInterface,
    alexeevdv\SumSub\Request\ResetApplicantRequest;
use alexeevdv\SumSub\Response\AccessTokenResponse,
    alexeevdv\SumSub\Response\ShareTokenResponse,
    alexeevdv\SumSub\Response\ApplicantDataResponse,
    alexeevdv\SumSub\Response\ApplicantStatusResponse,
    alexeevdv\SumSub\Response\DocumentImageResponse,
    alexeevdv\SumSub\Response\InspectionChecksResponse;
use Psr\Http\Client\ClientExceptionInterface,
    Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface,
    Psr\Http\Message\RequestInterface,
    Psr\Http\Message\ResponseInterface,
    Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\Utils;

final class Client implements ClientInterface
{
    public const PRODUCTION_BASE_URI = 'https://api.sumsub.com';

    public const STAGING_BASE_URI = 'https://test-api.sumsub.com';

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var RequestSignerInterface
     */
    private $requestSigner;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(
        HttpClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        RequestSignerInterface $requestSigner,
        string $baseUrl = self::PRODUCTION_BASE_URI
    ) {
        $this->httpClient       = $httpClient;
        $this->requestFactory   = $requestFactory;
        $this->requestSigner    = $requestSigner;
        $this->baseUrl          = $baseUrl;
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getAccessToken( AccessTokenRequest $request ): AccessTokenResponse
    {
        $queryParams = [
            'userId'    => $request->getUserId(),
            'levelName' => $request->getLevelName(),
        ];

        if ( $request->getEmail() !== null ) {
            $queryParams['applicantIdentifiers']['email'] = $request->getEmail();
        }

        if ( $request->getPhone() !== null ) {
            $queryParams['applicantIdentifiers']['phone'] = $request->getPhone();
        }

        if ( $request->getTtlInSecs() !== null ) {
            $queryParams['ttlInSecs'] = $request->getTtlInSecs();
        }

        $httpRequest = $this->createApiRequest(
            'POST',
            '/resources/accessTokens/sdk',
            ['Content-type' => 'application/json'],
            Utils::streamFor( json_encode( $queryParams ) )
        );
        
        $httpResponse = $this->sendApiRequest( $httpRequest );
        if ( $httpResponse->getStatusCode() !== 200 ) {
            throw new BadResponseException( $httpResponse );
        }

        $decodedResponse = $this->decodeResponse( $httpResponse );

        return new AccessTokenResponse( $decodedResponse['token'], $decodedResponse['userId'] );
    }


    /**
     * @param ShareTokenRequest $request
     * @return ShareTokenResponse
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getShareToken( ShareTokenRequest $request ): ShareTokenResponse
    {
        $queryParams = [
            'applicantId'   => $request->getApplicantId(),
            'forClientId'   => $request->getClientId(),
        ];

        if ( $request->getTtlInSecs() !== null ) {
            $queryParams['ttlInSecs'] = $request->getTtlInSecs();
        }

        $httpRequest = $this->createApiRequest(
            'POST',
            '/resources/accessTokens/shareToken',
            ['Content-type' => 'application/json'],
            Utils::streamFor(json_encode( $queryParams ))
        );
        
        $httpResponse = $this->sendApiRequest( $httpRequest );
        if ( $httpResponse->getStatusCode() !== 200 ) {
            throw new BadResponseException($httpResponse);
        }

        $decodedResponse = $this->decodeResponse( $httpResponse );

        return new ShareTokenResponse( $decodedResponse['token'], $decodedResponse['forClientId'] );
    }


    /**
     * @param ApplicantDataRequest $request
     * @return ApplicantDataResponse
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getApplicantData( ApplicantDataRequest $request ): ApplicantDataResponse
    {
        if ( $request->getApplicantId() !== null ) {
            $url = '/resources/applicants/'. $request->getApplicantId() .'/one';
        }
        else {
            $url = '/resources/applicants/-;externalUserId='. $request->getExternalUserId() .'/one';
        }

        $httpRequest = $this->createApiRequest('GET', $url);
        
        $httpResponse = $this->sendApiRequest( $httpRequest );
        if ( $httpResponse->getStatusCode() !== 200 ) {
            throw new BadResponseException( $httpResponse );
        }

        return new ApplicantDataResponse( $this->decodeResponse( $httpResponse ) );
    }

    /**
     * @param ResetApplicantRequest $request
     * @return void
     * @throws BadResponseException
     * @throws TransportException
     */
    public function resetApplicant( ResetApplicantRequest $request ): void
    {
        $httpRequest = $this->createApiRequest(
            'POST',
            '/resources/applicants/'. $request->getApplicantId() .'/reset'
        );
        
        $httpResponse = $this->sendApiRequest( $httpRequest );
        if ( $httpResponse->getStatusCode() !== 200 ) {
            throw new BadResponseException( $httpResponse );
        }

        $decodedResponse = $this->decodeResponse( $httpResponse );
        $isOk = ( $decodedResponse['ok'] ?? 0 ) === 1;

        if ( ! $isOk ) {
            throw new BadResponseException( $httpResponse );
        }
    }

    /**
     * @param ApplicantStatusRequest $request
     * @return ApplicantStatusResponse
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getApplicantStatus( ApplicantStatusRequest $request ): ApplicantStatusResponse
    {
        $httpRequest = $this->createApiRequest(
            'GET',
            '/resources/applicants/'. $request->getApplicantId() .'/requiredIdDocsStatus'
        );
        
        $httpResponse = $this->sendApiRequest( $httpRequest );
        if ( $httpResponse->getStatusCode() !== 200 ) {
            throw new BadResponseException( $httpResponse );
        }

        return new ApplicantStatusResponse( $this->decodeResponse( $httpResponse ) );
    }

    /**
     * @param DocumentImageRequest $request
     * @return DocumentImageResponse
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getDocumentImage( DocumentImageRequest $request ): DocumentImageResponse
    {
        $httpRequest = $this->createApiRequest(
            'GET',
            '/resources/inspections/'. $request->getInspectionId() .'/resources/'. $request->getImageId()
        );
        
        $httpResponse = $this->sendApiRequest( $httpRequest );
        if ( $httpResponse->getStatusCode() !== 200 ) {
            throw new BadResponseException( $httpResponse );
        }

        return new DocumentImageResponse( $httpResponse );
    }


    /**
     * @param InspectionChecksRequest $request
     * @return InspectionChecksResponse
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getInspectionChecks( InspectionChecksRequest $request ): InspectionChecksResponse
    {
        $httpRequest = $this->createApiRequest(
            'GET',
            '/resources/inspections/'. $request->getInspectionId() .'/checks'
        );
        
        $httpResponse = $this->sendApiRequest( $httpRequest );
        if ( $httpResponse->getStatusCode() !== 200 ) {
            throw new BadResponseException( $httpResponse );
        }

        return new InspectionChecksResponse( $this->decodeResponse( $httpResponse ) );
    }


    /**
     * @param string $method
     * @param string $uri
     * @param array $headers
     * @param StreamInterface|null $stream
     * @return RequestInterface
     */
    private function createApiRequest( string $method, string $uri, array $headers = [], ?StreamInterface $stream = null ): RequestInterface
    {
        $request = $this->requestFactory
            ->createRequest($method, $this->baseUrl . $uri);

        if ( $stream !== null ) {
            $request = $request->withBody( $stream );
        }

        foreach ( $headers as $key => $header ) {
            $request = $request->withHeader( $key, $header );
        }

        return $this->requestSigner->sign( $request );
    }


    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws TransportException
     */
    private function sendApiRequest( RequestInterface $request ): ResponseInterface
    {
        try {
            return $this->httpClient->sendRequest( $request );
        }
        catch ( ClientExceptionInterface $e ) {
            throw new TransportException( $e );
        }
    }


    /**
     * @param ResponseInterface $response
     * @return array
     * @throws BadResponseException
     */
    private function decodeResponse( ResponseInterface $response ): array
    {
        try {
            $result = json_decode( $response->getBody()->getContents(), true );
            if ( $result === null ) {
                throw new \Exception( json_last_error_msg() );
            }
            
            return $result;
        }
        catch ( \Throwable $e ) {
            throw new BadResponseException( $response, $e );
        }
    }
}
