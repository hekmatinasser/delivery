<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reason()
    {
        return $this->belongsTo(WalletTransactionReason::class, 'reason_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coinWalletTransaction()
    {
        return $this->belongsTo(CoinWalletTransaction::class);
    }

    /**
     * Changer User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function changer()
    {
        return $this->belongsTo(User::class, 'changer_id');
    }
}
