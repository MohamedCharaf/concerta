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
}
