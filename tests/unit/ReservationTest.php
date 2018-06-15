<?php

use App\Concert;
use App\Reservation;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReservationTest extends TestCase
{
    /** @test */
    function can_calculate_reservation_total_cost()
    {
    	$tickets = collect([
    		(object) ['price' => 1000],
    		(object) ['price' => 1000],
    	]);

        $reservation = new Reservation($tickets);

        $this->assertEquals(2000, $reservation->totalCost());
    }
}