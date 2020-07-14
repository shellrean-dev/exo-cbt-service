<template>
	<div class="container">
		<div class="row">
			<div class="col-sm-8">
				<div class="page-inner mt--5">
					<div class="card card-bg" v-if="jadwal">
						<div class="card-header">
							<h4>Konfirmasi Tes</h4>								
						</div>
						<div class="card-body">
							<form id="fmTes" name="fmTes" method="POST"  class="form-custom form-ajax">
								<div class="form-group">
									<label>Mata Pelajaran</label>
									<p class="form-control-static">{{jadwal.matpel}}&nbsp;</p>
								</div>
								<div class="form-group">
									<label>Alokasi Waktu Tes</label>
									<p class="form-control-static">{{ Math.floor(jadwal.jadwal.lama / 60)}} Menit &nbsp;</p>
								</div>
								<div class="form-group">
									<label>Waktu mulai</label>
									<p class="form-control-static">{{mulai}}&nbsp;</p>
								</div>
							</form>
						</div>
                        <div class="card-footer"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="page-inner mt--5">
					<div class="card card-bg" v-if="jadwal">
						<div class="card-body">
							<p>Tombol MULAI hanya akan muncul apabila waktu sekarang sudah melewati waktu mulai tes</p>
							<button type="button" class="btn btn-info w-100 rounded-pill btn-form-ajax" @click="start" v-if="!disable" :disabled="isLoading">
                                {{ isLoading ? 'Loading...' : 'MULAI' }}
                            </button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import { mapActions, mapState } from 'vuex'
import { successToas, errorToas} from '../../entities/notif'

export default {
	name: 'PrepareUjian',
	created() {
		this.starTime()
	},
	data() {
		return {
			disable: true,
			time: '',
			starter: '',
			durasi: ''
		}
	},
	computed: {
        ...mapState(['isLoading']),
		...mapState('siswa_jadwal', {
			jadwal: state => state.banksoalAktif,
			mulai: state => state.banksoalAktif.jadwal.mulai
		}),
		...mapState('siswa_user', {
		    peserta: state => state.pesertaDetail
		}),
	},
	methods: {
	    ...mapActions('siswa_ujian',['pesertaMulai']),
	    async start() {
            try {
    	    	await this.pesertaMulai()
    	    	this.$router.replace({ 
    	    		name: 'ujian.while'
    	    	})
            } catch (error) {
                this.$bvToast.toast(error.message, errorToas())
            }
	    },
	    starTime() {
			setInterval( () => {
				this.time = new Date()
			}, 1000 )
		}
	},
	watch: {
		time() {
			if(this.starter < this.time) {
				this.disable = false
			}
		}
	}
}
</script>