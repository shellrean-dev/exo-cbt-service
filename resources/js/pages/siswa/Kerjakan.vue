<template>
	<div>
		<loading :active.sync="isLoading" 
        :is-full-page="true"></loading>

		<div class="container exam mt--5" v-if="filleds">
			<div class="card">
				<div class="card-body">
					<div class="bar-top">
						<span>SOAL NOMOR</span>
						<div class="soal-title" id="page">{{ questionIndex+1 }}</div>
						<div id="page-count" style="display:none"></div>
						<div class="right">
							<div class="timer js-ujian">
								<div class="timer-time" id="timer"><i class="cil-clock"></i> {{ prettyTime }}</div>
							</div>
							<b-button variant="info" class="btn-soal" v-b-modal.nomorSoal :disabled="!listening">
							<span class="cil-apps"></span> Daftar Soal
							</b-button>
						</div>
					</div>
					<div class="bar-text">
						<span>Ukuran Soal :
						</span>
						<b-form-input v-model="range" type="range" min="12" max="30"></b-form-input>
					</div>
					<div class="soal-wrapper" id="content">
						<table class="table table-borderless table-sm">
				    		<tr v-if="audio != ''">
				    			<td colspan="2">
				    				<audio-player v-if="listening" :file="'/storage/audio/'+audio"></audio-player>
				    			</td>
				    		</tr>
				    		<tr>
				    			<td colspan="2" :style="'font-size:'+range+'px !important'" v-html="filleds[questionIndex].soal.pertanyaan"></td>
				    		</tr>
				    		<tr v-for="(jawab,index) in filleds[questionIndex].soal.jawabans" :key="index">
				    			<td width="50px" :style="'font-size:'+range+'px !important'">
				    				<b-form-radio size="lg" v-model="selected" name="jwb" :value="jawab.id"  @change="selectOption(index)">
				    					<span class="text-uppercase">{{ index | charIndex }}</span>.
				    				</b-form-radio>
				    			</td>
				    			<td :style="'font-size:'+range+'px !important'" v-html="jawab.text_jawaban"></td>
				    		</tr>
				    		<tr v-if="filleds[questionIndex].soal.tipe_soal == 2">
				    			<td height="auto">
				    				<textarea class="form-control" placeholder="Tulis jawaban disini..." rows="8" v-model="filleds[questionIndex].esay" @input="onInput($event.target.value)" style="height: 150px"></textarea>
				    			</td>
				    		</tr>
				    	</table>
					</div>
					<div class="button-wrapper">
						<b-button variant="info" class="sebelum" size="md" @click="prev()" v-if="questionIndex != 0" :disabled="isLoadinger || !listening">
							<span class="cil-chevron-left"></span>
							 Sebelumnya
						</b-button>

						<button id="soal-ragu" class="btn btn-warning ml-auto">
							<b-form-checkbox size="lg" value="1" v-model="ragu">Ragu ragu</b-form-checkbox>
						</button>
						<b-button variant="info" class="sesudah" size="md" :disabled="isLoadinger || !listening" @click="next()" v-if="questionIndex+1 != filleds.length">
							Selanjutnya <span class="cil-chevron-right"></span>
						</b-button>
		    			<b-button variant="success" class="sesudah" size="md" @click="$bvModal.show('modal-selesai')" v-if="questionIndex+1 == filleds.length && checkRagu() == false" :disabled="isLoadinger">
		    				SELESAI <i class="cil-check"></i>
		    			</b-button>
		    			<b-button variant="danger" class="sesudah" size="md" v-b-modal.modal-1 v-if="questionIndex+1 == filleds.length && checkRagu() == true">
		    				SELESAI <i class="cil-check"></i>
		    			</b-button>
					</div>
				</div>
			</div>
		</div>
		<b-modal id="modal-selesai" centered class="shadow">
		    <template v-slot:modal-header="{ close }">
		      <h5>Konfirmasi</h5>
		    </template>

		    <template v-slot:default="{ hide }">
			  <b-form-checkbox size="lg" v-model="isKonfirm">Saya sudah selesai mengerjakan</b-form-checkbox>
		    </template>

		    <template v-slot:modal-footer="{ cancel }">
		    	<div class="button-wrapper">
			      <b-button size="sm" variant="success" @click="selesai()" :disabled="!isKonfirm">
			        Selesai
			      </b-button>
			      <b-button size="sm" variant="danger" @click="cancel()">
			        Cancel
			      </b-button>
			    </div>
		    </template>
		 </b-modal>
		<b-modal id="nomorSoal" title="Nomor Soal" size="lg" class="shadow">
			<template v-slot:modal-footer="{ cancel }">
		      <b-button size="sm" variant="info" @click="cancel()">
		        Tutup
		      </b-button>
		    </template>
		    <template v-slot:default="{ hide }">
			 	<ul class="nomor-soal" id="nomor-soal">
			 		<li v-for="(fiel,index) in filleds" :key="index">
			 			<a href="#" :class="{
						'isi' : (fiel.jawab != 0 || fiel.esay != null),
						'ragu' : (fiel.ragu_ragu == 1),
						'active' : (index == questionIndex)}" @click.prevent="toLand(index)" :disabled="isLoadinger">
			 				{{ index+1 }} 
			 				<span></span>
			 			</a>
			 		</li>
				</ul>
		    </template>
		</b-modal>
		<b-modal id="modal-direction" centered title="Direction" class="shadow">
		    <template v-slot:modal-footer="{ cancel }">
		     	<div class="button-wrapper">
			      <b-button size="sm" variant="info" @click="playDirection()">
			        Oke
			      </b-button>
		  		</div>
		    </template>
		    <template v-slot:default="{ hide }">
		    	Listen for direction
		    </template>
		</b-modal>
	</div>
