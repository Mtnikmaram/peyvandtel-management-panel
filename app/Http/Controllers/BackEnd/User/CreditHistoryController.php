<?php

namespace App\Http\Controllers\BackEnd\User;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class CreditHistoryController extends Controller
{
    /**
     * @OA\Get(
     *   tags={"CreditHistories"},
     *   path="/user/creditHistory",
     *   summary="CreditHistory index",
     *   operationId="UserCreditHistoryIndex",
     *   security={{"sanctum":{}}},
     *   description="fetch a paginated list of a user's credit history",
     *   @OA\Parameter(
     *     name="from",
     *     in="query",
     *     description="it must contain a Date (Y/m/d)",
     *     @OA\Schema(
     *       type="string",
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="to",
     *     in="query",
     *     description="it must contain a Date (Y/m/d)",
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
     *          property="creditHistory",
     *          type="object",
     *          @OA\Property(
     *            property="data", 
     *            type="array", 
     *            @OA\Items(
     *              @OA\Schema(ref="#/components/schemas/UserCreditHistory")
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
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();


        try {
            $from = $request->from ? Carbon::createFromFormat("Y/m/d", $request->from) : null;
        } catch (Exception) {
            $from = null;
        }

        try {
            $to = $request->to ? Carbon::createFromFormat("Y/m/d", $request->to) : null;
            // dd($from,$to);
        } catch (Exception) {
            $to = null;
        }

        $histories = $user
            ->creditHistories()
            ->latest()
            ->when($from, fn ($q) => $q->whereDate("created_at", ">=", $from->format("Y-m-d")))
            ->when($to && !$to->isFuture(), fn ($q) => $q->whereDate("created_at", "<=", $to->format("Y-m-d")))
            ->paginate(15);
        $histories
            ->getCollection()
            ->each->makeHidden(['id']);
        return response()->json([
            "creditHistory" => $histories
        ]);
    }
}
