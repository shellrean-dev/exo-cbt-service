<template>
  <div class="container md:mx-auto flex flex-col justify-center space-y-4 lg:flex-row lg:space-y-0 lg:space-x-4 -mt-12 sm:-mt-24">
    <div class="w-full lg:max-w-xl lg:py-4 lg:px-4 mb-20">
      <div class="bg-white border-2 border-gray-300 shadow rounded-t-lg rounded-b-lg">
        <div class="pt-2 pb-2 px-2 flex justify-between border-b border-gray-300 mb-2 items-center">
          <div class="flex items-center">
            <p class="font-medium text-gray-700 px-2 text-xl">Ruang tunggu ujian</p>
          </div>
          <div class="flex justify-end space-x-2 mb-2 items-center">

          </div>
        </div>
		    <div class="py-2 px-2 my-2 border-b border-dashed border-gray-300">
			    <div class="mb-4">
			      <label for="" class="text-xs font-semibold text-gray-500 px-1">Ujian</label>
			      <p class="font-semibold text-gray-700 px-1">{{ datad.alias }}</p>
			    </div>
          <div class="mb-4">
			      <label for="" class="text-xs font-semibold text-gray-500 px-1">Tanggal</label>
			      <p class="font-semibold text-gray-700 px-1">{{ datad.tanggal }}</p>
			    </div>
          <div class="mb-4">
			      <label for="" class="text-xs font-semibold text-gray-500 px-1">Waktu mulai</label>
			      <p class="font-semibold text-gray-700 px-1">{{ datad.mulai }}</p>
			    </div>
          <div class="mb-4">
            <label for="" class="text-xs font-semibold text-gray-500 px-1">Durasi pengerjaan</label>
            <p class="font-semibold text-gray-700 px-1">{{ Math.floor(datad.lama / 60) }} menit</p>
          </div>
          <div class="">
            <button class="py-2 px-4 text-center bg-blue-400 text-white rounded-md"  @click="start" v-if="!disable" :disabled="isLoading">{{ isLoading ? 'Loading...' : 'Mulai' }}</button>
          </div>
			    <div class="py-1 px-1 flex space-x-1 bg-gray-100 border border-gray-200 rounded-md" v-if="disable">
				    <div class="">
					    <img src="/img/among.svg" class="h-10"/>
				    </div>
				    <div class="text-gray-600 flex-1">
					    Tombol mulai akan muncul saat waktu ujian tiba, sebelum itu berdoa terlebih dahulu
            </div>
				  </div>
			  </div>
		  </div>
    </div>
  </div>
</template>
<script>
import { mapActions, mapState, mapGetters } from 'vuex'
import { showSweetError } from '../../entities/alert'

export default {
  name: 'PrepareUjian',
  data() {
    return {
      disable: true,
      time: '',
      starter: '',
      durasi: '',
      datad: {}
    }
  },
  computed: {
    ...mapGetters(['isLoading']),
    ...mapState('siswa_ujian',{
      ujian: state => state.ujian
    }),
    ...mapState('siswa_jadwal', {
      jadwal: state => state.banksoalAktif
    }),
  },
  methods: {
    ...mapActions('siswa_ujian',['pesertaMulai']),
    showError(err) {
      showSweetError(this, err)
    },
    async start() {
      try {
        await this.pesertaMulai()
        this.$router.replace({
            name: 'ujian.while'
        })
      } catch (error) {
        this.showError(error)
      }
    },
    getDataUjian() {
      try {
        this.changeData()
      } catch (error) {
        this.showError(error)
      }
    },
    startTime() {
      setInterval( () => {
        this.time = new Date()
      }, 1000 )
    },
    changeData() {
      if(typeof this.ujian.jadwal_id != 'undefined') {
        if(this.jadwal.length != 'undefined') {
          let index;
          if(this.jadwal.length == 1) {
            index = 0;
          } else {
            index = this.jadwal.map(item => item.id).indexOf(this.ujian.jadwal_id)
          }
          if(index !== -1) {
            this.datad = this.jadwal[index]
            const date = new Date()
            const ye = date.getFullYear()
            const mo = date.getMonth()
            const da = date.getDate()
            const mulai = this.datad.mulai
            const splicer = mulai.split(":")
            const h = parseInt(splicer[0])
            const i = parseInt(splicer[1])
            const s = parseInt(splicer[2])
            const rest = new Date(ye,mo,da,h,i,s)
            this.starter = rest
            this.startTime()
          }
        }
      }
    }
  },
  created() {
    this.getDataUjian()
  },
  watch: {
    ujian() {
      this.getDataUjian()
    },
    time() {
      if(this.starter < this.time) {
        this.disable = false
      }
    },
    jadwal() {
      this.changeData()
    }
  }
}
</script>
