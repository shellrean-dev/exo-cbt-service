<template>
	<div class="wrapper overlay-sidebar">
      <div class="content">
        <div class="panel-header bg-info-gradient">
          <div class="page-inner py-5">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
              <div class="logo">
                <img src="/img/logo-white.png">
                <h2 class="text-white pb-2 fw-bold">ExtraordinaryCBT</h2>
              </div>
            </div>
          </div>
        </div>
        <div class="container">
        	<div class="row justify-content-center">
				<div class="col-lg-5">
					<div class="card mt--5">
						<div class="card-body">
							<h4>Selamat Datang</h4>
							<p>Silahkan login dengan username dan password yang anda miliki</p>
							<form class="auth-form" @submit.prevent="postLogin">
								<div class="input-group mb-3">
									<div class="input-group-prepend rounded-0">
						                <span class="input-group-text rounded-0">
						                  <i class="cil-mood-good"></i>
						                </span>
						            </div>
									<input type="text" autofocus="" class="form-control active" :class="{ 'is-invalid' : errors.no_ujian }" v-model="data.no_ujian" placeholder="No peserta" required @keyup="clearError"/>
									<div class="invalid-feedback" v-if="errors.no_ujian">{{ errors.no_ujian[0] }}</div>
								</div>
								<div class="input-group mb-3">
									<div class="input-group-prepend rounded-0">
						                <span class="input-group-text rounded-0">
						                  <i class="cil-lock-locked"></i>
						                </span>
						            </div>
									<input type="password" class="form-control":class="{ 'is-invalid' : errors.password }"placeholder="Password" v-model="data.password" required @keyup="clearError"/>
									<div class="invalid-feedback" v-if="errors.password">{{ errors.password[0] }} </div>
									
								</div>
								<p v-if="errors" class="text-danger mb-2" v-text="errors.invalid"></p>
								<b-button variant="info" size="lg" block  :disabled="isLoading" type="submit">
									{{ isLoading ? 'Loading..' : 'Login' }}
								</b-button>
							</form>
						</div>
						<div class="card-footer">
						</div>
					</div>
				</div>
			</div>
        </div>
        <div class="nav-fixed-bottom">
        <p class="text-center">&copy; ExtraordinaryCBT 2020 by Shellrean & ICT Team</p>
      </div>
    </div>
</div>
</template>
<script>
	import { mapActions, mapMutations, mapGetters, mapState } from 'vuex'
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
			...mapGetters(['isAuth','isLoading']),
			...mapState(['errors'])
		},
		methods: {
			...mapActions('siswa_auth',['submit']),
			...mapMutations(['CLEAR_ERRORS','SET_LOADING']),
			postLogin() {
				this.SET_LOADING(true)
				this.submit(this.data)
				.then((response) => {
					if (this.isAuth) {
						this.$store.commit('siswa_user/ASSIGN_PESERTA_DETAIL',response.data)
						this.CLEAR_ERRORS()
						this.$router.replace({ name: 'ujian.konfirm' })
					}
				})
				.catch( () => {
					
				})
			},
			clearError() {
				this.CLEAR_ERRORS()
			}
		}
	}
</script>