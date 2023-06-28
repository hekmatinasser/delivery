<?php

namespace App\Models;

use App\Enums\LogActionsEnum;
use App\Enums\LogModelsEnum;
use App\Enums\LogUserTypesEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Store",
 *     title="store",
 *     description="Store model",
 *     @OA\Property(
 *         property="id",
 *         description="ID of the store",
 *         type="integer",
 *         format="int64"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         description="ID of the user who owns the store",
 *         type="integer",
 *         format="int64"
 *     ),
 *     @OA\Property(
 *         property="category_id",
 *         description="ID of the category",
 *         type="integer",
 *         format="int64"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         description="The store's category",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         description="The store's name",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="postCode",
 *         description="The store's postal code",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="address",
 *         description="The store's address",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="areaType",
 *         description="area type of the store",
 *         type="string",
 *         enum={"RENT", "PROPERTY"}
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         description="The store's phone number",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="lat",
 *         description="The store's Latitude",
 *         type="number",
 *         format="float",
 *     ),
 *     @OA\Property(
 *         property="lang",
 *         description="The store's Longitude",
 *         type="number",
 *         format="float",
 *     ),
 * )
 */

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'store';
    protected $fillable = [
        'user_id',
        'category_id',
        'neighborhood_id',
        'name',
        'address',
        'postCode',
        'lat',
        'lang',
        'areaType',
        'phone',
    ];

    public function areaTypes()
    {
        return [
            0 => 'RENT',
            1 => 'OWNERSHIP',
        ];
    }

    protected function areaType(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) =>  $this->areaTypes()[$value],
            set: fn (string $value) => array_search($value, $this->areaTypes()),
        );
    }


    protected function categoryName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) =>  $this->category()->get(),
        );
    }

    /** Log changes to Store model when updating Store.
     *
     * @param User $user The User model.
     * @param array $oldData The original data for the store model.
     * @param array $newData The updated data for the store model.
     * @return void
     */
    function logStoreModelChanges(User $user, array $oldData, array $newData): void
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
            Log::store($user->userType == '0' ? LogUserTypesEnum::USER : LogUserTypesEnum::ADMIN, $user->id, LogModelsEnum::STORE, LogActionsEnum::EDIT, json_encode($changes));
        }
    }

    public function category()
    {
        return $this->belongsTo(StoreCategory::class, 'category_id')->select(['id', 'title']);
    }

    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class, 'neighborhood_id')->select(['id', 'name', 'code', 'description']);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'family']);
    }
}
