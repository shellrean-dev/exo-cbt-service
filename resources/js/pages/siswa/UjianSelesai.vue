<template>
	<div class="container mt--5">
		<div class="row justify-content-center">
			<div class="col-sm-6">
				<div class="card card-bg text-center">
                    <div class="card-header">
						<h2 class="ml-auto">Tes Selesai</h2>
                    </div>
					<div class="card-body">
						<p>
						Anda telah selesai mengerjakan ujian ini. <br>
                        Terimakasih, prestasi penting jujur yang utama</p>
                        <br><br>
						<button type="button" class="btn btn-info w-100 rounded-pill btn-form-ajax" @click="logout" :disabled="isLoading">{{ isLoading ? 'Loading...' : 'Logout' }}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { successToas, errorToas} from '../../entities/notif'

export default {
    computed: {
        ...mapGetters(['isLoading']),
    },
	methods: {
      ...mapActions('siswa_auth',['logoutPeserta']),
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
    }
}
</script>