<?php

namespace App;

use App\Ticket;
use App\Concert;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
	protected $guarded = [];

	public function concert()
	{
		return $this->belongsTo(Concert::class);
	}

	public function tickets()
	{
		return $this->hasMany(Ticket::class);
	}
}
