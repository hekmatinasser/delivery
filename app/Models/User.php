<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
            0 => 'seller',
            1 => 'delivery',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function coinWallet()
    {
        return $this->hasOne(CoinWallet::class);
    }

    public static function findByMobile(string $mobile): ?User
    {
        return static::where('mobile', $mobile)->first();
    }

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
}
