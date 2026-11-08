<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryRequestTest extends TestCase
{
    use RefreshDatabase, RequestTestHelper;

    public function test_authorize_is_true(): void
    {
        $this->assertTrue((new CategoryRequest)->authorize());
    }

    public function test_valid_data_passes(): void
    {
        $this->assertTrue($this->validate(CategoryRequest::class, ['name' => 'Books', 'description' => 'All books'])->passes());
    }

    public function test_name_is_required(): void
    {
        $this->assertTrue($this->validate(CategoryRequest::class, [])->errors()->has('name'));
    }

    public function test_name_length_limits(): void
    {
        $this->assertTrue($this->validate(CategoryRequest::class, ['name' => 'A'])->errors()->has('name'));
        $this->assertTrue($this->validate(CategoryRequest::class, ['name' => str_repeat('a', 101)])->errors()->has('name'));
    }

    public function test_name_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Books']);

        $v = $this->validate(CategoryRequest::class, ['name' => 'Books']);

        $this->assertSame('A category with this name already exists.', $v->errors()->first('name'));
    }

    public function test_name_is_trimmed_before_validation(): void
    {
        Category::factory()->create(['name' => 'Books']);

        $this->assertTrue($this->validate(CategoryRequest::class, ['name' => '  Books  '])->errors()->has('name'));
    }

    public function test_own_name_is_allowed_when_updating(): void
    {
        $category = Category::factory()->create(['name' => 'Books']);

        $v = $this->validate(CategoryRequest::class, ['name' => 'Books'], 'PUT', ['category' => $category]);

        $this->assertTrue($v->passes());
    }

    public function test_description_max_length(): void
    {
        $v = $this->validate(CategoryRequest::class, ['name' => 'Books', 'description' => str_repeat('x', 1001)]);

        $this->assertTrue($v->errors()->has('description'));
    }
}
