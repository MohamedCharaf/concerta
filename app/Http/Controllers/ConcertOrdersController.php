<?php

namespace App\Http\Controllers;

use App\Concert;
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
			$order = $concert->orderTickets(request('email'), request('ticket_quantity'));
			$this->paymentGetway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));
			return response()->json([], 201);

		} catch (PaymentFailedException $e) {
			$order->cancel();
			return response()->json([], 422);
		}
		catch(NotEnoughTicketsException $e) {
			return response()->json([], 422);
		}
	}
}
