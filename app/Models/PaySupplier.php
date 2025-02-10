<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaySupplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cheque_no',
        'ref_no',
        'bank_id',
        'supplier_id',
        'details',
        'total',
        'issued_by',
    ];

    public function cardPassengers()
    {
        return $this->belongsToMany(CardPassenger::class, 'card_passenger_pay_supplier')
            ->withPivot(['amount'])
            ->withTimestamps();
    }


    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paySupplier) {
            $paySupplier->name = self::generatePayRefundName();
            $paySupplier->issued_by = auth()->user()->id;
        });
    }
    public static function generatePayRefundName()
    {
        $latest = self::latest('id')->first();
        $nextNumber = $latest ? ((int) substr($latest->name, 2)) + 1 : 1;
        return 'SP' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

}