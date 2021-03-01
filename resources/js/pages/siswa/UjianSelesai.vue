<template>
  <div class="container md:mx-auto flex flex-col justify-center space-y-4 lg:flex-row lg:space-y-0 lg:space-x-4 -mt-12 sm:-mt-24">
    <div class="w-full lg:max-w-xl lg:py-4 lg:px-4 mb-20">
      <div class="bg-white border-2 border-gray-300 shadow rounded-t-lg rounded-b-lg">
        <div class="pt-2 pb-2 px-2 flex justify-between border-b border-gray-300 mb-2 items-center">
          <div class="flex items-center">
            <p class="font-medium text-gray-700 px-2 text-xl">Ujian selesai</p>
          </div>
          <div class="flex justify-end space-x-2 mb-2 items-center">

          </div>
        </div>
        <div class="py-2 px-2 my-2 border-b border-dashed border-gray-300">
          <div
          class="py-1 px-1 flex space-x-1 bg-gray-100 border border-gray-200 rounded-md mb-4 text-gray-600"
          v-if="typeof setting.text != 'undefined' && setting.text.finish != null && setting.text.finish != ''"
          v-html="setting.text.finish">
          </div>
          <div
          v-else
          class="py-1 px-1 flex space-x-1 bg-gray-100 border border-gray-200 rounded-md mb-4">
            <div class="">
              <img src="/img/among2.svg" class="h-10"/>
            </div>
            <div class="text-gray-600 flex-1">
              Anda telah menyelesaikan ujian ini, prestasi penting tetapi jujur yang utama. kembali ke halaman utama bila masih ada ujian selanjutnya.
				    </div>
			    </div>
			    <div class="">
				    <button
            :disabled="isLoading"
            @click="$router.push({name: 'ujian.konfirm' })"
            class="py-2 px-4 text-center bg-blue-400 text-white rounded-md hover:shadow-lg">{{ isLoading ? 'Loading...' : 'Ke halaman utama' }}</button>
			    </div>
	        <!-- <span class="text-xs text-gray-700">Semesta menyuruhku untuk melepaskannya padahal aku belum pernah meraihnya</span> -->
		    </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { showSweetError } from '../../entities/alert'

export default {
  computed: {
    ...mapGetters(['isLoading','setting']),
  },
	methods: {
    ...mapActions('siswa_auth',['logoutPeserta']),
    ...mapActions('siswa_jadwal',['ujianAktif']),
    ...mapActions('siswa_ujian',['getPesertaUjian']),
    showError(err) {
      showSweetError(this, err)
    },
    async logout() {
      try {
        await this.logoutPeserta()
        localStorage.removeItem('token')
        this.$store.state.token = localStorage.getItem('token')
        this.$router.push('/')
      } catch (error) {
        this.showError(error)
      }
    }
  },
  async created() {
    try {
      if(this.$route.name != 'ujian.while') {
        await this.ujianAktif()
        await this.getPesertaUjian()
      }
    } catch (error) {
      this.showError(error)
    }
  },
}
</script>
