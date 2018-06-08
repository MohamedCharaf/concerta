<?php

use App\Concert;
use Carbon\Carbon;
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
}