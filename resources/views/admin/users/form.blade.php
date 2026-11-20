@extends('admin.layout')
@section('title', $item->exists ? 'Edit user' : 'Add user')

@section('content')
  <form method="POST" action="{{ $item->exists ? route('admin.users.update', $item) : route('admin.users.store') }}" class="card p-4 sm:p-6 max-w-2xl space-y-4">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="lbl">Name</label>
        <input name="name" value="{{ old('name', $item->name) }}" class="inp" required>
      </div>
      <div>
        <label class="lbl">Email</label>
        <input type="email" name="email" value="{{ old('email', $item->email) }}" class="inp" required autocomplete="off">
      </div>
      <div>
        <label class="lbl">Password {{ $item->exists ? '(leave blank to keep)' : '' }}</label>
        <input type="password" name="password" class="inp" {{ $item->exists ? '' : 'required' }} autocomplete="new-password">
      </div>
      <div>
        <label class="lbl">Confirm password</label>
        <input type="password" name="password_confirmation" class="inp" autocomplete="new-password">
      </div>
    </div>
    @unless($item->exists && $item->is(auth()->user()))
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $item->is_admin)) class="rounded text-amber-500"> Admin panel access
      </label>
    @endunless
    <div class="flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
    </div>
  </form>
@endsection
