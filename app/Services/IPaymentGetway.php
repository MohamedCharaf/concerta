<?php 

namespace App\Services;

interface IPaymentGetway
{
	public function charge($amount, $token);
}
