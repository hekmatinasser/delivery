<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripChangesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status'=>true,
            'trip_code'=>$this->trip->trip_code,
            'user'=>$this->user->name,
            'description'=>$this->description,
            'changes'=>$this->changes,
            'status_determining_time'=>$this->status_determining_time,
            'created_at'=>Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at'=>Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
