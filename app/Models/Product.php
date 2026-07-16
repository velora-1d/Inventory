<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'base_unit_id',
        'purchase_unit_id',
        'sale_unit_id',
        'purchase_price',
        'sale_price',
        'avg_price',
        'min_stock',
        'default_warehouse_id',
        'description',
        'photo',
        'status'
    ];

    /**
     * Get the category of the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the base unit of the product.
     */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * Get the purchase unit of the product.
     */
    public function purchaseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    /**
     * Get the sale unit of the product.
     */
    public function saleUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'sale_unit_id');
    }

    /**
     * Get the default warehouse of the product.
     */
    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_warehouse_id');
    }

    /**
     * Get unit conversions for this product.
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(ProductUnitConversion::class);
    }

    /**
     * Get stock records across warehouses for this product.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
