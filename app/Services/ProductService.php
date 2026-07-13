<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function list(): Collection
    {
        return Product::latest()->get();
    }

    public function find(int $id): Product
    {
        return Product::findOrFail($id);
    }

    public function create(array $data): Product
    {
        $data['slug'] = $this->uniqueSlug($data['name']);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('products', 'public');
        } else {
            unset($data['image']);
        }

        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $this->deleteImageFile($product);
            $data['image'] = $data['image']->store('products', 'public');
        } else {
            // No new file was uploaded — never overwrite the existing image with null.
            unset($data['image']);
        }

        $product->update($data);

        return $product;
    }

    public function delete(Product $product): void
    {
        $this->deleteImageFile($product);
        $product->delete();
    }

    private function deleteImageFile(Product $product): void
    {
        $path = $product->getRawOriginal('image');

        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
