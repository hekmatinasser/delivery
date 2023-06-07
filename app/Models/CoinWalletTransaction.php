<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinWalletTransaction extends Model
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
        return $this->belongsTo(CoinWalletTransactionReason::class, 'reason_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function walletTransaction()
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function travelAction()
    {
        return $this->belongsTo(CoinWalletTransaction::class, 'travel_action_id');
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
