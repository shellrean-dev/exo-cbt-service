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
                <a href="#" class="text-white btn btn-round mr-2">{{ peserta.nama }}</a>
                <a href="#" @click.prevent="logout" class="btn btn-light btn-round">
                {{ isLoading ? 'Loading...' : 'Logout' }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <router-view></router-view>
      <div class="nav-fixed-bottom">
        <p class="text-center">&copy; ExtraordinaryCBT 2020 by Shellrean & ICT Team</p>
      </div>
  </div>
</template>
<script>
  import { mapState, mapActions, mapGetters } from 'vuex'

	export default {
		name: 'IndexUjian',
    created() {
      if(typeof this.peserta.id != 'undefined') {
        this.ujianAktif()
      }
    },
    data() {
      return {

      } 
    },
    computed: {
      ...mapGetters(['isLoading']),
      ...mapState('siswa_user', {
        peserta: state => state.pesertaDetail
      }),
      ...mapState('siswa_jadwal', {
        jadwal: state => state.banksoalAktif
      }),
    },
    methods: {
      ...mapActions('siswa_jadwal',['ujianAktif']),
      ...mapActions('siswa_auth',['logoutPeserta']),
      ...mapActions('siswa_ujian',['getPesertaDataUjian']),
      logout() { 
        return new Promise((resolve, reject) => {
            this.logoutPeserta()
            .then(() => {
              localStorage.removeItem('token')
              resolve()
            })
            .catch(() => {
              localStorage.removeItem('token')
              resolve()
            })
        }).then(() => {
            this.$store.state.token = localStorage.getItem('token')
            this.$router.push('/')
        })
      },
      dataUjianPeserta() {
        this.getPesertaDataUjian()
      }
    },
    watch: {
      peserta() {
        this.ujianAktif()
      },
      jadwal(val) {
         if(typeof(val) != 'undefined') {
            this.dataUjianPeserta()
          }
      }
    }
	}
</script>
