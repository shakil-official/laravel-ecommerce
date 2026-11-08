<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\SubcategoryRequest;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubcategoryRequestTest extends TestCase
{
    use RefreshDatabase, RequestTestHelper;

    public function test_authorize_is_true(): void
    {
        $this->assertTrue((new SubcategoryRequest)->authorize());
    }

    public function test_valid_data_passes(): void
    {
        $category = Category::factory()->create();

        $this->assertTrue($this->validate(SubcategoryRequest::class, ['category_id' => $category->id, 'name' => 'Phones'])->passes());
    }

    public function test_category_and_name_are_required(): void
    {
        $errors = $this->validate(SubcategoryRequest::class, [])->errors();

        $this->assertTrue($errors->has('category_id'));
        $this->assertTrue($errors->has('name'));
    }

    public function test_category_must_exist(): void
    {
        $v = $this->validate(SubcategoryRequest::class, ['category_id' => 999, 'name' => 'Phones']);

        $this->assertSame('The selected category does not exist.', $v->errors()->first('category_id'));
    }

    public function test_name_is_unique_inside_the_same_category_only(): void
    {
        $a = Category::factory()->create();
        $b = Category::factory()->create();
        Subcategory::factory()->for($a)->create(['name' => 'Phones']);

        $this->assertTrue($this->validate(SubcategoryRequest::class, ['category_id' => $a->id, 'name' => 'Phones'])->errors()->has('name'));
        $this->assertTrue($this->validate(SubcategoryRequest::class, ['category_id' => $b->id, 'name' => 'Phones'])->passes());
    }

    public function test_own_name_is_allowed_when_updating(): void
    {
        $sub = Subcategory::factory()->create(['name' => 'Phones']);

        $v = $this->validate(SubcategoryRequest::class, ['category_id' => $sub->category_id, 'name' => 'Phones'], 'PUT', ['subcategory' => $sub]);

        $this->assertTrue($v->passes());
    }
}
