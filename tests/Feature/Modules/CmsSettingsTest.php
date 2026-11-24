<?php

namespace Tests\Feature\Modules;

use Adm\Models\AdmUser;
use App\Models\CommonText;
use Tests\TestCase;

/**
 * Settings > CMS settings: super admins can hide the dashboard's Support
 * details card for clients without a support package.
 */
class CmsSettingsTest extends TestCase
{
    protected string $base = 'https://www.new-adf-cms.laragon';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
    }

    protected function superAdmin(): AdmUser
    {
        $User = AdmUser::query()->whereHas('group', fn ($q) => $q->where('super_admin', true))->first();
        if (!$User) {
            $this->markTestSkipped('Needs a super admin user.');
        }

        return $User;
    }

    public function test_support_details_card_can_be_turned_off_and_on(): void
    {
        $this->actingAs($this->superAdmin(), 'adm');
        $original = CommonText::where('field', 'dashboard_support_details')->first()?->value;

        try {
            $page = $this->get($this->base . '/adm/cms-settings')->assertOk()->assertSee('Show the Support details card');
            if ($original === null) {
                // Never saved: the toggle starts switched on
                $page->assertSee('checked: true', false);
            }
            // The card starts as this site has it set (shown unless saved as off)
            $dashboard = $this->get($this->base . '/adm')->assertOk();
            $original === '0' ? $dashboard->assertDontSee('Support details') : $dashboard->assertSee('Support details');

            $this->post($this->base . '/adm/cms-settings/update', ['dashboard_support_details' => '0'])->assertRedirect();
            $this->refreshCommonText();
            $this->get($this->base . '/adm')->assertOk()->assertDontSee('Support details');

            $this->post($this->base . '/adm/cms-settings/update', ['dashboard_support_details' => '1'])->assertRedirect();
            $this->refreshCommonText();
            $this->get($this->base . '/adm')->assertOk()->assertSee('Support details');
        } finally {
            if ($original === null) {
                CommonText::where('field', 'dashboard_support_details')->delete();
            } else {
                CommonText::where('field', 'dashboard_support_details')->update(['value' => $original]);
            }
        }
    }

    public function test_other_admins_cannot_open_cms_settings(): void
    {
        $Group = \Adm\Models\AdmUserGroup::create(['title' => 'Settings test editors', 'permissions' => ['cms-settings'], 'super_admin' => false, 'active' => true]);
        $User = AdmUser::create([
            'name'              => 'Settings Test Editor',
            'email'             => 'settings-test-' . uniqid() . '@example.test',
            'password'          => bcrypt(str()->random(32)),
            'adm_user_group_id' => $Group->id,
        ]);

        try {
            $this->actingAs($User, 'adm');
            $this->get($this->base . '/adm/cms-settings')->assertForbidden();
        } finally {
            $User->delete();
            $Group->delete();
        }
    }

    /**
     * CommonTextHelper keeps values in a static cache for the request
     */
    protected function refreshCommonText(): void
    {
        $Ref = new \ReflectionClass(\App\Helpers\CommonTextHelper::class);
        foreach (['LOADED' => false, 'VALUES' => []] as $property => $value) {
            if ($Ref->hasProperty($property)) {
                $Ref->setStaticPropertyValue($property, $value);
            }
        }
    }
}
