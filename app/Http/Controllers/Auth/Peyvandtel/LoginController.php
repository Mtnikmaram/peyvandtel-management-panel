<?php

namespace App\Http\Controllers\Auth\Peyvandtel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Peyvandtel\LoginRequest;
use App\Models\PeyvandtelAdmin;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * @OA\Post(
     *      path="/peyvandtel/auth/login",
     *      operationId="PeyvandtelAdminLogin",
     *      tags={"Authentication"},
     *      summary="Peyvandtel admin login",
     *      description="authenticate admin and returns token",
     *      @OA\Parameter(
     *          name="username",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="model",
     *          in="query",
     *          description="model of the device",
     *          required=true,
     *          @OA\Schema(
     *            type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                property="token",
     *                type="string",
     *              ),
     *              @OA\Property(
     *                property="admin",
     *                ref="#/components/schemas/PeyvandtelAdmin"
     *              )
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
     *          description="No Peyvandtel Admin available",
     *          @OA\JsonContent(type="object")
     *      )
     * )
     */
    public function login(LoginRequest $request)
    {
        $admin = PeyvandtelAdmin::query()
            ->where('username', $request->username)
            ->firstOrFail();

        if (!Hash::check($request->password, $admin->password))
            return response()->apiError('credentials', 'اطلاعات وارد شده صحیح نمی‌باشد');

        $token = $admin->createToken($request->model);

        return response()->json([
            "token" => $token->plainTextToken,
            "user" => $admin,
        ]);
    }
}
