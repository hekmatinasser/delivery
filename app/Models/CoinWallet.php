<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="CoinWallet",
 *     title="CoinWallet",
 *     description="A Coin Wallet object",
 *     required={"user_id", "coins"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="coins", type="number", format="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 */

class CoinWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coins'
    ];

    protected $guarded = [];

    /**
     * CoinWallet belong to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
