<?php

namespace Tests\Feature;

use App\Support\RoomPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RoomPricingCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_precedence_daily_monthly_and_base(): void
    {
        DB::table('room_plan_prices')->insert(['room_type'=>'double', 'plan_code'=>'room','one_guest'=>90,'two_guests'=>120]);
        DB::table('room_month_prices')->insert(['room_type'=>'double', 'plan_code'=>'room','month'=>'2027-07','one_guest'=>100,'two_guests'=>150]);
        DB::table('room_day_prices')->insert(['room_type'=>'double', 'plan_code'=>'room','stay_date'=>'2027-07-02','one_guest'=>130,'two_guests'=>220]);
        $stay = RoomPricing::totalForRoom('double','room',2,'2027-07-01',3);
        $this->assertSame(520, $stay['total']); // 150 + 220 + 150
        $this->assertSame([150,220,150], array_column($stay['nights'], 'price'));
        $this->assertSame(240, RoomPricing::totalForRoom('double','room',2,'2027-06-01',2)['total']);
        $this->assertSame(300, RoomPricing::totalForRoom('double','room',1,'2027-07-01',3)['total']);
    }

    public function test_admin_calendar_writes_month_and_date_range(): void
    {
        $this->withSession(['magnolia_admin'=>true])
            ->post(route('admin.pricing.schedule'), [
                'room_type'=>'double','plan_code'=>'room','scope'=>'month','month'=>'2027-08','one_guest'=>90,'two_guests'=>160
            ])->assertRedirect()->assertSessionHas('success');

        $this->withSession(['magnolia_admin'=>true])
            ->post(route('admin.pricing.schedule'), [
                'room_type'=>'double','plan_code'=>'room','scope'=>'range','from'=>'2027-08-08','to'=>'2027-08-09',
                'one_guest'=>111,'two_guests'=>199
            ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseCount('room_day_prices', 2);
        $this->get(route('booking-prices.preview', ['checkin'=>'2027-08-07','nights'=>3]))
            ->assertOk()->assertJsonPath('rooms.double.room.two', 160+199+199);
        $this->withSession(['magnolia_admin'=>true])->get(route('admin.pricing.index', [
            'month'=>'2027-08','room'=>'double','plan'=>'room'
        ]))->assertOk()->assertSee('Pricing calendar')->assertSee('199');
    }

    public function test_custom_rate_plans_are_drafts_until_all_rooms_are_priced(): void
    {
        $this->withSession(['magnolia_admin'=>true])
            ->post(route('admin.pricing.plans.store'), [
                'code'=>'FLEX','name'=>'Flexible rate','includes_breakfast'=>1,
            ])->assertRedirect();
        $this->assertDatabaseHas('room_rate_plans', ['code'=>'flex','is_active'=>false]);
        $this->assertNotContains('flex', array_column(RoomPricing::plans(), 'code'));
        $this->withSession(['magnolia_admin'=>true])->patch(route('admin.pricing.plans.publish', 'flex'), [
            'is_active'=>1
        ])->assertSessionHasErrors('plan');

        foreach (array_keys(config('hotel.rooms')) as $room) {
            $this->withSession(['magnolia_admin'=>true])->post(route('admin.pricing.base'), [
                'room_type'=>$room,'plan_code'=>'flex','one_guest'=>110,'two_guests'=>160
            ])->assertRedirect();
        }
        $this->withSession(['magnolia_admin'=>true])->patch(route('admin.pricing.plans.publish', 'flex'), [
            'is_active'=>1
        ])->assertRedirect();
        $this->assertContains('flex', array_column(RoomPricing::plans(), 'code'));
        $this->get(route('booking-prices.preview', ['checkin'=>'2027-02-02','nights'=>2]))
            ->assertOk()->assertJsonPath('rooms.double.flex.two', 320);
    }

    public function test_booking_uses_date_specific_total_not_a_flat_rate(): void
    {
        Mail::fake();
        $arrival=now()->addDays(10)->toDateString();
        $next=now()->addDays(11)->toDateString();
        DB::table('room_day_prices')->insert([
            ['room_type'=>'double','plan_code'=>'room','stay_date'=>$arrival,'one_guest'=>110,'two_guests'=>200],
            ['room_type'=>'double','plan_code'=>'room','stay_date'=>$next,'one_guest'=>110,'two_guests'=>300],
        ]);
        $payload=[
            'items'=>[['room'=>'double','qty'=>1]],
            'rate'=>'room','checkin'=>$arrival,'nights'=>2,'adults'=>2,'children'=>0,
            'children_ages'=>[],'first_name'=>'Nino','last_name'=>'B','email'=>'nino@example.com',
            'phone'=>'+995555123456','country'=>'Georgia','privacy'=>true,
        ];
        $this->postJson(route('bookings.store'), $payload)
            ->assertCreated()->assertJsonPath('total', 500)
            ->assertJsonPath('daily_totals', [200,300]);
        $this->assertDatabaseHas('bookings', ['total'=>500]);
    }

    public function test_schedule_requires_admin_and_rejects_invalid_ranges(): void
    {
        $payload=['room_type'=>'double','plan_code'=>'room','scope'=>'range',
            'from'=>'2027-04-10','to'=>'2027-04-09','one_guest'=>100,'two_guests'=>150];
        $this->post(route('admin.pricing.schedule'), $payload)->assertForbidden();
        $this->withSession(['magnolia_admin'=>true])->post(route('admin.pricing.schedule'), $payload)
            ->assertSessionHasErrors('to');
    }
}
