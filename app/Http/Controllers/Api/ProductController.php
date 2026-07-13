<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct(private ProductService $products)
    {
    }

    public function index(Request $request)
    {
        Log::info('products.index', ['user_id' => $request->user()?->id]);

        return response()->json($this->products->list());
    }

    public function show(Request $request, Product $product)
    {
        Log::info('products.show', [
            'user_id'    => $request->user()?->id,
            'product_id' => $product->id,
        ]);

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'sku'         => 'nullable|string|max:100|unique:products,sku',
            'image'       => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:4096',
            'is_active'   => 'nullable|boolean',
        ]);

        $product = $this->products->create($data);

        Log::info('products.store', [
            'user_id'    => $request->user()?->id,
            'product_id' => $product->id,
            'payload'    => Arr::except($data, 'image'),
        ]);

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|required|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'sku'         => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'image'       => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:4096',
            'is_active'   => 'nullable|boolean',
        ]);

        $product = $this->products->update($product, $data);

        Log::info('products.update', [
            'user_id'    => $request->user()?->id,
            'product_id' => $product->id,
            'payload'    => Arr::except($data, 'image'),
        ]);

        return response()->json($product);
    }

    public function destroy(Request $request, Product $product)
    {
        $productId = $product->id;

        $this->products->delete($product);

        Log::warning('products.destroy', [
            'user_id'    => $request->user()?->id,
            'product_id' => $productId,
        ]);

        return response()->json(['message' => 'Product deleted']);
    }
}
