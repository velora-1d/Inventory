<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOutItem extends Model
{
    protected $fillable = [
        'stock_out_id',
        'product_id',
        'unit_id',
        'qty',
        'qty_base_unit',
        'price',
        'subtotal'
    ];

    /**
     * Get the header transaction.
     */
    public function stockOut(): BelongsTo
    {
        return $this->belongsTo(StockOut::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the transaction unit.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
