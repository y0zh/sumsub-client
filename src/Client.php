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
    Psr\Http\Message\ResponseInterface;

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
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->requestSigner = $requestSigner;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getAccessToken(AccessTokenRequest $request): AccessTokenResponse
    {
        $queryParams = [
            'userId' => $request->getUserId(),
            'levelName' => $request->getLevelName(),
        ];

        if ($request->getTtlInSecs() !== null) {
            $queryParams['ttlInSecs'] = $request->getTtlInSecs();
        }

        $url = sprintf('%s/resources/accessTokens?%s', $this->baseUrl, http_build_query($queryParams));

        $httpRequest = $this->createApiRequest('POST', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        $decodedResponse = $this->decodeResponse($httpResponse);

        return new AccessTokenResponse($decodedResponse['token'], $decodedResponse['userId']);
    }


    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getShareToken( ShareTokenRequest $request ): ShareTokenResponse
    {
        $queryParams = [
            'applicantId' => $request->getApplicantId(),
            'forClientId' => $request->getClientId(),
        ];

        if ( $request->getTtlInSecs() !== null ) {
            $queryParams['ttlInSecs'] = $request->getTtlInSecs();
        }

        $url = sprintf( '%s/resources/accessTokens/shareToken?%s', $this->baseUrl, http_build_query( $queryParams ) );

        $httpRequest = $this->createApiRequest( 'POST', $url );
        $httpResponse = $this->sendApiRequest( $httpRequest );

        if ( $httpResponse->getStatusCode() !== 200 ) {
            throw new BadResponseException($httpResponse);
        }

        $decodedResponse = $this->decodeResponse( $httpResponse );

        return new ShareTokenResponse( $decodedResponse['token'], $decodedResponse['forClientId'] );
    }
    

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getApplicantData(ApplicantDataRequest $request): ApplicantDataResponse
    {
        if ($request->getApplicantId() !== null) {
            $url = $this->baseUrl . '/resources/applicants/' . $request->getApplicantId() . '/one';
        } else {
            $url = $this->baseUrl . '/resources/applicants/-;externalUserId=' . $request->getExternalUserId() . '/one';
        }

        $httpRequest = $this->createApiRequest('GET', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new ApplicantDataResponse($this->decodeResponse($httpResponse));
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function resetApplicant(ResetApplicantRequest $request): void
    {
        $url = $this->baseUrl . '/resources/applicants/' . $request->getApplicantId() . '/reset';

        $httpRequest = $this->createApiRequest('POST', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        $decodedResponse = $this->decodeResponse($httpResponse);
        $isOk = ($decodedResponse['ok'] ?? 0) === 1;

        if (! $isOk) {
            throw new BadResponseException($httpResponse);
        }
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getApplicantStatus(ApplicantStatusRequest $request): ApplicantStatusResponse
    {
        $url = $this->baseUrl . '/resources/applicants/' . $request->getApplicantId() . '/requiredIdDocsStatus';

        $httpRequest = $this->createApiRequest('GET', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new ApplicantStatusResponse($this->decodeResponse($httpResponse));
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getDocumentImage(DocumentImageRequest $request): DocumentImageResponse
    {
        $url = $this->baseUrl . '/resources/inspections/' . $request->getInspectionId() . '/resources/' . $request->getImageId();

        $httpRequest = $this->createApiRequest('GET', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new DocumentImageResponse($httpResponse);
    }

    public function getInspectionChecks(InspectionChecksRequest $request): InspectionChecksResponse
    {
        $url = $this->baseUrl . '/resources/inspections/' . $request->getInspectionId() . '/checks';

        $httpRequest = $this->createApiRequest('GET', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new InspectionChecksResponse($this->decodeResponse($httpResponse));
    }

    private function createApiRequest(string $method, string $uri): RequestInterface
    {
        $httpRequest = $this->requestFactory
            ->createRequest($method, $uri)
            ->withHeader('Accept', 'application/json');
        return $this->requestSigner->sign($httpRequest);
    }

    /**
     * @throws TransportException
     */
    private function sendApiRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new TransportException($e);
        }
    }

    /**
     * @throws BadResponseException
     */
    private function decodeResponse(ResponseInterface $response): array
    {
        try {
            $result = json_decode($response->getBody()->getContents(), true);
            if ($result === null) {
                throw new \Exception(json_last_error_msg());
            }
            return $result;
        } catch (\Throwable $e) {
            throw new BadResponseException($response, $e);
        }
    }
}
