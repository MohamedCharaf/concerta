<?php

namespace App;

use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\NotEnoughTicketsException;

class Concert extends Model
{
	protected $guarded = [];
	protected $dates = ['date'];


	public function getFormattedDateAttribute()
	{
		return  $this->date->format('F j, Y');
	}

	public function getFormattedStartTimeAttribute()
	{
		return  $this->date->format('g:ia');
	}

	public function getTicketPriceInDollarsAttribute()
	{
		return  number_format($this->ticket_price / 100, 2);
	}

	public function scopePublished($query)
	{
		return $query->whereNotNull('published_at');
	}

	public function orders()
	{
		return $this->hasMany(Order::class);
	}

	public function tickets()
	{
		return $this->hasMany(Ticket::class);
	}

	public function orderTickets($email, $amount)
	{
		if ($amount > $this->ticketsRemaining()) {
			throw new NotEnoughTicketsException;
		}

		$order = $this->orders()->create([
			'email' => $email
		]);

		$tickets = $this->tickets()->take($amount)->get();

		foreach ($tickets as $ticket) {
			$order->tickets()->save($ticket);
		}
		
		return $order;
	}

	public function addTickets($amount)
	{
		foreach (range(1, $amount) as $i) {
			$this->tickets()->create([]);
		}
	}

	public function ticketsRemaining()
	{
		return $this->tickets()->available()->count();
	}
}
