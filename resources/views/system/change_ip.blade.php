@extends("system.layoute")
@section("content")
<div class="max-w-sm mx-auto">
  <div class="mb-2">
    <label for="price" class="block text-sm font-medium text-gray-700">IP Address</label>
    <input type="text" name="ip_address" class="border-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-md" placeholder="IP Address Server Or Domain">
  </div>
  <div class="mt-2">
    <button class="bg-gray-800 text-white block w-full py-2 rounded-md mb-1">Simpan</button>
    <a href="{{ route('system.exo.index') }}" class="text-center border-gray-800 border-2 text-gray-800 block w-full py-2 rounded-md">Kembali</a>
  </div>
</div>
@endsection