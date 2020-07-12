<template>
	<div class="container mt--5">
		<div class="row justify-content-center">
			<div class="col-sm-6">
				<div class="card card-bg">
					<div class="card-body">
						<h4>Tes Selesai</h4>
						<p>
						Anda telah selesai mengerjakan ujian ini. Terimakasih, prestasi penting jujur yang utama</p>
						<button type="button" class="btn btn-info w-100 rounded-pill btn-form-ajax" @click="logout">Logout</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import { mapState, mapActions } from 'vuex'
export default {
	methods: {
      ...mapActions('siswa_auth',['logoutPeserta']),
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
    }
}
</script>