<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @OA\Schema(
     *     schema="SuccessResponse",
     *     title="Success Response",
     *     description="Schema for a successful API response",
     *     @OA\Property(
     *         property="success",
     *         description="Indicates if the request was successful",
     *         type="boolean",
     *         example=true
     *     ),
     *     @OA\Property(
     *         property="data",
     *         description="Response data",
     *         type="object",
     *         example={}
     *     ),
     *     @OA\Property(
     *         property="message",
     *         description="Response message",
     *         type="string",
     *         example="Success"
     *     )
     * )

     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message = ''): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @OA\Schema(
     *     schema="ErrorResponse",
     *     title="Error Response",
     *     description="Schema for an error API response",
     *     @OA\Property(
     *         property="success",
     *         description="Indicates if the request was successful",
     *         type="boolean",
     *         example=false
     *     ),
     *     @OA\Property(
     *         property="message",
     *         description="Error message",
     *         type="string",
     *         example="Error"
     *     )
     * )
     *
     *
     * @OA\Schema(
     *     schema="ErrorValidation",
     *     title="Error Validation",
     *     description="Schema for an error Validation API response",
     *     @OA\Property(
     *         property="message",
     *         description="Error message",
     *         type="string",
     *         example="Error"
     *     ),
     *     @OA\Property(
     *         property="errors",
     *         description="Error data",
     *         type="object",
     *         @OA\Property(
     *                 property="fieldName",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string",
     *                     example={
     *                         "field is wrong.",
     *                     }
     *                 )
     *             )
     *     )
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];


        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }
}
