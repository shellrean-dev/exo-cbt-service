<template>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-8">
				<div class="page-inner mt--5">
					<div class="card">
						<div class="card-header">
							<h4>Konfirmasi data peserta</h4>
						</div>
                        <div class="card-body" >
                            <div class="alert alert-primary" v-if="typeof setting.text != 'undefined' && setting.text.welcome != null && setting.text.welcome != ''" v-html="setting.text.welcome">
                            </div>
                            <form @submit.prevent="ujianStart" class="form-custom">
                                <div class="form-group">
                                    <label for="nisn">NO UJIAN</label>
                                    <p class="form-control-static" v-text="peserta.no_ujian"></p>
                                </div>
                                <div class="form-group">
                                    <label for="nama">Nama Peserta</label>
                                    <p class="form-control-static" v-text="peserta.nama"></p>
                                </div>
                                <template v-if="jadwal && jadwal.length > 0">
                                    <div class="form-group">
                                        <label>Jadwal ujian</label>
                                        <select class="form-control" v-model="data.jadwal_id" @change="checkToken()" required>
                                            <option :value="jad.id" v-for="jad in jadwal" :key="jad.id">
                                                {{ jad.alias }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group" v-if="active_token">
                                        <label>Token</label>
                                        <input type="text" class="form-control" placeholder="Masukkan token" autofocus="" v-model="data.token" required>
                                    </div>
                                    <div class="form-group">
                                        <b-button variant="info" type="submit" block :disabled="isLoading">
                                            {{ isLoading ? 'Processing...' : 'Submit' }}
                                        </b-button>
                                    </div>
                                </template>
                                <template  v-if="jadwal && jadwal.length == 0">
                                    <div class="alert alert-info">
                                        Tidak ada mata ujian untuk anda saat ini
                                    </div>
                                </template>
                            </form>
                        </div>
                        <div class="card-footer"></div>
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
	        timeout: 0,
            data: {
                jadwal_id: '',
                token: ''
            },
            active_token: true
	      }
	    },
	    computed: {
	    	...mapGetters(['isAuth','isLoading','setting']),
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
	      ...mapActions('siswa_ujian',[ 'startUjian', 'getPesertaUjian']),
          async ujianStart(){
            try {
                await this.startUjian(this.data)
                await this.getPesertaUjian()
                this.$router.replace({ name: 'ujian.prepare' })
            } catch (error) {
                this.$bvToast.toast(error.message, errorToas())
            }
          },
          checkToken() {
            let jadwal = this.jadwal.find(x => x.id == this.data.jadwal_id)
            if(jadwal) {
                if(jadwal.setting.token == "1") {
                    this.active_token =  true
                } else {
                    this.active_token = false
                }
            }
          }
	    }
	}
</script>
