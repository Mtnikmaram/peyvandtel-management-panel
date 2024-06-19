<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Peyvandtel Management Panel",
 *     description="Peyvandtel Management Panel Api Documentation",
 *     @OA\Contact(
 *         name="Peyvandtel",
 *         email="info@peyvandtel.ir"
 *     ),
 * ),
 * @OA\Server(
 *     url="/api",
 * ),
 * @OAS\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="http",
 *      scheme="bearer"
 * )
 * @OA\Schema(
 *  schema="ValidationErrorResponse",
 *  @OA\Property(
 *      property="message",
 *      type="string",
 *      example="The propertyName is required. (and 1 more error)",
 *      description="the first error message and it also contains the count of other errors occurred"
 *  ),
 *  @OA\Property(
 *      property="errors", 
 *      type="object",
 *      @OA\Property(
 *          property="propertyName", 
 *          type="array", 
 *          collectionFormat="multi",
 *          @OA\Items(
 *              type="string",
 *              example="The propertyName is required.",
 *          )
 *          )
 *      )
 *  )
 *  @OA\Schema(
 *      schema="UnauthorizedErrorResponse",
 *      @OA\Property(
 *          property="message",
 *          type="string",
 *          example="Unauthenticated.",
 *      ),
 *  )
 *
 */
abstract class Controller
{
    //
}
