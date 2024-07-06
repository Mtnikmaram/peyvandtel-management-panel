<?php

namespace App\Http\Controllers\BackEnd\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackEnd\User\ServiceStoreRequest;
use App\Services\ServiceDTO;
use App\Services\ServiceFactory;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ServicesController extends Controller
{
    /**
     * @OA\Post(
     *   tags={"Services"},
     *   path="/user/services",
     *   summary="send a request for Sahab Part Speech To Text Service",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="serviceId",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       enum={"SahabPartAISpeechToText"},
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="payload",
     *     in="query",
     *     description="the `serviceId` and `attachments` keys are preserved. anything other than these 2 will be considered as payload and it will be available on further requests with the key `payload`",
     *     @OA\Schema(
     *       type="object",
     *       @OA\AdditionalProperties(
     *         type="string",
     *       )
     *     )
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(
     *           property="attachments[]",
     *           type="array",
     *           @OA\Items(
     *             type="file"
     *           )
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *      response=201, 
     *      description="Success. Created.",
     *      @OA\JsonContent(
     *        type="object",
     *     )
     *   ),
     *   @OA\Response(
     *      response=422,
     *      description="Validation error",
     *      @OA\JsonContent(
     *          ref="#/components/schemas/ValidationErrorResponse"
     *      )
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @OA\JsonContent(
     *          ref="#/components/schemas/UnauthorizedErrorResponse"
     *      )
     *  ),
     * )
     */
    public function store(ServiceStoreRequest $request)
    {
        $payload = $request->except('serviceId', 'attachments');

        $dto = new ServiceDTO(
            $request->serviceId,
            $request->user(),
            $payload,
            $request->attachments ?: []
        );

        try {
            ServiceFactory::execute($dto);
        } catch (Exception $e) {
            Log::critical("service store failed", ["message" => $e->getMessage(), "request" => $request->all, "user" => $request->user(), "file" => $e->getFile(), "line" => $e->getLine()]);
            return response()->apiError("exception", $e->getMessage());
        }

        return response()->noContent(Response::HTTP_CREATED);
    }
}
