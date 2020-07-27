<template>
	<div class="wrapper overlay-sidebar">
      <div class="content">
        <div class="panel-header bg-info-gradient">
          <div class="page-inner py-5">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row px-3">
              <div class="logo" v-if="typeof setting.sekolah != 'undefined'" >
                <img :src="setting.sekolah.logo != '' ? '/storage/'+setting.sekolah.logo : '/img/logo-white.png'">
                <h2 class="text-white pb-2 fw-bold">{{ setting.sekolah.nama != '' ? setting.sekolah.nama : 'ExtraordinaryCBT' }}</h2>
              </div>
              <div class="ml-md-auto py-2 py-md-0">
                <span class="text-white btn btn-round mr-2">{{ peserta.nama }}</span>
                <a href="#" @click.prevent="logout" class="btn btn-light btn-round">
                {{ isLoading ? 'Loading...' : 'Logout' }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <router-view></router-view>
      <div class="nav-fixed-bottom mb-5">
        <p class="text-center">&copy; ExtraordinaryCBT 2020 v1.0.11 by Shellrean</p>
      </div>
  </div>
</template>
<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { successToas, errorToas} from '../../entities/notif'

export default {
    name: 'IndexUjian',
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
        async logout() { 
            try {
                await this.logoutPeserta()
                localStorage.removeItem('token')
                this.$store.state.token = localStorage.getItem('token')
                this.$router.push('/')
            } catch (error) {
                this.$bvToast.toast(error.message, errorToas())
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
            this.$bvToast.toast(error.message, errorToas())
        }
    },
    watch: {
        uncomplete(val) {
            if(this.$route.name != 'ujian.while' && typeof val.jadwal_id != 'undefined') {
                this.$router.replace({ 
                    name: 'ujian.while'
                })
            }
        }
    }
}
</script>
