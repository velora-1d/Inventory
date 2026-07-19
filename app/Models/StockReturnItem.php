<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReturnItem extends Model
{
    protected $fillable = [
        'stock_return_id',
        'product_id',
        'unit_id',
        'qty',
        'qty_base_unit',
        'price',
        'subtotal',
        'condition',
        'notes'
    ];

    /**
     * Get the header transaction.
     */
    public function stockReturn(): BelongsTo
    {
        return $this->belongsTo(StockReturn::class);
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