</template>
<script>
import { mapActions, mapState, mapGetters, mapMutations} from 'vuex'
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import AudioPlayer from '../../components/siswa/AudioPlayer.vue'
import _ from 'lodash'

export default {
	name: 'DataUjian',
	created() {
		if(typeof this.jadwal.jadwal != 'undefined') {
			this.filledAllSoal()
			this.start()
		}
	},
	components: {
		AudioPlayer,
		Loading
	},
	data() {
		return {
			questionIndex: '',
			selected: '',
			patt: 17,
			sidebar: false,
			ragu: '',
			time: 0,
			isKonfirm : false,
			interval: '',
			audio: '',
			direction: '',
			listening: true,
			hasdirec: [],
			range: 16,
		}
	},
	filters: {
		charIndex(i) {
			return String.fromCharCode(97 + i)
		}
	},
	computed: {
		...mapGetters(['isAuth','isLoading','isLoadinger']),
		...mapState('siswa_ujian',{ 
			jawabanPeserta: state => state.jawabanPeserta,
			filleds: state => state.filledUjian.data,
			detail: state => state.filledUjian.detail
		}),
		...mapState('siswa_jadwal', {
			jadwal: state => state.banksoalAktif,
		}),
		...mapState('siswa_user', {
        	peserta: state => state.pesertaDetail
      	}),
      	prettyTime () {
			let sec_num = parseInt(this.time, 10)
    		let hours   = Math.floor(sec_num / 3600)
    		let minutes = Math.floor((sec_num - (hours * 3600)) / 60)
    		let seconds = sec_num - (hours * 3600) - (minutes * 60)
    		return hours+':'+minutes+':'+seconds
		}
	},
	methods: {
		...mapActions('siswa_banksoal', ['getUjian']),
		...mapActions('siswa_ujian', ['submitJawaban','submitJawabanEssy','takeFilled','updateWaktuSiswa','updateRaguJawaban','selesaiUjianPeserta']),
		getAllSoal() {
			this.getUjian({
				banksoal: this.$route.params.banksoal,
				peserta: localStorage.getItem('id')
			})
			.then((resp) => {
				
			})
			.catch(() => {
				this.$notify({
                  group: 'foo',
                  title: 'Error',
                  type: 'error',
                  text: 'Terjadi Kesalahan (Error: 00FACCG).'
                })
			})
		},
		filledAllSoal() {
			const payld = {
				peserta_id: this.peserta.id,
				banksoal: this.jadwal.banksoal_id,
				jadwal_id: this.jadwal.ujian_id
			}
			this.takeFilled(payld) 
			.then((resp) => {

			})
			.catch(() => {
				this.$notify({
                  group: 'foo',
                  title: 'Error',
                  type: 'error',
                  text: 'Terjadi Kesalahan (Error: 00FACCF).'
                })
			})
		},
		selectOption(index) {
			const fill = this.filleds[this.questionIndex]

	        this.submitJawaban({ 
	        	jawaban_id : this.filleds[this.questionIndex].id,
	        	jawab : this.filleds[this.questionIndex].soal.jawabans[index].id,
	        	correct: this.filleds[this.questionIndex].soal.jawabans[index].correct,
	        	index : this.questionIndex
	        })
			.catch(() => {
				this.$notify({
                  group: 'foo',
                  title: 'Error',
                  type: 'error',
                  text: 'Sepertinya anda terputus dari server (Error: 00FACCO).'
                })
			})
		},
		raguRagu(val) {
			this.updateRaguJawaban({
				ragu_ragu: val,
				index: this.questionIndex,
				jawaban_id : this.filleds[this.questionIndex].id
			})
		},
		selesai() {
			this.selesaiUjianPeserta({
				peserta_id : this.peserta.id,
				jadwal_id : this.detail.jadwal_id
			})
			this.$router.push({ name: 'ujian.selesai' })
			clearInterval(this.interval); 
		},
		prev() {
		    if (this.filleds.length > 0) this.questionIndex--
		},
		next() {
			if (this.questionIndex < this.filleds.length) this.questionIndex++
		},
		toggle() {
	    	this.sidebar = !this.sidebar;
	    },
	    toLand(index) {
	    	this.questionIndex = index
	    },
	    start () {
			this.timer = setInterval( () => {
				if (this.time > 0) {
					 this.time--
				} else {

				}
			}, 1000 )
		},
		checkRagu() {
			let ragger = 0
			this.filleds
			.filter(function(element) {
			    if (element.ragu_ragu == "1") {
			       ragger++
			    }
			})

			if (ragger > 0) {
				return true
			}
			return false
		},
		inputJawabEssy(val) {
			const fill = this.filleds[this.questionIndex]

	        this.submitJawabanEssy({ 
	        	jawaban_id : this.filleds[this.questionIndex].id,
	        	index : this.questionIndex,
	        	essy: fill.esay
	        })
	        .catch(() => {
	        	this.$notify({
                  group: 'foo',
                  title: 'Error',
                  type: 'error',
                  text: 'Sepertinya anda terputus dari server (Error: 00FACCO).'
                })
	        })
		},
		playDirection() {
			this.listening = false
			this.direction.play()
			this.direction.onended = () => {
				this.hasdirec.push(this.filleds[this.questionIndex].soal.id)
				this.listening = true
			}
			this.$bvModal.hide('modal-direction')
		},
		onInput: _.debounce(function (value) {
	      this.inputJawabEssy(value)
	    }, 500)
	},
	watch: {
		soals(val) {
			this.filledAllSoal()
		},
		questionIndex() {
			this.selected = this.filleds[this.questionIndex
			].jawab
			this.ragu = this.filleds[this.questionIndex].ragu_ragu
			if(this.filleds[this.questionIndex].soal.audio != null) {
				this.audio = this.filleds[this.questionIndex].soal.audio
			} 
			else {
				this.audio = ''
			}

			if(this.filleds[this.questionIndex].soal.direction != null) {
				this.direction = new Audio('/storage/audio/'+this.filleds[this.questionIndex].soal.direction)
			} else {
				if(this.direction != '') {
					this.direction.pause()
				}
				this.direction = ''
			}
		},
		filleds() {
			this.questionIndex = 0
		},
		detail(val) {
			this.time = val.sisa_waktu
			this.interval = setInterval( () => {
				if (this.time > 0) {
					
				} else {
					this.selesai()
				}
			}, 5000 )
		}, 
		ragu(val) {
			if (val == false) {
				const set = 0
				this.raguRagu(set)
			} else {
				this.raguRagu(val)
			}
		},
		direction(val) {
			if(val != '') {
				if(this.hasdirec.includes(this.filleds[this.questionIndex].soal.id)) {
					return
				}
				this.$bvModal.show('modal-direction')
			}
		},
		jadwal(val) {
			this.filledAllSoal()
			this.start()
		}
	}
}
</script>