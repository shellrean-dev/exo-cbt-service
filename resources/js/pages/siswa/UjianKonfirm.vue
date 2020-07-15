<template>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-8">
				<div class="page-inner mt--5">
					<div class="card">
						<div class="card-header">
							<h4>Konfirmasi Data Peserta</h4>
						</div>
						<div class="card-body">
							<form id="fmToken" name="fmToken" @submit.prevent="cekToken" class="form-custom form-ajax">
								<div class="form-group">
									<label for="nisn">NO UJIAN</label>
									<p class="form-control-static" v-text="peserta.no_ujian"></p>
								</div>
								<div class="form-group">
									<label for="nama">Nama Peserta</label>
									<p class="form-control-static" v-text="peserta.nama"></p>
								</div>
                                <template v-if="jadwal && ujian">
    								<div class="form-group">
    									<label for="nm_uji">Mata Ujian</label>
    									<p class="form-control-static" v-if="typeof jadwal.matpel != 'undefined'" v-text="jadwal.matpel"></p>
    									<p class="form-control-static" v-if="typeof jadwal.matpel == 'undefined'">Tidak ada jadwal ujian pada hari ini</p>
    									<span class="line"></span>
    								</div>
    								<div class="form-group" v-if="typeof jadwal.matpel != 'undefined' && typeof ujian.status_ujian != 'undefined' && ujian.status_ujian != '1'">
    									<label for="token">Token</label>
    									<input type="text" class="form-control" autofocus="" placeholder="Masukkan token" v-model="token_ujian">
    									<span class="line"></span>
    									<small class="text-danger" v-if="invalidToken.token">Token tidak sesuai</small>
    									<small class="text-danger" v-if="invalidToken.release">Status token belum dirilis</small>
    								</div>
    								<div class="form-group" v-if="typeof jadwal.matpel != 'undefined' && typeof ujian.status_ujian != 'undefined' && ujian.status_ujian != '1'">
    									<b-button variant="info" type="submit" block :disabled="isLoading">
    										{{ isLoading ? 'Processing...' : 'Submit' }}
    									</b-button>
    								</div>
                                </template>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template> 
<script>
	import { mapActions, mapState,mapGetters, mapMutations } from 'vuex'
    import { successToas, errorToas} from '../../entities/notif'

	export default {
		name: 'KonfirmUjian',
	    data() {
	      return {
	        token_ujian : '',
	        timeout: 0
	      } 
	    },
	    computed: {
	    	...mapGetters(['isAuth','isLoading']),
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
	      ...mapActions('siswa_ujian',['tokenChecker']),
	      cekToken(){
	      	this.tokenChecker({
	      		token: this.token_ujian
	      	})
	      	.then((res) => {
	      		this.$router.replace({ name: 'ujian.prepare' })
	      	})
	      	.catch((error) => {
	      		this.$bvToast.toast(error.message, errorToas())
	      	})
	      }
	    }
	}
</script>