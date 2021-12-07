@extends("system.layoute")
@section("content")
  @if(Session::has('updated'))
  <div class="py-3 px-4 max-w-md mx-auto border-2 border-blue-300 rounded-md text-gray-600 mb-4 bg-blue-50">
    <p class="mb-2">Versi terbaru telah rilis v3.0.1.</p>
    <a href="" class="py-2 px-4 bg-blue-400 text-white rounded-md">Update</a>
  </div>
  @endif

  @if(Session::has('changeip'))
  <div class="py-3 px-4 max-w-md mx-auto border-2 border-blue-300 rounded-md text-gray-600 mb-4 bg-blue-50">
    <p>IP / Domain berhasil diubah</p>
  </div>
  @endif
  <div class="flex items-center justify-center space-x-4">
    <div class="grid grid-cols-2 gap-4">
      <a href="{{ route('system.exo.change.ip') }}" class="shadow-sm bg-white border-2 border-gray-300 text-gray-900 px-14 py-6 rounded-md text-sm font-medium text-center bg-gray-50">
        <span class="text-gray-600 font-semibold">Ganti IP</span>
      </a>
      <a href="{{ route('system.exo.check.update') }}" class="shadow-sm bg-white border-2 border-gray-300 text-gray-900 px-14 py-6 rounded-md text-sm font-medium text-center bg-gray-50">
        <span class="text-gray-600 font-semibold">Check Update</span>
      </a>
    </div>
  </div>
@endsection