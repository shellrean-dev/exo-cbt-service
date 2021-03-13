<template>
  <div>
    <loading
      color="#007bff"
      loader="dots"
      :height="45"
      :width="45"
      :active.sync="isLoading"
      :is-full-page="true"
    ></loading>
    <div class="pt-6 pb-24 shadow-sm border-gray-300 px-4 bg-gradient-to-r from-blue-500 to-blue-400 text-white">
      <div class="flex justify-between flex-col sm:flex-row">
        <div class="flex items-center space-x-1"
        v-if="typeof setting.sekolah != 'undefined'"
        >
          <div class="h-16 w-16 bg-white py-1 px-1 rounded-md items-center justify-center flex">
            <img
            :src="setting.sekolah.logo != ''
            ? '/storage/'+setting.sekolah.logo
            : '/img/exo.jpg'"
            class="h-12 w-12 object-cover"
            />
          </div>
          <div class="flex flex-col">
            <p class="font-semibold">{{ setting.sekolah.nama != '' ? setting.sekolah.nama : 'ExtraordinaryCBT' }}</p>
            <p class="text-sm text-gray-100">CBT-Application</p>
          </div>
        </div>
        <div class="flex space-x-2 justify-end">
          <div class="flex flex-col">
            <p class="font-semibold text-right">{{ peserta.nama }}</p>
            <p class="text-sm text-right">{{ peserta.no_ujian }}</p>
          </div>
          <button class="h-12 w-12 flex items-center justify-center bg-white text-gray-600 rounded-md hover:shadow-lg"
          :class="{ isLoading : 'bg-gray-100' }"
          :disabled="isLoading"
          @click="logout"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-7 feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          </button>
        </div>
      </div>
    </div>
    <router-view></router-view>
    <div class="relative sm:fixed bottom-0 left-0 w-full border-t border-gray-300 text-gray-600 py-2 px-4 text-center bg-white">
	    <span class="text-sm">&copy; 2021 extraordinary-cbt v2.0.0</span>
    </div>
  </div>
</template>
<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { successToas, errorToas} from '../../entities/notif'
import { showSweetError } from '../../entities/alert'
import Loading from 'vue-loading-overlay'

export default {
  name: 'IndexUjian',
  components: {
    Loading,
  },
  computed: {
    ...mapGetters(['isLoading', 'setting']),
    ...mapState('siswa_user', {
      peserta: state => state.pesertaDetail
    }),
    ...mapState('siswa_ujian', {
      uncomplete: state => state.uncomplete
    })
  },
  methods: {
    ...mapActions('siswa_jadwal',['ujianAktif']),
    ...mapActions('siswa_auth',['logoutPeserta']),
    ...mapActions('siswa_ujian',['getPesertaDataUjian', 'getPesertaUjian', 'getUncompleteUjian']),
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
        await this.getUncompleteUjian()
      }
    } catch (error) {
      this.showError(error)
    }
  },
  watch: {
    uncomplete(val) {
      if(this.$route.name != 'ujian.while' && typeof val.jadwal_id != 'undefined') {
        if (val.status_ujian == 3) {
          this.$router.replace({
            name: 'ujian.while'
          })
        } else if (val.status_ujian == 0) {
          this.$router.replace({
            name: 'ujian.while'
          })
        }
      }
    }
  }
}
</script>
