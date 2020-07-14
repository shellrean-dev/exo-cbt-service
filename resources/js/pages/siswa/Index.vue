<template>
	<div class="wrapper overlay-sidebar">
      <div class="content">
        <div class="panel-header bg-info-gradient">
          <div class="page-inner py-5">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row px-3">
              <div class="logo">
                <img src="/img/logo-white.png">
                <h2 class="text-white pb-2 fw-bold">ExtraordinaryCBT</h2>
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
        <p class="text-center">&copy; ExtraordinaryCBT 2020 by Shellrean & ICT Team</p>
      </div>
  </div>
</template>
<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { successToas, errorToas} from '../../entities/notif'

export default {
    name: 'IndexUjian',
    computed: {
        ...mapGetters(['isLoading']),
        ...mapState('siswa_user', {
            peserta: state => state.pesertaDetail
        }),
    },
    methods: {
        ...mapActions('siswa_jadwal',['ujianAktif']),
        ...mapActions('siswa_auth',['logoutPeserta']),
        ...mapActions('siswa_ujian',['getPesertaDataUjian']),
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
            await this.ujianAktif()
            await this.getPesertaDataUjian()
        } catch (error) {
            this.$bvToast.toast(error.message, errorToas())
        }
    }
}
</script>
