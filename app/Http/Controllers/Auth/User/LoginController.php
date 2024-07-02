<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\User\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * @OA\Post(
     *   tags={"Authentication"},
     *   path="/user/auth/login",
     *   summary="login user via username & password",
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
     *   @OA\Parameter(
     *     name="model",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(
     *              property="token",
     *              type="string",
     *          )
     *         )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/ValidationErrorResponse"
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="No User available",
     *          @OA\JsonContent(type="object")
     *      )
     * )
     */
    public function login(LoginRequest $request)
    {
        $user = User::query()
            ->where('username', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password))
            return response()->apiError('credentials', "username or password is not correct");

        $user->tokens()->where('name', $request->model)->delete();
        $token = $user->createToken($request->model);

        return response()->json([
            "token" => $token->plainTextToken
        ]);
    }
}
