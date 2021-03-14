<template>
  <div class="container md:mx-auto flex flex-col justify-center space-y-4 lg:flex-row lg:space-y-0 lg:space-x-4 -mt-12 sm:-mt-24">
    <div class="w-full lg:max-w-xl lg:py-4 lg:px-4 mb-20">
      <div class="bg-white border-gray-200 shadow sm:shadow-2xl py-2 rounded-t-xl rounded-b-xl">
        <div class="pt-2 pb-2 px-2 flex justify-between border-b border-gray-200 mb-2 items-center">
          <div class="flex items-center">
            <p class="font-medium text-gray-700 px-2 text-xl">Konfirmasi data peserta</p>
          </div>
          <div class="flex justify-end space-x-2 mb-2 items-center">

          </div>
        </div>
		    <div class="py-2 px-2 my-2 border-b border-gray-200">
          <div class="py-2 px-4 bg-blue-100 border border-gray-300 text-blue-600 rounded-md" v-if="typeof setting.text != 'undefined' && setting.text.welcome != null && setting.text.welcome != ''" v-html="setting.text.welcome">
          </div>
			    <div class="mb-4">
			      <label for="" class="text-xs font-semibold text-gray-500 px-1">Nama Peserta</label>
			      <p class="font-semibold text-gray-700 px-1">{{ peserta.nama }}</p>
			    </div>
			    <div class="mb-4">
			      <label for="" class="text-xs font-semibold text-gray-500 px-1">Nomor Ujian</label>
			      <p class="font-semibold text-gray-700 px-1">{{ peserta.no_ujian }}</p>
			    </div>
          <form @submit.prevent="ujianStart" v-if="jadwal && jadwal.length > 0">
            <div class="mb-4">
              <label for="" class="text-xs font-semibold text-gray-500 px-1">Jadwal Ujian</label>
              <div class="">
                <div class="w-full">
                  <select v-model="data.jadwal_id" @change="checkToken()" class="text-gray-700 w-full pl-4 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-blue-300" :readonly="isLoading" :class="{'bg-gray-50' : isLoading}">
                    <option :value="jad.id" v-for="jad in jadwal" :key="jad.id">
                      {{ jad.alias }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
            <div class="mb-4" v-if="active_token">
              <label for="" class="text-xs font-semibold text-gray-500 px-1">Token</label>
              <div class="flex">
                <input type="text" v-model="data.token" class="w-full pl-4 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-blue-300" placeholder="Token" required="" :readonly="isLoading" :class="{'bg-gray-50' : isLoading}">
              </div>
            </div>
            <div class="">
              <button type="submit" class="py-2 px-4 text-center bg-blue-400 text-white rounded-md hover:shadow-lg" :class="{ isLoading: 'bg-blue-300' }">{{ isLoading ? 'Loading...' : 'Submit' }}</button>
            </div>
          </form>
          <div v-else class="py-2 px-4 bg-blue-100 border border-gray-300 text-blue-600 rounded-md">
            Tidak ada mata ujian untuk anda saat ini
          </div>
			  </div>
		  </div>
    </div>
  </div>
</template>
<script>
	import { mapActions, mapState,mapGetters, mapMutations } from 'vuex'
  import { showSweetError } from '../../entities/alert'

	export default {
		name: 'KonfirmUjian',
	  data() {
	    return {
	      timeout: 0,
        data: {
          jadwal_id: '',
          token: ''
        },
        active_token: true
	    }
	  },
	  computed: {
	  	...mapGetters(['isAuth','isLoading','setting']),
	  	...mapState('siswa_jadwal', {
	  		jadwal: state => state.banksoalAktif
	  	}),
	  	...mapState('siswa_user', {
		    peserta: state => state.pesertaDetail
		   }),
	  	...mapState('siswa_ujian', {
	  		ujian: state => state.dataUjian,
	  		invalidToken: state => state.invalidToken
	  	})
	  },
	  methods: {
	    ...mapActions('siswa_ujian',[ 'startUjian', 'getPesertaUjian']),
      showError(err) {
        showSweetError(this, err)
      },
      async ujianStart(){
        try {
          await this.startUjian(this.data)
          await this.getPesertaUjian()
          this.$router.replace({ name: 'ujian.prepare' })
        } catch (error) {
          this.showError(error)
        }
      },
      checkToken() {
        let jadwal = this.jadwal.find(x => x.id == this.data.jadwal_id)
        if(jadwal) {
          if(jadwal.setting.token == "1") {
            this.active_token =  true
          } else {
            this.active_token = false
          }
        }
      }
	  }
	}
</script>
