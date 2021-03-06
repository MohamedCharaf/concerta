<?php

namespace App\Http\Controllers;

use App\Order;
use App\Concert;
use App\Reservation;
use Illuminate\Http\Request;
use App\Services\IPaymentGetway;
use App\Exceptions\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;

class ConcertOrdersController extends Controller
{
	private $paymentGetway;

	function __construct(IPaymentGetway $paymentGetway)
	{
		$this->paymentGetway = $paymentGetway;
	}
	public function store($concertId)
	{
		$concert = Concert::published()->findOrFail($concertId);

		$this->validate(request(), [
			'email' => 'required',
			'ticket_quantity' => 'required|integer|min:1',
			'payment_token' => 'required',
		]);

		try {
			$tickets = $concert->findTickets(request('ticket_quantity'));

			$reservation = new Reservation($tickets);

			$this->paymentGetway->charge($reservation->totalCost(), request('payment_token'));

			Order::place($tickets, request('email'), $reservation->totalCost());

			return response()->json([
				'email' => request('email'),
				'ticket_quantity' => request('ticket_quantity'),
				'amount' => $reservation->totalCost()
			], 201);

		} catch (PaymentFailedException $e) {
			return response()->json([], 422);
		}
		catch(NotEnoughTicketsException $e) {
			return response()->json([], 422);
		}
	}
}
