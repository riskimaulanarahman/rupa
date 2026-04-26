<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCurrencyInputRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        Setting::set('business_type', 'clinic', 'string');
        Setting::set('feature_products', true, 'boolean');
    }

    public function test_edit_product_view_passes_decimal_prices_through_safe_js_encoding(): void
    {
        $user = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $category = ProductCategory::create([
            'name' => 'Retail',
            'description' => 'Retail products',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cuticle Oil',
            'sku' => 'CUTICLE-01',
            'description' => 'Hydrating oil',
            'price' => 50000,
            'cost_price' => 25000,
            'stock' => 10,
            'min_stock' => 2,
            'unit' => 'pcs',
            'is_active' => true,
            'track_stock' => true,
        ]);

        $response = $this->actingAs($user)->get(route('products.edit', $product));

        $response->assertOk();
        $response->assertSee('x-data="currencyInput(\'50000.00\')"', false);
        $response->assertSee('x-data="currencyInput(\'25000.00\')"', false);
    }

    public function test_updating_product_without_changing_prices_keeps_decimal_cast_values_stable(): void
    {
        $user = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $category = ProductCategory::create([
            'name' => 'Retail',
            'description' => 'Retail products',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cuticle Oil',
            'sku' => 'CUTICLE-01',
            'description' => 'Hydrating oil',
            'price' => 50000,
            'cost_price' => 25000,
            'stock' => 10,
            'min_stock' => 2,
            'unit' => 'pcs',
            'is_active' => true,
            'track_stock' => true,
        ]);

        $response = $this->actingAs($user)->put(route('products.update', $product), [
            'category_id' => $category->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'description' => 'Updated description only',
            'price' => 50000,
            'cost_price' => 25000,
            'stock' => $product->stock,
            'min_stock' => $product->min_stock,
            'unit' => $product->unit,
            'is_active' => 1,
            'track_stock' => 1,
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'description' => 'Updated description only',
            'price' => 50000,
            'cost_price' => 25000,
        ]);
    }
}
