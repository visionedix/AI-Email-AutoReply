<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('id', 'desc')->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['keyword_search'] = $this->normalizeKeywords($data['keyword_search'] ?? null);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['drow_images'] = $this->storeDrowImages($request);

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedData($request, $product);
        $data['keyword_search'] = $this->normalizeKeywords($data['keyword_search'] ?? null);

        if ($request->hasFile('image')) {
            $this->deleteFile($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['drow_images'] = $this->storeDrowImages($request, $product->drow_images ?? []);

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->deleteFile($product->image);

        foreach ($product->drow_images ?? [] as $image) {
            $this->deleteFile($image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'unit' => ['required', 'string', 'max:100'],
            'per_unit_price' => ['required', 'numeric', 'min:0'],
            'product_details' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'keyword_search' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'drow_images' => ['nullable', 'array', 'max:4'],
            'drow_images.*' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function normalizeKeywords(?string $keywords): ?string
    {
        $keywords = collect(explode(',', $keywords ?? ''))
            ->map(fn (string $keyword) => trim($keyword))
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        return $keywords !== '' ? $keywords : null;
    }

    /**
     * @param array<int, string> $existingImages
     *
     * @return array<int, string>
     */
    private function storeDrowImages(Request $request, array $existingImages = []): array
    {
        $images = $existingImages;

        foreach ($request->file('drow_images', []) as $index => $file) {
            if ($file === null) {
                continue;
            }

            if (isset($images[$index])) {
                $this->deleteFile($images[$index]);
            }

            $images[$index] = $file->store('products/drow-images', 'public');
        }

        ksort($images);

        return array_values(array_filter($images));
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
