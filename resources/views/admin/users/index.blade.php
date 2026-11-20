@extends('admin.layout')
@section('title', 'Admin users')

@section('actions')
  <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">+ Add user</a>
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th class="hidden md:table-cell">Last login</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
          @foreach($items as $u)
            <tr>
              <td class="font-semibold whitespace-nowrap">{{ $u->name }} @if($u->is(auth()->user()))<span class="badge bg-amber-50 text-amber-700">you</span>@endif</td>
              <td>{{ $u->email }}</td>
              <td>{!! $u->is_admin ? '<span class="badge bg-emerald-50 text-emerald-700">Admin</span>' : '<span class="badge bg-gray-100 text-gray-500">No access</span>' !!}</td>
              <td class="hidden md:table-cell text-gray-500 text-xs">{{ $u->last_login_at?->diffForHumans() ?? 'never' }}</td>
              <td class="text-right whitespace-nowrap">
                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-light btn-sm">Edit</a>
                @unless($u->is(auth()->user()))
                  <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline" data-confirm="Delete {{ $u->name }}?">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm">Delete</button>
                  </form>
                @endunless
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  {{ $items->links() }}
@endsection
