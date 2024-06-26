<?php

namespace App\Http\Controllers\BackEnd\Peyvandtel;

use App\Http\Controllers\Controller;
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
}
