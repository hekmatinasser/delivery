<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateNeighborhoodRequest;
use App\Http\Requests\Admin\StoreNeighborhoodFareRequest;
use App\Http\Requests\Neighborhood\GetNeighborhoodFareRequest;
use App\Models\InterNeighborhoodFare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class InterNeighborhoodFareController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/neighborhood/fare/calculator",
     *     summary="Get inter-neighborhood fare",
     *     description="Get the fare for traveling between two neighborhoods",
     *     tags={"Neighborhood"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *          name="origin",
     *          in="query",
     *          required=true,
     *          description="The origin neighborhood id",
     *          @OA\Schema(
     *              type="number"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="destination",
     *          in="query",
     *          required=true,
     *          description="The destination neighborhood id",
     *          @OA\Schema(
     *              type="number"
     *          )
     *     ),

     *     @OA\Response(
     *         response=200,
     *         description="Inter neighborhood fare calculated successfully",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     )
     * )
     */
    public function InterNeighborhoodFare(GetNeighborhoodFareRequest $request)
    {
        $original = $request->origin . '-' . $request->destination;
        $interNeighborhoodFare  = InterNeighborhoodFare::orWhere('original', '=', $original)
            ->orWhere('reverse', '=', $original)
            ->first();

        if (!$interNeighborhoodFare) {
            $message = 'این مسیر قیمت گزاری نشده است.';
            return $this->sendError($message, '', 400);
        }

        return $this->sendResponse($interNeighborhoodFare, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/neighborhood/fare",
     *     summary="Calculate inter neighborhood fare",
     *     tags={"Neighborhood"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body for calculating inter neighborhood fare",
     *         @OA\JsonContent(ref="#/components/schemas/StoreNeighborhoodFareRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inter neighborhood fare calculated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inter Neighborhood Fare Created Successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     )
     * )
     */
    public function calculatingInterNeighborhoodFare(StoreNeighborhoodFareRequest $request)
    {
        $origin = $request->origin;
        $destination = $request->destination;
        if ($this->CheckRepetitiveRecored($origin, $destination)) {
            $message = 'این مسیر قبلا قیمت گزاری شده است.';
            return $this->sendError($message, '', 400);
        }

        $original = $origin . '-' . $destination;
        $reverse = $destination . '-' . $origin;

        InterNeighborhoodFare::create([
            'user_id' => auth()->user()->id,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'original' => $original,
            'reverse' => $reverse,
            'fare' => $request->fare,
        ]);

        return $this->sendResponse('', 'کرایه با موفقیت ایجاد شد');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/neighborhood/fare/edit/{interNeighborhoodFare}",
     *     summary="Edit an inter-neighborhood fare",
     *     description="Edit an existing inter-neighborhood fare record.",
     *     operationId="editInterNeighborhoodFare",
     *     tags={"Neighborhood"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="interNeighborhoodFare",
     *         in="path",
     *         description="ID of the inter-neighborhood fare to edit",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *         ),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body for updating an inter-neighborhood fare",
     *         @OA\JsonContent(
     *             required={
     *                 "origin",
     *                 "destination",
     *                 "fare"
     *             },
     *             @OA\Property(
     *                 property="origin",
     *                 type="integer",
     *                 description="The ID of the origin neighborhood",
     *             ),
     *             @OA\Property(
     *                 property="destination",
     *                 type="integer",
     *                 description="The ID of the destination neighborhood",
     *             ),
     *             @OA\Property(
     *                 property="fare",
     *                 type="integer",
     *                 description="The new fare for the inter-neighborhood",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inter-neighborhood fare updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Inter-neighborhood fare updated successfully",
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inter-neighborhood fare not found",
     *     ),
     * )
     */
    public function editInterNeighborhoodFare(InterNeighborhoodFare $interNeighborhoodFare, Request $request)
    {
        $origin = $request->origin;
        $destination = $request->destination;

        $original = $origin . '-' . $destination;
        $reverse = $destination . '-' . $origin;

        $interNeighborhoodFare->update([
            'user_id' => auth()->user()->id,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'original' => $original,
            'reverse' => $reverse,
            'fare' => $request->fare,
        ]);


        return $this->sendResponse('', 'کرایه با موفقیت اصلاح شد');
    }

    protected function CheckRepetitiveRecored(string $origin, string $destination)
    {
        $original = $origin . '-' . $destination;
        $record = InterNeighborhoodFare::orWhere('original', '=', $original)
            ->orWhere('reverse', '=', $original)
            ->first();
        if ($record) {
            return true;
        }
    }
}
