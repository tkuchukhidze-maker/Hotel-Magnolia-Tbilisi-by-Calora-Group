<?php

namespace Tests\Feature;

use App\Models\MarketingCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_routes_require_login(): void
    {
        $this->get('/admin/marketing')->assertRedirect('/admin/marketing/login');
        $this->post('/admin/marketing/campaigns')->assertRedirect('/admin/marketing/login');
        $this->get('/admin/marketing/login')->assertOk();
    }

    public function test_normal_user_cannot_open_marketing_dashboard(): void
    {
        $user = User::factory()->create(['is_marketing_admin' => false]);
        $this->actingAs($user)->get('/admin/marketing')->assertForbidden();
    }

    public function test_staff_member_can_create_draft_campaign(): void
    {
        $user = User::factory()->create(['is_marketing_admin' => true]);
        $this->actingAs($user)->post('/admin/marketing/campaigns', [
            'name' => 'November Escape', 'goal' => 'direct_bookings',
            'audience' => 'Couples', 'market' => 'Georgia',
            'channels' => ['facebook', 'instagram'], 'budget_gel' => 500,
        ])->assertRedirect();

        $this->assertDatabaseHas('marketing_campaigns', [
            'name' => 'November Escape', 'status' => 'draft', 'created_by' => $user->id,
        ]);
        $this->actingAs($user)->get('/admin/marketing')->assertOk()->assertSee('November Escape');
    }

    public function test_campaign_generator_requires_ai_key(): void
    {
        config(['services.openai.key' => null]);
        $user = User::factory()->create(['is_marketing_admin' => true]);
        $campaign = MarketingCampaign::create([
            'created_by' => $user->id, 'name' => 'November',
            'goal' => 'direct_bookings', 'audience' => 'Couples',
            'market' => 'Georgia', 'channels' => ['facebook'], 'status' => 'draft',
        ]);

        $this->actingAs($user)->post('/admin/marketing/campaigns/'.$campaign->id.'/generate')
            ->assertRedirect()->assertSessionHasErrors('ai');
        $this->assertSame('draft', $campaign->fresh()->status);
    }
}
