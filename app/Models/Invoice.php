<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'consultation_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'payment_status',
        'payment_method',
        'invoice_date',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_at');
    }

    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-success',
            'partially_paid' => 'bg-info text-dark',
            'unpaid' => 'bg-danger',
            'cancelled' => 'bg-secondary',
            default => 'bg-light text-dark',
        };
    }

    public function recalculateTotals(): void
    {
        $paymentsSum = (float) $this->payments()->sum('amount');
        if ($paymentsSum > 0 || $this->payments()->exists()) {
            $this->paid_amount = $paymentsSum;
        }

        $this->subtotal = (float) $this->items()->sum('total_price');
        $this->total_amount = max(0, $this->subtotal + (float) $this->tax_amount - (float) $this->discount_amount);
        $this->balance_due = max(0, $this->total_amount - (float) $this->paid_amount);

        if ($this->balance_due <= 0) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0 && $this->balance_due > 0) {
            $this->payment_status = 'partially_paid';
        } else {
            $this->payment_status = 'unpaid';
        }

        $this->save();
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $last = self::where('invoice_number', 'LIKE', "INV-{$year}-%")
            ->orderByDesc('id')
            ->first();

        if ($last && preg_match('/INV-\d{4}-(\d+)/', $last->invoice_number, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return sprintf('INV-%s-%04d', $year, $number);
    }
}
