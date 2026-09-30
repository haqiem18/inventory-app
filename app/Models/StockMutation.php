<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DebtPayment;

class StockMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'branch_id',
        'to_branch_id',
        'supplier_id',
        'product_id',
        'quantity',
        'purchase_price',
        'price',
        'subtotal',
        'type',
        'to_branch_id',
        'transaction_type',
        'paid_amount',
        'status',
        'payment_status',
        'sub_type',
        'notes',
        'mutation_date',
        'customer_id',
        'sales_id',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function salesPerson()
    {
        return $this->belongsTo(SalesPerson::class, 'sales_id');
    }
    public function debtPayments()
    {
        // Ini memberitahu Laravel bahwa satu mutasi bisa punya banyak catatan pembayaran
        return $this->hasMany(DebtPayment::class);
    }
    
}
