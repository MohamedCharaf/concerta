<?php

use App\Services\FakePaymentGetway;
use App\Exceptions\PaymentFailedException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FakePaymentGetwayTest extends TestCase
{
	/** @test */
	function charges_with_a_valid_payment_token_are_successful()
	{
		$paymentGetAway = new FakePaymentGetway;

		$paymentGetAway->charge(2500, $paymentGetAway->getValidTestToken());

		$this->assertEquals(2500, $paymentGetAway->totalCharges());
	}

	/** @test */
	function charges_with_an_invalid_token_are_failed()
	{
		try {
			$paymentGetway = new FakePaymentGetway;
			$paymentGetway->charge(2500, 'invalid-token');
		} catch (PaymentFailedException $e) {
			return;
		}

		$this->fail();
	}
}