<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 *
 * @OA\Schema(
 *     schema="NeighborhoodResource",
 *     title="NeighborhoodResource",
 *     description="Neighborhood resource",
 *     @OA\Property(
 *         property="user",
 *         type="string",
 *         description="The name of the user who created the neighborhood"
 *     ),
 *     @OA\Property(
 *         property="user_email",
 *         type="string",
 *         description="The email of the user who created the neighborhood"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the neighborhood"
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         description="The code of the neighborhood"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="The status of the neighborhood"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         description="The date and time when the neighborhood was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         description="The date and time when the neighborhood was last updated"
 *     )
 * )
 */
class NeighborhoodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => $this->user->name,
            'user_email' => $this->user->email,
            'name' => $this->name,
            'code' => $this->code,
            'status' => $this->status,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
