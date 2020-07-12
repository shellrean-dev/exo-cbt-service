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
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="page-inner mt--5">
					<div class="card card-bg" v-if="jadwal">
						<div class="card-body">
							<p>Tombol MULAI hanya akan muncul apabila waktu sekarang sudah melewati waktu mulai tes</p>
							<button type="button" class="btn btn-info w-100 rounded-pill btn-form-ajax" @click="start" v-if="!disable">MULAI</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import { mapActions, mapState } from 'vuex'
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
	    start() {
	    	this.pesertaMulai()
	    	this.$router.replace({ 
	    		name: 'ujian.while'
	    	})
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