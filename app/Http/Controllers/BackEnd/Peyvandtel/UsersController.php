<?php

namespace App\Http\Controllers\BackEnd\Peyvandtel;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackEnd\Peyvandtel\UsersStoreRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    /**
     * @OA\Get(
     *   tags={"Users"},
     *   path="/peyvandtel/users",
     *   summary="Users index",
     *   operationId="PeyvandtelAdminUsersIndex",
     *   description="returns a paginated list of users. can be filtered",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="integer",default=1)
     *   ),
     *   @OA\Parameter(
     *     name="phone",
     *     in="query",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="username",
     *     in="query",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="maxCredit",
     *     in="query",
     *     description="filter users that their credit are fewer than this amount",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *      response=200, 
     *      description="Success",
     *      @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="users",
     *          type="object",
     *          @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *          allOf={
     *              @OA\Schema(ref="#/components/schemas/Pagination"),
     *          },
     *        )
     *     )
     *   )
     * )
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->phone, fn ($q) => $q->where('phone', 'LIKE', "%$request->phone%"))
            ->when($request->username, fn ($q) => $q->where('username', 'LIKE', "%$request->username%"))
            ->when($request->name, fn ($q) => $q->where('name', 'LIKE', "%$request->name%"))
            ->when($request->maxCredit, fn ($q) => $q->where('credit', '<=', $request->maxCredit))
            ->paginate(20);

        return response()->json([
            "users" => $users
        ]);
    }

    /**
     * @OA\Post(
     *   tags={"Users"},
     *   path="/peyvandtel/users",
     *   summary="Users Store",
     *   operationId="PeyvandtelAdminUsersStore",
     *   description="if password is provided in parameters, the response will be empty, otherwise the password will be generated and be shown in the request one time only.",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="username",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="phone",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="credit_threshold",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="password",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="password_confirmation",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *      response=201, 
     *      description="Success. Created.",
     *      @OA\JsonContent(
     *        type="object",
     *     )
     *  ),
     *  @OA\Response(
     *      response=200, 
     *      description="Success.",
     *      @OA\JsonContent(
     *        type="object",
     *        @OA\Property(
     *          property="password",
     *          type="string",
     *        )
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
     * )
     */
    public function store(UsersStoreRequest $request)
    {
        $hasPassword = $request->has('password') && $request->password;
        $password = $hasPassword ? $request->password : Str::password(length: 12, symbols: false);


        $data = $request->only(["username", "phone", "name", "credit_threshold"]);
        $data["password"] = Hash::make($password);
        User::query()->create($data);

        if ($hasPassword)
            return response()->noContent(Response::HTTP_CREATED);
        else
            return response()->json([
                "password" => $password
            ]);
    }
}
