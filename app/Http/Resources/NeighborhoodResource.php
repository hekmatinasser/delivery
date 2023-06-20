<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'user'=>$this->user->name,
            'user_email'=>$this->user->email,
            'name'=>$this->name,
            'code'=>$this->code,
            'status'=>$this->status,
            'created_at'=>Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at'=>Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
