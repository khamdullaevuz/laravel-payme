<?php

namespace Khamdullaevuz\Payme\Models;

use Khamdullaevuz\Payme\Enums\PaymeState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class PaymeTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'state' => PaymeState::class,
        'create_time' => 'integer',
        'perform_time' => 'integer',
        'cancel_time' => 'integer',
        'reason' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}