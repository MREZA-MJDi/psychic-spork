<?php
namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_map_uses_configured_address_and_preserves_contact_route(): void
    {
        SiteSetting::setValue('contact.address', 'تهران، خیابان آزمایشی');
        $this->get(route('contact'))->assertRedirect(route('about').'#contact');
        $this->get(route('about'))->assertOk()
            ->assertSee('contact-map-card', false)
            ->assertSee(rawurlencode('تهران، خیابان آزمایشی'), false)
            ->assertSee('contact-trust', false)
            ->assertSee(route('shipping'), false)
            ->assertSee(route('returns'), false);
    }
}
