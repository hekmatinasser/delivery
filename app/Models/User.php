<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\LogActionsEnum;
use App\Enums\LogModelsEnum;
use App\Enums\LogUserTypesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="A user object",
 *     @OA\Property(property="id", type="integer", description="The user's ID"),
 *     @OA\Property(property="name", type="string", description="The user's name"),
 *     @OA\Property(property="family", type="string", description="The user's family name"),
 *     @OA\Property(property="mobile", type="string", description="The user's mobile number"),
 *     @OA\Property(property="nationalCode", type="string", description="The user's national code"),
 *     @OA\Property(property="nationalPhoto", type="string", description="The user's national photo"),
 *     @OA\Property(property="email", type="string", description="The user's email address"),
 *     @OA\Property(property="status", type="integer", description="The user's status"),
 *     @OA\Property(property="unValidCodeCount", type="integer", description="The user's unvalidated code count"),
 *     @OA\Property(property="address", type="string", description="The user's address"),
 *     @OA\Property(property="postCode", type="string", description="The user's postal code"),
 *     @OA\Property(property="phone", type="string", description="The user's phone number"),
 *     @OA\Property(property="userType", type="string", description="The user's type (0 => customer or 2 => admin)"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The date/time the user was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The date/time the user was last updated"),
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'family',
        'mobile',
        'nationalCode',
        'nationalPhoto',
        'email',
        'password',
        'status',
        'unValidCodeCount',
        'address',
        'postCode',
        'phone',
        'userType',
    ];

    public function userTypes()
    {
        return [
            0 => 'customer',
            1 => 'admin',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function abilites()
    {
        return $this->belongsToMany(Role::class)
            ->join('permission_role', 'permission_role.role_id', 'roles.id')
            ->join('permissions', 'permissions.id', 'permission_role.permission_id')
            ->select('permissions.name');
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class)->with('permissions');
    }

    /**
     * Get the user's wallet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     *
     * @OA\Property(
     *     property="wallet",
     *     type="object",
     *     ref="#/components/schemas/Wallet"
     * )
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get the user's coin wallet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     *
     * @OA\Property(
     *     property="coinWallet",
     *     type="object",
     *     ref="#/components/schemas/CoinWallet"
     * )
     */
    public function coinWallet()
    {
        return $this->hasOne(CoinWallet::class);
    }

    /**
     * Get the user's vehicle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     *
     * @OA\Property(
     *     property="vehicle",
     *     type="object",
     *     ref="#/components/schemas/Vehicle"
     * )
     */
    public function vehicle()
    {
        return $this->hasOne(Vehicle::class);
    }

    /**
     * Get the user's store.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     *
     * @OA\Property(
     *     property="store",
     *     type="object",
     *     ref="#/components/schemas/Store"
     * )
     */
    public function store()
    {
        return $this->hasOne(Store::class)->with(['category', 'neighborhood']);
    }

    /**
     * Find a user by mobile number.
     *
     * @param string $mobile The user's mobile number
     *
     * @return User|null
     *
     */
    public static function findByMobile(string $mobile): ?User
    {
        return static::where('mobile', $mobile)->first();
    }

    /**
     * Retrieves a user by their activation code, if it is valid and not expired.
     *
     * @param string $activationCode The activation code to search for.
     * @return User|null The user associated with the activation code, or null if not found or expired.
     */
    public function getUserByActivationCode(string $activationCode): ?User
    {
        $verifyCode = VerifyCode::where('code', $activationCode)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if ($verifyCode) {
            return $this->findByMobile($verifyCode->mobile);
        }

        return null;
    }

    /** Log changes to user model when updating profile.
     *
     * @param User $user The user model being updated.
     * @param array $oldData The original data for the user model.
     * @param array $newData The updated data for the user model.
     * @return void
     */
    function logUserModelChanges(User $user, array $oldData, array $newData): void
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
            Log::store($user->userType == '0' ? LogUserTypesEnum::USER : LogUserTypesEnum::ADMIN, $user->id, LogModelsEnum::USER, LogActionsEnum::EDIT, json_encode($changes));
        }
    }
}
