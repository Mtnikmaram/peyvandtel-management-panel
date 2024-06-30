<?php

namespace App\Http\Controllers\BackEnd\Peyvandtel;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackEnd\Peyvandtel\ServicePricesStoreRequest;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Services\ServiceException;
use App\Services\ServiceFactory;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

class ServicePricesController extends Controller
{
    /**
     * @OA\Get(
     *   tags={"Service Prices"},
     *   path="/peyvandtel/servicePrices",
     *   summary="ServicePrices index",
     *   operationId="PeyvandtelAdminServicePricesIndex",
     *   description="returns a paginated list of service prices. can be filtered",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="serviceId",
     *     in="query",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="users",
     *          type="object",
     *          @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ServicePrice")),
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
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $prices = ServicePrice::query()
            ->when($request->serviceId, fn($q) => $q->where('service_id', $request->serviceId))
            ->paginate(20);

        return response()->json([
            "prices" => $prices
        ]);
    }

    /**
     * @OA\Post(
     *   tags={"Service Prices"},
     *   path="/peyvandtel/servicePrices",
     *   summary="ServicePrices store",
     *   description="create a new price for an existing service.",
     *   operationId="PeyvandtelAdminServicePricesStore",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="serviceId",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="amount",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     description="Object containing an array of each service additional information",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="setting",
     *         type="array",
     *         @OA\Items(
     *          type="object",
     *          @OA\Property(property="key", type="string", example="each_second"),
     *          @OA\Property(property="value", type="string", example=10),
     *        )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *      response=201,
     *      description="Success. Created.",
     *      @OA\JsonContent(
     *        type="object",
     *     )
     *  ),
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
    public function store(ServicePricesStoreRequest $request)
    {
        /** @var Service $service */
        $service = Service::query()->find($request->serviceId);

        //validate by service
        try {
            ServiceFactory::getValidator($service)->validate($service, $request->amount, $request->setting);
        } catch (ServiceException $e) {
            return response()->apiError($e->getErrorName(), $e->getMessage());
        }

        $service
            ->price()
            ->create([
                "amount" => $request->amount,
                "setting" => $request->setting
            ]);

        return response()->noContent(Response::HTTP_CREATED);
    }
}
