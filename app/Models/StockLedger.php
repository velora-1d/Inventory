<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLedger extends Model
{
    // Enable disable timestamps custom handling
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'unit_id',
        'qty_in',
        'qty_out',
        'balance',
        'price',
        'notes',
        'transaction_date',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'created_at' => 'datetime'
    ];

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the unit.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Boot function to automatically set created_at on creation
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }
}
