<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PayRefund extends Model
{
    protected $fillable = [
        'name',
        'date',
        'customer_id',
        'bank_id',
        'cheque_no',
        'total_amount',
        'details',
        'issued_by',
        'modified_by'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function refundPassengers()
    {
        return $this->belongsToMany(RefundPassenger::class, 'pay_refund_refund_passenger');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payRefund) {
            $payRefund->name = self::generatePayRefundName();
        });
    }
    public static function generatePayRefundName()
    {
        $latest = self::latest('id')->first();
        $nextNumber = $latest ? ((int) substr($latest->name, 2)) + 1 : 1;
        return 'RP' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}