<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockIn extends Model
{
    protected $fillable = [
        'transaction_no',
        'transaction_date',
        'supplier_id',
        'warehouse_id',
        'reference_no',
        'notes',
        'status',
        'created_by'
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    /**
     * Get the supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the destination warehouse.
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
        return $this->hasMany(StockInItem::class);
    }
}
