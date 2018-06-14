<?php

use App\Concert;
use Carbon\Carbon;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ConcertTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    function can_get_formatted_date()
    {
    	$concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01, 8:00pm'),
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */
    function can_get_formatted_start_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01, 17:00:00'),
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /** @test */
    function can_get_ticket_price_in_dollars()
    {
    	$concert = factory(Concert::class)->make([
    		'ticket_price' => 2050
    	]);

    	$this->assertEquals('20.50', $concert->ticket_price_in_dollars);
    }

    /** @test */
    function concerts_with_a_published_at_dates_are_published()
    {
        $published1 = factory(Concert::class)->create([
        	'published_at' => Carbon::parse('-2 weeks'),
        ]);

        $published2 = factory(Concert::class)->create([
        	'published_at' => Carbon::parse('-2 weeks'),
        ]);

        $unpublished = factory(Concert::class)->create([
        	'published_at' => null,
        ]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($published1));
        $this->assertTrue($publishedConcerts->contains($published2));
        $this->assertFalse($publishedConcerts->contains($unpublished));
    }

    /** @test */
    function can_order_concert_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);
        $order = $concert->orderTickets('john@example.com', 3);

        $this->assertNotNull($order);
        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    function can_add_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    function tickets_remaining_do_not_include_tickets_associated_with_orders()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);

        $order = $concert->orderTickets('john@example.com', 3);

        $this->assertEquals(47, $concert->ticketsRemaining());
    }

    /** @test */
    function trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);

        try {
            $order = $concert->orderTickets('john@example.com', 51);
        } catch (NotEnoughTicketsException $e) {
            $order = $concert->orders()->where('email', 'john@example.com')->first();
            $this->assertNull($order);
            $this->assertEquals(50, $concert->ticketsRemaining());
            return;
        }

        $this->fails('order succeeded though there is not enough tickets available');
    }

    /** @test */
    function cannot_order_tickets_that_have_already_been_purchased()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);
        $concert->orderTickets('john@example.com', 8);

        try {
            $concert->orderTickets('jane@example.com', 3);
        } catch(NotEnoughTicketsException $e) {
            $johns = $concert->orders()->where('email', 'jane@example.com')->first();
            $this->assertNull($johns);
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        $this->fails('order succeeded though all tickets have been already purchased!!');
    }
}