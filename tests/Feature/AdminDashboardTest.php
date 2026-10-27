<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_admin_requires_authentication(): void
    {
        $this->get('/admin')->assertForbidden();
        $this->put('/admin/rooms/double/rates', [
            'one_room' => 100, 'one_breakfast' => 120, 'two_room' => 150, 'two_breakfast' => 170,
        ])->assertForbidden();
        $this->get('/admin/login')->assertOk();
    }

    public function test_admin_login_fails_closed_when_credentials_are_unconfigured(): void
    {
        config(['hotel_admin.email' => '', 'hotel_admin.password_hash' => '']);
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'unsafe'])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_login_and_change_prices(): void
    {
        config(['hotel_admin.email' => 'manager@example.com',
                'hotel_admin.password_hash' => Hash::make('VeryLongTestPassword!')]);
        $this->post('/admin/login', ['email' => 'manager@example.com', 'password' => 'VeryLongTestPassword!'])
            ->assertRedirect('/admin');

        $this->get('/admin')->assertOk()->assertSee('Magnolia Management');

        $this->put('/admin/rooms/double/rates', [
            'one_room' => 110, 'one_breakfast' => 125, 'two_room' => 147, 'two_breakfast' => 160,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('room_rates', ['room_type' => 'double', 'two_room' => 147]);
        $this->get('/rooms')->assertOk()->assertSee('GEL 147', false);
        $this->assertSame(147, \App\Support\RoomCatalog::all()['double']['p2'][0]);
    }

    public function test_promotion_is_created_as_inactive_draft(): void
    {
        $this->withSession(['magnolia_admin' => true])
            ->post('/admin/promotions', [
                'name' => 'Autumn Weekend',
                'code' => 'AUTUMN10',
                'discount_percent' => 10,
                'starts_at' => '2026-11-01',
                'ends_at' => '2026-11-30',
            ])->assertRedirect();

        $this->assertDatabaseHas('hotel_promotions', ['code' => 'AUTUMN10', 'is_active' => 0]);
    }
}
