@extends("system.layoute")
@section("content")
<form class="max-w-sm mx-auto" method="post" action="{{ route('system.exo.change.ip.store') }}">
  @csrf
  <div class="mb-2">
    <label for="price" class="block text-sm font-medium text-gray-700">Protocol</label>
    <input type="text" name="protocol" class="border-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-md" placeholder="http / https">
  </div>
  <div class="mb-2">
    <label for="price" class="block text-sm font-medium text-gray-700">IP Address</label>
    <input type="text" name="ip_address" class="border-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-md" placeholder="IP Address Server Or Domain">
  </div>
  <div class="mt-2 flex space-x-2">
    <a href="{{ route('system.exo.index') }}" class="text-center border-gray-800 border-2 text-gray-800 block w-full py-2 rounded-md">Kembali</a>
    <button class="bg-gray-800 text-white block w-full py-2 rounded-md">Simpan</button>
  </div>
</form>
@endsection