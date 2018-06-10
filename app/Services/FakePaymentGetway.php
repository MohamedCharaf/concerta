<?php 

namespace App\Services;

use App\Exceptions\PaymentFailedException;

/**
 * 
 */
class FakePaymentGetway implements IPaymentGetway
{
	protected $charges;

	function __construct()
	{
		$this->charges = collect();
	}

	public function getValidTestToken()
	{
		return 'valid-token';
	}

	public function charge($amount, $token)
	{
		if ($token !== $this->getValidTestToken()) {
			throw new PaymentFailedException;
		}
		$this->charges[] = $amount;
	}

	public function totalCharges()
	{
		return $this->charges->sum();
	}

}

