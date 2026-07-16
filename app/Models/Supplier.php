<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'email',
        'contact_person',
        'status'
    ];

    /**
     * Get the stock-in transactions from this supplier.
     */
    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }
}
