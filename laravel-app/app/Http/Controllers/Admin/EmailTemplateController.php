<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Models\EmailTemplate;
use App\Admin\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $emailTemplates = EmailTemplate::orderBy('id', 'desc')->paginate(15);

        return view('admin.email_templates.index', compact('emailTemplates'));
    }

    public function create()
    {
        $products = Product::orderBy('product_name')->get();

        return view('admin.email_templates.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['keyword_search'] = $this->normalizeKeywords($data['keyword_search'] ?? null);

        if ($request->hasFile('template_document')) {
            $data['template_document'] = $request->file('template_document')->store('email-templates/documents', 'public');
        }

        EmailTemplate::create($data);

        return redirect()->route('admin.email-templates.index')->with('success', 'Email template created successfully.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $products = Product::orderBy('product_name')->get();

        return view('admin.email_templates.edit', compact('emailTemplate', 'products'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['keyword_search'] = $this->normalizeKeywords($data['keyword_search'] ?? null);

        if ($request->hasFile('template_document')) {
            $this->deleteFile($emailTemplate->template_document);
            $data['template_document'] = $request->file('template_document')->store('email-templates/documents', 'public');
        }

        $emailTemplate->update($data);

        return redirect()->route('admin.email-templates.index')->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate): RedirectResponse
    {
        $this->deleteFile($emailTemplate->template_document);
        $emailTemplate->delete();

        return redirect()->route('admin.email-templates.index')->with('success', 'Email template deleted successfully.');
    }

    public function show(EmailTemplate $emailTemplate)
    {
        return view('admin.email_templates.show', compact('emailTemplate'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'template_name' => ['required', 'string', 'max:255'],
            'template_subject' => ['required', 'string', 'max:255'],
            'template_body' => ['required', 'string'],
            'template_other_details' => ['nullable', 'string'],
            'template_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,txt,rtf,html,htm', 'max:5120'],
            'keyword_search' => ['nullable', 'string'],
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

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
