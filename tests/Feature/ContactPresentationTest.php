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

    public function test_about_fills_blank_copy_and_renders_a_phone_icon_link(): void
    {
        SiteSetting::setValue('about.hero_title', '');
        SiteSetting::setValue('about.story_title', 'عنوان سفارشی فروشگاه');
        SiteSetting::setValue('contact.phone', '021 12345678');
        SiteSetting::setValue('contact.working_hours', '');

        $this->get(route('about'))->assertOk()
            ->assertSee('درباره فروشگاه لباس زیر جانان')
            ->assertSee('عنوان سفارشی فروشگاه')
            ->assertSee('href="tel:02112345678"', false)
            ->assertSee('aria-label="تماس تلفنی با جانان"', false)
            ->assertDontSee('021 12345678')
            ->assertDontSee('ساعت پاسخ‌گویی');
    }
}
