<?php
namespace App\Domain\Projections;
use Illuminate\Database\Eloquent\Model;

final class RefundProjection extends Model
{
    protected $fillable = [
        'refund_id',
        'order_id',
        'amount',
        'status',
        'approved_at',
        'succeeded_at',
    ];
}
