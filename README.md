sumsub-client
===============

![PHP 7.1](https://img.shields.io/badge/PHP-7.1-green.svg) 
![PHP 7.2](https://img.shields.io/badge/PHP-7.2-green.svg)
![PHP 7.3](https://img.shields.io/badge/PHP-7.3-green.svg)
![PHP 7.4](https://img.shields.io/badge/PHP-7.4-green.svg)
![PHP 8.0](https://img.shields.io/badge/PHP-8.0-green.svg)
![PHP 8.1](https://img.shields.io/badge/PHP-8.1-green.svg)

API client for sumsub.com

## Installation

```shell script
composer require y0zh/sumsub-client
```

## Client configuration

Client works with any [PSR-18 compatible HTTP client](https://packagist.org/providers/psr/http-client-implementation) and require [PSR-17 HTTP factory](https://packagist.org/providers/psr/http-factory-implementation).

```php
use alexeevdv\SumSub\Client;
use alexeevdv\SumSub\Request\RequestSigner;

$requestSigner = new RequestSigner('Your APP token', 'Your secret');

$client = new Client(
    $psr18HttpClient,
    $psr17HttpFactory,
    $requestSigner
);
```

## Getting SDKs access token

```php
use alexeevdv\SumSub\Request\AccessTokenRequest;

$externalUserId = 'some-id';
$levelName = 'some-level';
$ttlInSeconds = 3600;
$response = $client->getAccessToken(new AccessTokenRequest($externalUserId, $levelName, $ttlInSeconds));
$accessToken = $response->getToken();
```

## Getting share token

```php
use alexeevdv\SumSub\Request\ShareTokenRequest;

$applicantId = 'some-id';
$clientId = 'some-id';
$ttlInSeconds = 3600;
$response = $client->getShareToken( new ShareTokenRequest( $applicantId, $clientId, $ttlInSeconds ) );
$shareToken = $response->getToken();
```

## Getting applicant data by applicant id

```php
use alexeevdv\SumSub\Request\ApplicantDataRequest;

$applicantId = 'some-id';
$response = $client->getApplicantData(new ApplicantDataRequest($applicantId));
$applicantData = $response->asArray();
```

## Getting applicant data by external user id

```php
use alexeevdv\SumSub\Request\ApplicantDataRequest;

$externalUserId = 'some-id';
$response = $client->getApplicantData(new ApplicantDataRequest(null, $externalUserId));
$applicantData = $response->asArray();
```

## Resetting an applicant

```php
use alexeevdv\SumSub\Request\ResetApplicantRequest;

$applicantId = 'some-id';
$client->resetApplicant(new ResetApplicantRequest($applicantId));
```

## Getting applicant status

```php
use alexeevdv\SumSub\Request\ApplicantStatusRequest;

$applicantId = 'some-id';
$response = $client->getApplicantStatus(new ApplicantStatusRequest($applicantId));
$applicantStatus = $response->asArray();
```

## Getting document images

```php
use alexeevdv\SumSub\Request\DocumentImageRequest;

$inspectionId = 'some-id';
$imageId = '123';
$response = $client->getDocumentImages(new DocumentImageRequest($inspectionId, $imageId));
$stream = $response->asStream();
$conentType = $response->getContentType();
```

## Getting inspection checks

```php
use alexeevdv\SumSub\Request\InspectionChecksRequest;

$inspectionId = 'some-id';
$response = $client->getInspectionChecks(new InspectionChecksRequest($inspectionId));
$checksData = $response->asArray();
```
