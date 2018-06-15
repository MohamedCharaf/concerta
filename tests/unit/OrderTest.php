<?php

use App\Order;
use App\Concert;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    function can_place_order_using_tickets_and_email()
    {
        $concert = factory(Concert::class)->create()->addTickets(5);
        $this->assertEquals(5, $concert->ticketsRemaining());
        $tickets = $concert->findTickets(3);

        $order = Order::place($tickets, 'john@example.com', 3600);

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(2, $concert->ticketsRemaining());
    }

    /** @test */
    function concerting_to_an_array()
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 1200])->addTickets(5);
        $order = $concert->orderTickets('jane@example.com', 5);

        $result = $order->toArray();

        $this->assertEquals([
            'email' => 'jane@example.com',
            'ticket_quantity' => 5,
            'amount' => 6000
        ], $result);
    }

    /** @test */
    function tickets_are_released_when_an_order_is_cancelled()
    {
    	$concert = factory(Concert::class)->create()->addTickets(10);
    	$order = $concert->orderTickets('jane@example.com', 5);
    	$this->assertEquals(5, $concert->ticketsRemaining());

    	$order->cancel();

    	$this->assertEquals(10, $concert->ticketsRemaining());
    	$this->assertNull(Order::find($order->id));
    }
}