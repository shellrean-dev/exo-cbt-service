<template>
  <div class="fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>
      <span class="hidden sm:inline-block sm:align-middle " aria-hidden="true">&#8203;</span>
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full sm:max-w-lg sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
	        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6 " id="modal-headline">
            Navigasi soal
          </h3>
          <div class="grid grid-cols-4 sm:grid-cols-8 gap-4 mb-4">
			      <button
            v-for="(fiel,index) in filleds" :key="index"
            @click="$emit('toland', index)" :disabled="isLoadinger"
            class="w-full h-12 flex items-center justify-center rounded-md border-2 font-semibold relative hover:shadow-lg"
            :class="{
              'border-gray-300 text-gray-700': (fiel.jawab == 0 && fiel.esay == '' && fiel.jawab_complex.length == 0 && fiel.ragu_ragu != 1),
              'border-green-300 text-green-700': ((fiel.jawab != 0 || fiel.esay != '' || fiel.jawab_complex.length != 0) && fiel.ragu_ragu != 1),
              'border-yellow-300 text-yellow-700': (fiel.ragu_ragu == 1)
            }"
            >
				      <div class="h-4 w-4 rounded-full absolute -top-2 -right-2 bg-gray-100 border-2 border-gray-400"
              v-show="(fiel.jawab == 0 && fiel.esay == '' && fiel.jawab_complex.length == 0 && fiel.ragu_ragu != 1)"
              ></div>
              <div class="h-4 w-4 rounded-full absolute -top-2 -right-2 bg-green-100 border-2 border-green-400"
              v-show="(fiel.jawab != 0 || fiel.esay != '' || fiel.jawab_complex.length != 0 && fiel.ragu_ragu != 1)"
              ></div>
              <div class="h-4 w-4 rounded-full absolute -top-2 -right-2 bg-yellow-100 border-2 border-yellow-400"
              v-show="(fiel.ragu_ragu == 1)"
              ></div>
				      {{ index+1 }}
			      </button>
		      </div>
		      <div class="flex flex-col sm:flex-row justify-between">
			      <div class="flex flex-items items-center space-x-2">
              <div class="h-4 w-4 rounded-full bg-gray-100 border-2 border-gray-400"></div>
              <p class="text-sm">Belum diisi</p>
            </div>
            <div class="flex flex-items items-center space-x-2">
              <div class="h-4 w-4 rounded-full bg-green-100 border-2 border-green-400"></div>
              <p class="text-sm">Sudah diisi</p>
            </div><div class="flex flex-items items-center space-x-2">
              <div class="h-4 w-4 rounded-full bg-yellow-100 border-2 border-yellow-400"></div>
              <p class="text-sm">ragu-ragu</p>
            </div>
          </div>
        </div>
        <div class="px-4 border-t-2 border-dashed py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
          @click="$emit('close')"
          type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapState, mapGetters } from 'vuex'
export default {
  computed: {
    ...mapGetters([
      'isLoading',
      'isLoadinger'
    ]),
    ...mapState('siswa_ujian', {
      filleds: state => state.filledUjian.data
    })
  }
}
</script>
