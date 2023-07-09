<?php

namespace App\Models;

use App\Enums\LogActionsEnum;
use App\Enums\LogModelsEnum;
use App\Enums\LogUserTypesEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use LaravelLang\Publisher\Constants\Types;

/**
 * @OA\Schema(
 *     schema="Vehicle",
 *     title="Vehicle",
 *     description="Vehicle model",
 *     @OA\Property(
 *         property="id",
 *         description="ID of the vehicle",
 *         type="integer",
 *         format="int64"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         description="ID of the user who owns the vehicle",
 *         type="integer",
 *         format="int64"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         description="Type of the vehicle",
 *         type="string",
 *         enum={"MOTOR", "CAR"}
 *     ),
 *     @OA\Property(
 *         property="brand",
 *         description="Brand of the vehicle",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="pelak",
 *         description="License plate number of the vehicle",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="color",
 *         description="Color of the vehicle",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="model",
 *         description="Model of the vehicle",
 *         type="string"
 *     )
 * )
 */
class Vehicle extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'vehicle';
    protected $fillable = [
        'user_id',
        'type',
        'brand',
        'pelak',
        'color',
        'model',
    ];

    public function types()
    {
        return [
            0 => 'MOTOR',
            1 => 'CAR',
        ];
    }

    protected function type(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) =>  $this->types()[$value],
            set: fn (string $value) => array_search($value, $this->types()),
        );
    }


    /** Log changes to Vehicle model when updating Vehicle.
     *
     * @param User $user The User model.
     * @param array $oldData The original data for the vehicle model.
     * @param array $newData The updated data for the vehicle model.
     * @return void
     */
    function logVehicleModelChanges(User $user, array $oldData, array $newData): void
    {
        $changes = [];

        foreach ($newData as $key => $value) {
            if ($value !== $oldData[$key]) {
                $changes[$key] = [
                    'old' => $oldData[$key],
                    'new' => $value,
                ];
            }
        }

        if (!empty($changes)) {
            Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::VEHICLE, LogActionsEnum::EDIT, json_encode($changes));
        }
    }


    public function user()
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'family']);
    }

    public function neighborhoodsAvailable()
    {
        return $this->hasMany(NeighborhoodsAvailable::class)->with('neighborhood', function ($query) {
            return $query->select(['id', 'code', 'name'])->get();
        });
    }

    public function storesAvailable()
    {
        return $this->hasMany(StoreAvailable::class)
            ->where(function ($query) {
                $query->whereNull('expire')
                    ->orWhereDate('expire', '>', now());
            })
            ->whereNotIn('store_id', $this->storesBlocked()->pluck('store_id'))
            ->with('store', function ($query) {
                return $query->select(['*'])->get();
            });
    }


    public function storesBlocked()
    {
        return $this->hasMany(StoresBlocked::class)->with('store', function ($query) {
            return $query->select(['*'])->get();
        })
            ->where(function ($query) {
                $query->whereNull('expire')
                    ->orWhereDate('expire', '>', now());
            });
    }
}
