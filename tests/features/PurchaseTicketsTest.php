<?php

use App\Concert;
use App\Services\IPaymentGetway;
use App\Services\FakePaymentGetway;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PurchaseTicketsTest extends TestCase
{
	use DatabaseMigrations;

    protected $paymentGetway;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentGetway = new FakePaymentGetway;
        $this->app->instance(IPaymentGetway::class, $this->paymentGetway);
    }

    private function orderTickets($concert, $params)
    {
        $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    private function assertValidationError($field)
    {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, $this->decodeResponseJson());
    }

    /** @test */
    function customer_can_purchase_published_concert_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create([
        	'ticket_price' => 3250
        ]);
        $concert->addTickets(50);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGetway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(201);
        $this->assertEquals(9750, $this->paymentGetway->totalCharges());

        $order = $concert->orders()->where('email', 'john@example.com')->first();
        
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    function email_is_required_to_purchase_tickets()
    {
    	$this->paymentGetway = new FakePaymentGetway;
    	$this->app->instance(IPaymentGetway::class, $this->paymentGetway);

        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGetway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    /** @test */
    function ticket_quanity_is_required_to_purchase_ticket()
    {
        $this->paymentGetway = new FakePaymentGetway;
        $this->app->instance(IPaymentGetway::class, $this->paymentGetway);

        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert, [
            'payment_token' => $this->paymentGetway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    function ticket_quanity_is_greater_than_1_to_purchase_ticket()
    {
        $this->paymentGetway = new FakePaymentGetway;
        $this->app->instance(IPaymentGetway::class, $this->paymentGetway);

        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert, [
            'payment_token' => $this->paymentGetway->getValidTestToken(),
            'ticket_quantity' => 0,
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    function payment_token_is_required()
    {
        $this->paymentGetway = new FakePaymentGetway;
        $this->app->instance(IPaymentGetway::class, $this->paymentGetway);

        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
        ]);

        $this->assertValidationError('payment_token');
    }

    /** @test */
    function an_order_is_not_created_if_payment_fails()
    {
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3250]);
        $concert->addTickets(50);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 5,
            'payment_token' => 'invalid-payment-token',
        ]);
        $this->assertResponseStatus(422);

        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNull($order);
    }

    /** @test */
    function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->states('unpublished')->create([]);
        $concert->addTickets(50);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 5,
            'payment_token' => $this->paymentGetway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(404);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGetway->totalCharges());
    }

    /** @test */
    function cannot_purchase_more_tickets_than_remain()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(50);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGetway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNull($order);
        $this->assertEquals(0, $this->paymentGetway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }
}