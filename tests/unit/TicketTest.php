<?php

use App\Concert;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TicketTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    function ticket_can_be_released()
    {
    	$concert = factory(Concert::class)->create();
    	$concert->addTickets(1);
    	$order = $concert->orderTickets('john@example.com', 1);
    	$ticket = $order->tickets()->first();
    	$this->assertEquals($order->id, $ticket->order_id);

    	$ticket->release();

    	$this->assertNull($ticket->fresh()->order_id);
    }
}