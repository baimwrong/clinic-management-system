<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_reference',
        'amount',
        'payment_method',
        'recorded_by',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function generatePaymentReference(): string
    {
        $year = date('Y');
        $last = self::where('payment_reference', 'LIKE', "PAY-{$year}-%")
            ->orderByDesc('id')
            ->first();

        if ($last && preg_match('/PAY-\d{4}-(\d+)/', $last->payment_reference, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return sprintf('PAY-%s-%04d', $year, $number);
    }
}
