<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Collection;

class ProductPicker extends Component
{
    public string $search = '';
    public ?int $activeCategoryId = null;
    public string $theme = 'indigo';

    /**
     * Render the component with filtered products.
     */
    public function render(ProductRepository $productRepository)
    {
        $outletId = auth()->user()->outlet_id;

        $categories = Category::whereHas('products.prices', fn($q) => $q->where('outlet_id', $outletId))
            ->orderBy('sort_order')
            ->get();

        $products = $productRepository->getActiveForOutlet(
            $outletId,
            $this->search,
            $this->activeCategoryId
        );

        return view('livewire.pos.product-picker', [
            'categories' => $categories,
            'products' => $products
        ]);
    }

    /**
     * Filter by category.
     */
    public function setCategory(?int $id = null)
    {
        $this->activeCategoryId = $id;
    }

    /**
     * Clear search and category.
     */
    public function resetFilters()
    {
        $this->search = '';
        $this->activeCategoryId = null;
    }

    /**
     * Emit event to Alpine.js to add product to cart.
     */
    public function selectProduct(int $productId)
    {
        $product = Product::with(['prices' => fn($q) => $q->where('outlet_id', auth()->user()->outlet_id), 'modifiers.items'])
            ->find($productId);

        if ($product) {
            $outletData = $product->prices->first();
            
            // Prepare product data for JS
            $productData = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) ($outletData->price ?? $product->price),
                'modifiers' => $product->modifiers->toArray(),
                'image' => $product->image
            ];

            $this->dispatch('product-selected', product: $productData);
        }
    }
}
