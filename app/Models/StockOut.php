<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOut extends Model
{
    protected $fillable = [
        'transaction_no',
        'transaction_date',
        'warehouse_id',
        'reference_no',
        'recipient',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    /**
     * Get the source warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who created the record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the items in this transaction.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockOutItem::class);
    }
}
