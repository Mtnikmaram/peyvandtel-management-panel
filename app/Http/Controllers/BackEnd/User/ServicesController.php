<?php

namespace App\Http\Controllers\BackEnd\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackEnd\User\ServiceStoreRequest;
use App\Models\Service;
use App\Services\ServiceDTO;
use App\Services\ServiceFactory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ServicesController extends Controller
{
    /**
     * @OA\Get(
     *   tags={"Services"},
     *   path="/user/services/{serviceId}",
     *   summary="Services index",
     *   operationId="UserServicesIndex",
     *   security={{"sanctum":{}}},
     *   description="fetch a paginated list of a specific service",
     *   @OA\Parameter(
     *     name="serviceId",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       enum={"VoiceToText"},
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="from",
     *     in="query",
     *     description="it must contain a DateTime (Y/m/d H:i). The result will be filtered for those after this DateTime.",
     *     @OA\Schema(
     *       type="string",
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="payload",
     *     in="query",
     *     description="search in the payload. payload must contain this value",
     *     @OA\Schema(
     *       type="string",
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="integer",default=1)
     *   ),
     *   @OA\Response(
     *      response=200, 
     *      description="Success",
     *      @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="result",
     *          type="object",
     *          @OA\Property(
     *            property="data", 
     *            type="array", 
     *            @OA\Items(
     *              @OA\Schema(type="object")
     *            )
     *          ),
     *          allOf={
     *              @OA\Schema(ref="#/components/schemas/Pagination"),
     *          },
     *        )
     *     )
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @OA\JsonContent(
     *          ref="#/components/schemas/UnauthorizedErrorResponse"
     *      )
     *  ),
     * )
     */
    public function index(Request $request, string $service)
    {
        $user = $request->user();

        try {
            $serviceModel = Service::getClassByShownName($service);
            $repository = ServiceFactory::getServiceRepository($serviceModel, $user);
        } catch (Exception | InvalidArgumentException $e) {
            Log::critical('service repository', ['serviceName' => $service, "message" => $e->getMessage()]);
            return response()->apiError("service Repository", $e->getMessage());
        }

        if ($request->payload)
            $repository = $repository->setSearchAttribute('payload', $request->payload);

        if ($request->from) {
            $dateTime = Carbon::createFromFormat("Y/m/d H:i", $request->from)->startOfMinute();
            $repository = $repository->setSearchAttribute('from', $dateTime->format('Y-m-d H:i:s'));
        }

        return response()->json([
            "last_fetch" => now()->format("Y-m-d H:i"),
            "result" => $repository->paginatedList($request->page ?? 1)
        ]);
    }


    /**
     * @OA\Post(
     *   tags={"Services"},
     *   path="/user/services",
     *   summary="send a request for Sahab Part Speech To Text Service",
     *   operationId="UserServicesStore",
     *   security={{"sanctum":{}}},
     *   description="the `serviceId` and `attachments` keys are preserved. anything other than these 2 will be considered as payload and it will be available on further requests with named `payload`. **DO NOT send any sensitive data.**",
     *   @OA\Parameter(
     *     name="serviceId",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       enum={"VoiceToText"},
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="payload",
     *     in="query",
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

        try {
            $serviceId = Service::getClassByShownName($request->serviceId, true);
        } catch (Exception | InvalidArgumentException $e) {
            Log::critical('service store failed', ['request' => $request->all(), "message" => $e->getMessage()]);
        }


        $dto = new ServiceDTO(
            $serviceId,
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

    /**
     * @OA\Get(
     *   tags={"Services"},
     *   path="/user/services/{serviceId}/{id}",
     *   summary="Services show",
     *   operationId="UserServicesShow",
     *   security={{"sanctum":{}}},
     *   description="fetch a detail information of one a specific service",
     *   @OA\Parameter(
     *     name="serviceId",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       enum={"VoiceToText"},
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="payload",
     *     in="query",
     *     description="search in the payload",
     *     @OA\Schema(
     *       type="string",
     *     )
     *   ),
     *   @OA\Response(
     *      response=200, 
     *      description="Success",
     *      @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="result",
     *          type="object",
     *        )
     *     )
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Not Found",
     *      @OA\JsonContent(
     *          type="object",
     *      )
     *  ),
     *   @OA\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @OA\JsonContent(
     *          ref="#/components/schemas/UnauthorizedErrorResponse"
     *      )
     *  ),
     * )
     */
    public function show(Request $request, string $service, string $id)
    {
        $user = $request->user();

        try {
            $serviceModel = Service::getClassByShownName($service);
            $repository = ServiceFactory::getServiceRepository($serviceModel, $user);
        } catch (Exception | InvalidArgumentException $e) {
            Log::critical('service repository', ['serviceName' => $service, "message" => $e->getMessage()]);
            return response()->apiError("service Repository", $e->getMessage());
        }

        if ($request->payload)
            $repository = $repository->setSearchAttribute('payload', $request->payload);

        $result = $repository->show($id);
        if (!$result) return response()->noContent(Response::HTTP_NOT_FOUND);

        return response()->json([
            "result" => $result
        ]);
    }
}
