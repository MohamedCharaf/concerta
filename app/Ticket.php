<?php

namespace App;

use App\Order;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
	protected $guarded = [];

	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function scopeAvailable($query)
	{
		return $query->whereNull('order_id');
	}

	public function release()
	{
		$this->update(['order_id' => null]);
	}
}
