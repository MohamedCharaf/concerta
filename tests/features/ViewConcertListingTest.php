<?php

use App\Order;
use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewConcertListingTest extends TestCase
{
    use DatabaseMigrations;
    /** @test */
    function user_can_view_a_published_concert()
    {
        $concert = factory(Concert::class)->states('published')->create([
            'title' => 'The Red Chord',
            'subtitle' => 'Fly',
            'date' => Carbon::parse('December 13, 2016 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'The Mosh pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
            'additional_information' => 'For tickets call 555-55-555-55',
        ]);

        $this->visit('/concerts/' . $concert->id);

        $this->see('The Red Chord');
        $this->see('Fly');
        $this->see('December 13, 2016');
        $this->see('8:00pm');
        $this->see('32.50');
        $this->see('The Mosh pit');
        $this->see('123 Example Lane');
        $this->see('Laraville');
        $this->see('ON');
        $this->see('17916');
        $this->see('For tickets call 555-55-555-55');
    }

    /** @test */
    function user_cannot_view_unpublished_tests()
    {
        $concert = factory(Concert::class)->states('unpublished')->create();
        
        //When you want to test a failure scenario use http method
        //When you want to test a sucessfull scenario, use visit method
        $this->get('/concerts/' . $concert->id);

        $this->assertResponseStatus(404);
    }
    
}
