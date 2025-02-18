<?php

declare(strict_types=1);

namespace alexeevdv\SumSub;

use alexeevdv\SumSub\Exception\Exception;
use alexeevdv\SumSub\Request\AccessTokenRequest;
use alexeevdv\SumSub\Request\ApplicantDataRequest;
use alexeevdv\SumSub\Request\ApplicantStatusRequest;
use alexeevdv\SumSub\Request\DocumentImageRequest;
use alexeevdv\SumSub\Request\InspectionChecksRequest;
use alexeevdv\SumSub\Request\ResetApplicantRequest;
use alexeevdv\SumSub\Response\AccessTokenResponse;
use alexeevdv\SumSub\Response\ApplicantDataResponse;
use alexeevdv\SumSub\Response\ApplicantStatusResponse;
use alexeevdv\SumSub\Response\DocumentImageResponse;
use alexeevdv\SumSub\Response\InspectionChecksResponse;

interface ClientInterface
{
    /**
     * Get access token for SDKs
     *
     * @see https://docs.sumsub.com/reference/generate-access-token
     * @throws Exception
     */
    public function getAccessToken(AccessTokenRequest $request): AccessTokenResponse;

    /**
     * Get share token
     *
     * @see https://docs.sumsub.com/reference/generate-share-token
     * @throws Exception
     */
    public function getShareToken( ShareTokenRequest $request ): ShareTokenResponse;

    /**
     * Get applicant data
     *
     * @see https://docs.sumsub.com/reference/get-applicant-data
     * @throws Exception
     */
    public function getApplicantData(ApplicantDataRequest $request): ApplicantDataResponse;

    /**
     * Resetting an applicant
     *
     * @see https://docs.sumsub.com/reference/reset-applicant
     * @throws Exception
     */
    public function resetApplicant(ResetApplicantRequest $request): void;

    /**
     * Get applicant status
     *
     * @see https://docs.sumsub.com/reference/get-status-of-verification-steps
     * @throws Exception
     */
    public function getApplicantStatus(ApplicantStatusRequest $request): ApplicantStatusResponse;

    /**
     * Get document images
     *
     * @see https://docs.sumsub.com/reference/get-document-images
     * @throws Exception
     */
    public function getDocumentImage(DocumentImageRequest $request): DocumentImageResponse;

    /**
     * Get inspection checks
     *
     * @throws Exception
     */
    public function getInspectionChecks(InspectionChecksRequest $request): InspectionChecksResponse;
}
