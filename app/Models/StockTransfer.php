<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    protected $fillable = [
        'transaction_no',
        'transaction_date',
        'from_warehouse_id',
        'to_warehouse_id',
        'reference_no',
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
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Get the destination warehouse.
     */
    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
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
        return $this->hasMany(StockTransferItem::class);
    }
}
