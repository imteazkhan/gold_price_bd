@extends('admin.layout')
@section('title', 'FAQs')

@section('actions')
  <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary btn-sm">+ Add FAQ</a>
@endsection

@section('content')
  <div class="space-y-3">
    @forelse($items as $f)
      <div class="card p-4 flex flex-col sm:flex-row sm:items-start gap-3">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="badge bg-gray-100 text-gray-600 mono">#{{ $f->sort_order }}</span>
            {!! $f->is_active ? '<span class="badge bg-emerald-50 text-emerald-700">Active</span>' : '<span class="badge bg-gray-100 text-gray-500">Hidden</span>' !!}
          </div>
          <p class="font-semibold text-sm mt-1.5">{{ $f->question }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($f->answer, 200) }}</p>
        </div>
        <div class="flex gap-2 shrink-0">
          <a href="{{ route('admin.faqs.edit', $f) }}" class="btn btn-light btn-sm">Edit</a>
          <form method="POST" action="{{ route('admin.faqs.destroy', $f) }}" data-confirm="Delete this FAQ?">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="card p-8 text-center text-gray-400 text-sm">No FAQs yet.</div>
    @endforelse
  </div>
@endsection
