@extends('admin.layout')
@section('title', 'Settings')

@section('content')
  <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4 max-w-4xl">
    @csrf @method('PUT')

    <div class="card p-4 sm:p-6 space-y-4">
      <h2 class="font-bold text-sm">Website</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl">Site name</label><input name="site_name" value="{{ old('site_name', $settings['site_name']) }}" class="inp" required></div>
        <div><label class="lbl">Browser title</label><input name="site_title" value="{{ old('site_title', $settings['site_title']) }}" class="inp" required></div>
        <div class="sm:col-span-2"><label class="lbl">Hero heading</label><input name="hero_title" value="{{ old('hero_title', $settings['hero_title']) }}" class="inp" required></div>
        <div class="sm:col-span-2"><label class="lbl">Hero sub-text</label><textarea name="hero_subtitle" rows="2" class="inp">{{ old('hero_subtitle', $settings['hero_subtitle']) }}</textarea></div>
        <div class="sm:col-span-2"><label class="lbl">Meta description (SEO)</label><textarea name="meta_description" rows="2" class="inp">{{ old('meta_description', $settings['meta_description']) }}</textarea></div>
        <div><label class="lbl">Rates source name</label><input name="rates_source" value="{{ old('rates_source', $settings['rates_source']) }}" class="inp"></div>
        <div><label class="lbl">Footer text</label><input name="footer_text" value="{{ old('footer_text', $settings['footer_text']) }}" class="inp"></div>
        <div><label class="lbl">Contact email</label><input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" class="inp"></div>
        <div><label class="lbl">Contact phone</label><input name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}" class="inp"></div>
        <div class="sm:col-span-2"><label class="lbl">Comparison note</label><input name="compare_note" value="{{ old('compare_note', $settings['compare_note']) }}" class="inp"></div>
      </div>
    </div>

    <div class="card p-4 sm:p-6 space-y-4">
      <h2 class="font-bold text-sm">Market ticker &amp; calculator</h2>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div><label class="lbl">USD / BDT</label><input type="number" step="0.01" name="usd_bdt" value="{{ old('usd_bdt', $settings['usd_bdt']) }}" class="inp mono"></div>
        <div><label class="lbl">Gold intl. ($/oz)</label><input type="number" step="0.01" name="gold_int_usd" value="{{ old('gold_int_usd', $settings['gold_int_usd']) }}" class="inp mono"></div>
        <div><label class="lbl">Silver intl. ($/oz)</label><input type="number" step="0.01" name="silver_int_usd" value="{{ old('silver_int_usd', $settings['silver_int_usd']) }}" class="inp mono"></div>
        <div><label class="lbl">VAT %</label><input type="number" step="0.01" name="vat_percent" value="{{ old('vat_percent', $settings['vat_percent']) }}" class="inp mono" required></div>
        <div><label class="lbl">Default making %</label><input type="number" step="0.1" name="default_making_percent" value="{{ old('default_making_percent', $settings['default_making_percent']) }}" class="inp mono" required></div>
        <div><label class="lbl">Grams per vhori</label><input type="number" step="0.001" name="vhori_grams" value="{{ old('vhori_grams', $settings['vhori_grams']) }}" class="inp mono" required></div>
      </div>
      <p class="text-[11px] text-gray-500">Leave a ticker value empty to hide it from the top bar.</p>
    </div>

    <button class="btn btn-primary">Save settings</button>
  </form>

  <form method="POST" action="{{ route('admin.settings.password') }}" class="card p-4 sm:p-6 space-y-4 max-w-4xl">
    @csrf @method('PUT')
    <h2 class="font-bold text-sm">Change my password</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div><label class="lbl">Current password</label><input type="password" name="current_password" class="inp" required autocomplete="current-password"></div>
      <div><label class="lbl">New password</label><input type="password" name="password" class="inp" required autocomplete="new-password"></div>
      <div><label class="lbl">Confirm new password</label><input type="password" name="password_confirmation" class="inp" required autocomplete="new-password"></div>
    </div>
    <button class="btn btn-light">Update password</button>
  </form>
@endsection
