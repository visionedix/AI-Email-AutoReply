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

        if ($request->hasFile('quotation_documents')) {
            $data['quotation_documents'] = $request->file('quotation_documents')->store('products/quotation-documents', 'public');
        }

        $data = array_merge($data, $this->storeDrowImages($request));

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

        if ($request->hasFile('quotation_documents')) {
            $this->deleteFile($product->quotation_documents);
            $data['quotation_documents'] = $request->file('quotation_documents')->store('products/quotation-documents', 'public');
        }

        $data = array_merge($data, $this->storeDrowImages($request, $product));

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->deleteFile($product->image);
        $this->deleteFile($product->quotation_documents);

        foreach (['drow_image_1', 'drow_image_2', 'drow_image_3'] as $field) {
            $this->deleteFile($product->{$field});
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
            'other_details' => ['nullable', 'string'],
            'specification' => ['nullable', 'string'],
            'keyword_search' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'quotation_documents' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'drow_image_1' => ['nullable', 'image', 'max:2048'],
            'drow_image_2' => ['nullable', 'image', 'max:2048'],
            'drow_image_3' => ['nullable', 'image', 'max:2048'],
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
     * @param  Product|null  $product
     * @return array<string, string|null>
     */
    private function storeDrowImages(Request $request, ?Product $product = null): array
    {
        $fields = ['drow_image_1', 'drow_image_2', 'drow_image_3'];
        $images = [];

        foreach ($fields as $field) {
            $existingPath = $product?->{$field};
            $file = $request->file($field);

            if ($file) {
                if ($existingPath) {
                    $this->deleteFile($existingPath);
                }

                $images[$field] = $file->store('products/drow-images', 'public');
                continue;
            }

            if ($product && filled($existingPath)) {
                $images[$field] = $existingPath;
            }
        }

        return $images;
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
