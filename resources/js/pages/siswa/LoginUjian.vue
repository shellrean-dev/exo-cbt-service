<template>
  <div>
    <div class="pt-6 pb-24 shadow-sm border-gray-300 px-4 bg-blue-400 text-white">
      <div class="flex justify-between flex-col sm:flex-row">
        <div class="flex items-center space-x-1"
        v-if="typeof setting.sekolah != 'undefined'" >
          <div class="h-16 w-16 bg-white py-1 px-1 rounded-md items-center justify-center flex">
            <img
            :src="setting.sekolah.logo != ''
            ? '/storage/'+setting.sekolah.logo
            : '/img/tutwuri.jpg'"
            class="h-12 w-12 object-cover" />
          </div>
          <div class="flex flex-col">
            <p class="font-semibold">{{ setting.sekolah.nama != '' ? setting.sekolah.nama : 'ExtraordinaryCBT' }}</p>
            <p class="text-sm text-gray-100">CBT-Application</p>
          </div>
        </div>
      </div>
    </div>
    <div class="container md:mx-auto flex flex-col justify-center space-y-4 lg:flex-row lg:space-y-0 lg:space-x-4 -mt-24">
      <div class="w-full lg:max-w-lg lg:py-4 lg:px-4 mb-20">
        <div class="bg-white border-2 border-gray-300 shadow rounded-t-lg rounded-b-lg">
          <div class="pt-2 pb-2 px-2 flex justify-between border-b border-gray-300 mb-2 items-center">
            <div class="flex items-center">
              <p class="font-medium text-gray-700 text-lg px-2">Login</p>
            </div>
            <div class="flex justify-end space-x-2 mb-2 items-center">

            </div>
          </div>
          <div class="py-2 px-2 my-2">
            <form class="auth-form" @submit.prevent="postLogin">
              <div class="mb-4">
                <label for="" class="text-xs font-semibold text-gray-500 px-1">Nomor Ujian</label>
                <div class="flex">
                  <input v-model="data.no_ujian" type="text" class="w-full pl-4 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-blue-300" :class="{ 'border-red-300' : errors.no_ujian }" placeholder="Nomor Ujian" required=""
                  @keyup="clearError">
                </div>
                <div class="text-xs text-red-600" v-if="errors.no_ujian">{{ errors.no_ujian[0] }}</div>
              </div>
              <div class="mb-4">
                <label for="" class="text-xs font-semibold text-gray-500 px-1">Password</label>
                <div class="flex">
                  <input v-model="data.password" type="password" class="w-full pl-4 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-blue-300" :class="{ 'border-red-300' : errors.password }" placeholder="*******" required=""
                  @keyup="clearError">
                </div>
                <div class="text-xs text-red-600" v-if="errors.password">{{ errors.password[0] }} </div>
              </div>
              <p
              v-if="typeof errors.invalid != 'undefined' && errors.invalid != ''" class="text-red-600 py-2 px-4 bg-red-100 border border-red-300 rounded-md mb-2" v-text="errors.invalid"></p>
              <div class="">
                <button class="py-2 px-4 text-center bg-blue-400 text-white rounded-md active:outline-none hover:shadow-lg"
                :disabled="isLoading"
                :class="{'bg-blue-300' : isLoading}">
                {{ isLoading ? 'Loading...' : 'Login' }}</button>
              </div>
            </form>
          </div>
          <div class="py-2 px-2 flex justify-between border-t border-gray-300 items-center">
          </div>
        </div>
      </div>
    </div>
    <div class="fixed bottom-0 left-0 w-full border-t border-gray-300 text-gray-600 py-2 px-4 text-center bg-white">
	    <span class="text-sm">&copy; 2021 extraordinary-cbt by shellrean</span>
    </div>
  </div>
</template>
<script>
import { mapActions, mapMutations, mapGetters, mapState } from 'vuex'
import { showSweetError } from '../../entities/alert'
 export default {
	  data() {
		  return {
			  data: {
				  no_ujian: '',
				  password: ''
			  }
		  }
	  },
	  created() {
		  if (this.isAuth) {
			  this.$router.replace({ name: 'ujian.konfirm' })
		  }
	  },
	  computed: {
		  ...mapGetters(['isAuth','isLoading','setting']),
		  ...mapState(['errors'])
	  },
	  methods: {
		  ...mapActions('siswa_auth',['submit']),
		  ...mapMutations(['CLEAR_ERRORS','SET_LOADING']),
		  async postLogin() {
        try {
          const network = await this.submit(this.data)
          if (this.isAuth) {
            this.$store.commit('siswa_user/ASSIGN_PESERTA_DETAIL',network.data)
            this.$router.replace({ name: 'ujian.konfirm' })
          }
        } catch (err) {
          showSweetError(this, err)
        }
		  },
		  clearError() {
			  this.CLEAR_ERRORS()
		  }
	  },
    watch: {
      errors(v) {
        console.log(v)
      }
    }
  }
</script>
