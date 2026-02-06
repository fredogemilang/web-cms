<?php

namespace Tests\Feature\Livewire\Admin\Cpt;

use App\Livewire\Admin\Cpt\CptForm;
use App\Models\CustomPostType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CptFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        // Ensure the user has necessary permissions if your app uses them
        // For now, just acting as the user
        $this->actingAs($user);
        
        // Ensure admin path is set for tests
        config(['admin.path' => 'admin']);
    }

    /** @test */
    public function it_renders_correctly()
    {
        Livewire::test(CptForm::class)
            ->assertStatus(200)
            ->assertSee('Add New Custom Post Type');
    }

    /** @test */
    public function it_can_create_a_new_cpt()
    {
        Livewire::test(CptForm::class)
            ->set('name', 'test_post')
            ->set('singularLabel', 'Test Post')
            ->set('pluralLabel', 'Test Posts')
            ->set('slug', 'test-posts')
            ->call('save')
            ->assertRedirect(route('admin.cpt.index'));

        $this->assertDatabaseHas('custom_post_types', [
            'name' => 'test_post',
            'slug' => 'test-posts',
        ]);
    }

    /** @test */
    public function it_can_add_meta_fields()
    {
        Livewire::test(CptForm::class)
            ->set('name', 'test_post')
            ->set('singularLabel', 'Test Post')
            ->set('pluralLabel', 'Test Posts')
            ->set('slug', 'test-posts')
            ->set('newField', [
                'name' => 'price',
                'label' => 'Price',
                'type' => 'text',
                'description' => 'Product price',
                'is_required' => true,
            ])
            ->call('saveField')
            ->call('save');

        $cpt = CustomPostType::where('slug', 'test-posts')->first();
        $this->assertCount(1, $cpt->metaFields);
        $this->assertEquals('price', $cpt->metaFields->first()->name);
    }
}
