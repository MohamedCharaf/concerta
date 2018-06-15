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
		return $this->belongsToMany(Order::class, 'tickets');
	}

	public function tickets()
	{
		return $this->hasMany(Ticket::class);
	}

	public function orderTickets($email, $quantity)
	{
		$tickets = $this->findTickets($quantity);

		return $this->createOrder($email, $tickets);
	}

	public function findTickets($quantity)
	{
        $tickets = $this->tickets()->available()->take($quantity)->get();

        if ($tickets->count() < $quantity) {
            throw new NotEnoughTicketsException;
        }
        return $tickets;
	}

	public function createOrder($email, $tickets)
	{
		return Order::place($tickets, $email, $tickets->sum('price'));
	}


	public function addTickets($amount)
	{
		foreach (range(1, $amount) as $i) {
			$this->tickets()->create([]);
		}

		return $this;
	}

	public function ticketsRemaining()
	{
		return $this->tickets()->available()->count();
	}
}
