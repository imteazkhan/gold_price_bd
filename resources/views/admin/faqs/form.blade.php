@extends('admin.layout')
@section('title', $item->exists ? 'Edit FAQ' : 'Add FAQ')

@section('content')
  <form method="POST" action="{{ $item->exists ? route('admin.faqs.update', $item) : route('admin.faqs.store') }}" class="card p-4 sm:p-6 max-w-3xl space-y-4">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div>
      <label class="lbl">Question</label>
      <input name="question" value="{{ old('question', $item->question) }}" class="inp" required maxlength="255">
    </div>
    <div>
      <label class="lbl">Answer</label>
      <textarea name="answer" rows="5" class="inp" required>{{ old('answer', $item->answer) }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-4 items-end">
      <div>
        <label class="lbl">Sort order</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="inp">
      </div>
      <label class="flex items-center gap-2 text-sm pb-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active)) class="rounded text-amber-500"> Active
      </label>
    </div>
    <div class="flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('admin.faqs.index') }}" class="btn btn-light">Cancel</a>
    </div>
  </form>
@endsection
