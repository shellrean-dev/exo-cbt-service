<template>
  <div class="container md:mx-auto flex flex-col justify-center space-y-4 lg:flex-row lg:space-y-0 lg:space-x-4 -mt-12 sm:-mt-24">
    <div class="w-full lg:max-w-xl lg:py-4 lg:px-4 mb-5 lg:mb-20"
    v-if="typeof setting.text != 'undefined' && setting.text.welcome != null && setting.text.welcome != ''"
    >
      <div class="bg-white border-gray-200 shadow sm:shadow-2xl py-2 rounded-t-xl rounded-b-xl">
        <div class="py-2 px-4 rounded-md mb-2" v-html="setting.text.welcome">
        </div>
      </div>
    </div>
    <div class="w-full lg:max-w-xl lg:py-4 lg:px-4 mb-20">
      <div class="bg-white border-gray-200 shadow sm:shadow-2xl py-2 rounded-t-xl rounded-b-xl">
        <div class="flex pt-2 pb-2">
          <div class="px-2 py-1 border-b-2 mb-2 cursor-pointer"
          :class="tab == 0 ? 'border-blue-400' : 'border-gray-200'"
          @click="tab = 0"
          >
            <p class="font-medium text-gray-700 px-2">Ujian</p>
          </div>
          <div class="px-2 py-1 border-b-2 mb-2 cursor-pointer"
          :class="tab == 1 ? 'border-blue-400' : 'border-gray-200'"
          @click="tab = 1"
          >
            <p class="font-medium text-gray-700 px-2">Hasil</p>
          </div>
          <div class="flex-1 py-2 border-b-2 border-gray-200 mb-2">
          </div>
        </div>
		    <div class="py-2 px-2 my-2 border-b border-gray-200"
        v-if="tab == 0"
        >
			    <div class="mb-4">
			      <label for="" class="text-xs font-semibold text-gray-500 px-1">Nama Peserta</label>
			      <p class="font-semibold text-gray-700 px-1">{{ peserta.nama }}</p>
			    </div>
			    <div class="mb-4">
			      <label for="" class="text-xs font-semibold text-gray-500 px-1">Nomor Ujian</label>
			      <p class="font-semibold text-gray-700 px-1">{{ peserta.no_ujian }}</p>
			    </div>
          <form @submit.prevent="ujianStart" v-if="jadwal && jadwal.length > 0">
            <template v-if="jadwalAllowEnter.length > 0">
              <div class="mb-4">
                <label for="" class="text-xs font-semibold text-gray-500 px-1">Jadwal Ujian</label>
                <div class="">
                  <div class="w-full">
                    <select v-model="data.jadwal_id" @change="checkToken()" class="text-gray-700 w-full pl-4 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-blue-300" :readonly="isLoading" :class="{'bg-gray-50' : isLoading}">
                      <option :value="jad.id" v-for="jad in jadwalAllowEnter" :key="jad.id">
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
                <button type="submit" class="py-2 px-4 text-center bg-blue-400 text-white rounded-md hover:shadow-lg" :class="{ isLoading: 'bg-blue-300' }">{{ isLoading ? 'Loading...' : 'Masuk' }}</button>
              </div>
            </template>
          </form>
          <template v-if="jadwal && jadwal.length > 0">
            <template v-if="jadwalNotAllowEnter.length">
              <div class="py-2 px-4 bg-blue-100 border border-gray-300 text-blue-600 rounded-md">
                Anda bisa masuk ke ruang tunggu ujian berikut saat jadwal - 10 menit
              </div>
              <table class="min-w-max w-full table-auto">
                <tbody class="text-gray-600 text-sm font-light">
                <tr class="border-b border-gray-200 hover:bg-gray-100"
                    v-for="(jadwal, index) in jadwalNotAllowEnter"
                    :key="index"
                >
                  <td class="py-3 px-6 text-left whitespace-nowrap">
                    <div class="flex items-center">
                      <span class="font-medium">{{ index+1 }}</span>
                    </div>
                  </td>
                  <td class="py-3 px-6 text-left whitespace-nowrap">
                    <div class="flex items-center">
                      <span class="font-medium">{{ jadwal.alias }}</span>
                    </div>
                  </td>
                  <td class="py-3 px-6 text-left">
                    <div class="flex items-center">
                      <span>{{ jadwal.mulai }}</span>
                    </div>
                  </td>
                </tr>
                </tbody>
              </table>
            </template>
          </template>
          <div v-else class="py-2 px-4 bg-yellow-100 border border-gray-300 text-yellow-600 rounded-md">
            Tidak ada mata ujian untuk anda saat ini
          </div>
			  </div>
        <div class="py-2 px-2 my-2 border-b border-gray-200"
        v-if="tab == 1"
        >
          <table class="min-w-max w-full table-auto">
            <thead>
              <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Ujian</th>
                <th class="py-3 px-6 text-left">Hasil</th>
              </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
              <tr class="border-b border-gray-200 hover:bg-gray-100"
              v-for="(hasil, index) in hasils"
              :key="index"
              >
                <td class="py-3 px-6 text-left whitespace-nowrap">
                  <div class="flex items-center">
                    <span class="font-medium">{{ hasil.alias }}</span>
                  </div>
                </td>
                <td class="py-3 px-6 text-left">
                  <div class="flex items-center">
                    <span>{{ hasil.hasil }}</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
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
        active_token: true,
        tab: 0
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
	  		invalidToken: state => state.invalidToken,
        hasils: state => state.hasils,
	  	}),
      jadwalAllowEnter() {
        if(!this.jadwal) {
          return []
        }
        if (typeof this.jadwal != "object") {
          return []
        }
        return this.jadwal.filter(jdwl => {
          let diffMins = this.getDiffMins(jdwl);
          if (diffMins <= 10) {
            return true;
          }
          return false;
        });
      },
      jadwalNotAllowEnter() {
        if(!this.jadwal) {
          return []
        }
        if (typeof this.jadwal != "object") {
          return []
        }
        return this.jadwal.filter(jdwl => {
          let diffMins = this.getDiffMins(jdwl);
          if (diffMins > 10) {
            return jdwl;
          }
        });
      }
	  },
	  methods: {
	    ...mapActions('siswa_ujian',[ 'startUjian', 'getPesertaUjian','getHasilUjian']),
      showError(err) {
        showSweetError(this, err)
      },
      getDiffMins(jadwal) {
        const date = new Date()
        const ye = date.getFullYear()
        const mo = date.getMonth()
        const da = date.getDate()
        const mulai = jadwal.mulai
        const splicer = mulai.split(":")
        const h = parseInt(splicer[0])
        const i = parseInt(splicer[1])
        const s = parseInt(splicer[2])
        const rest = new Date(ye,mo,da,h,i,s)

        let today = new Date();
        let diffMs = (rest - today);
        let diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000);

        return diffMins;
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
	  },
    created() {
      this.getHasilUjian();
    }
	}
</script>
