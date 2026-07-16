<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnitConversion extends Model
{
    protected $fillable = [
        'product_id',
        'from_unit_id',
        'to_unit_id',
        'conversion_value'
    ];

    /**
     * Get the product associated with this conversion.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the source unit (e.g. Dus).
     */
    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    /**
     * Get the target unit (e.g. Pcs).
     */
    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }
}
