<?php

namespace App\Http\Controllers\BackEnd\Peyvandtel;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackEnd\Peyvandtel\ServicesSetTokenCredentialRequest;
use App\Http\Requests\BackEnd\Peyvandtel\ServicesSetUsernamePasswordCredentialRequest;
use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    /**
     * @OA\Get(
     *   tags={"Services"},
     *   path="/peyvandtel/services",
     *   summary="Services index",
     *   description="fetch list of all available services with its information",
     *   security={{"sanctum":{}}},
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
     *          property="services",
     *          type="object",
     *          @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
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
    public function index()
    {
        $services = Service::query()
            ->paginate(30);

        return response()->json([
            "services" => $services
        ]);
    }

    /**
     * @OA\Put(
     *   tags={"Services"},
     *   path="/peyvandtel/services/{serviceId}/credential/token",
     *   summary="Services set credential",
     *   description="set credential for token based services",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="serviceId",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="token",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *      response=204, 
     *      description="Success. No Content.",
     *      @OA\JsonContent(
     *        type="object",
     *     )
     *  ),
     *  @OA\Response(
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
     *  @OA\Response(
     *      response=404,
     *      description="Not Found",
     *      @OA\JsonContent(
     *          type="object",
     *      )
     *  ),
     * )
     */
    public function setTokenCredential(ServicesSetTokenCredentialRequest $request, Service $service)
    {
        $service->credential = $request->token;
        $service->save();

        return response()->noContent();
    }

    /**
     * @OA\Put(
     *   tags={"Services"},
     *   path="/peyvandtel/services/{serviceId}/credential/usernamePassword",
     *   summary="Services set credential",
     *   description="set credential for username/password based services",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="serviceId",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="username",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="password",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *      response=204, 
     *      description="Success. No Content.",
     *      @OA\JsonContent(
     *        type="object",
     *     )
     *  ),
     *  @OA\Response(
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
     *  @OA\Response(
     *      response=404,
     *      description="Not Found",
     *      @OA\JsonContent(
     *          type="object",
     *      )
     *  ),
     * )
     */
    public function setUsernamePasswordCredential(ServicesSetUsernamePasswordCredentialRequest $request, Service $service)
    {
        $credential = Service::concatUsernameAndPassword($request->username, $request->password);
        $service->credential = $credential;
        $service->save();

        return response()->noContent();
    }

    /**
     * @OA\Put(
     *   tags={"Services"},
     *   path="/peyvandtel/services/{serviceId}/toggleActive",
     *   summary="Services toggle active state",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="serviceId",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *      response=200, 
     *      description="Success.",
     *      @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="newState",
     *          type="boolean",
     *          example="true",
     *        )
     *     )
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @OA\JsonContent(
     *          ref="#/components/schemas/UnauthorizedErrorResponse"
     *      )
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Not Found",
     *      @OA\JsonContent(
     *          type="object",
     *      )
     *  ),
     * )
     */
    public function toggleActiveState(Service $service)
    {
        $service->active = !$service->active;
        $service->save();
        $service->refresh();

        return response()->json([
            "newState" => $service->active
        ]);
    }
}
