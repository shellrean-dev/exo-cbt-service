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
                        <div class="right">
                            <div class="btn-group">
                              <button type="button" class="btn btn-outline-danger btn-soal"><i class="cil-clock"></i> {{ prettyTime }}</button>
                              <b-button variant="info" v-b-modal.nomorSoal :disabled="!listening" class="btn-soal"><span class="cil-apps"></span> Daftar Soal</b-button>
                            </div>
                        </div>
                    </div>
                    <div class="bar-text">
                        <span>Ukuran Soal :
                        </span>
                        <b-form-input v-model="range" type="range" min="12" max="30"></b-form-input>
                    </div>
                    <div class="soal-wrapper" id="content" v-if="typeof filleds[questionIndex] != 'undefined'">
                        <table class="table table-borderless table-sm">
                            <tr v-if="audio != ''">
                                <td colspan="2">
                                    <audio-player v-if="listening" :file="'/storage/audio/'+audio"></audio-player>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" :style="'font-size:'+range+'px !important'"><RenderString :string="changeToZoomer(filleds[questionIndex].soal.pertanyaan)" /></td>
                            </tr>
                            <tr v-for="(jawab,index) in filleds[questionIndex].soal.jawabans" :key="index">
                                <td width="50px" :style="'font-size:'+range+'px !important'">
                                    <b-form-radio size="lg"v-model="selected" name="jwb" :value="jawab.id"  @change="selectOption(index)" v-if="[1,3].includes(parseInt(filleds[questionIndex].soal.tipe_soal))">
                                        <span class="text-uppercase">{{ index | charIndex }}</span>.
                                    </b-form-radio>
                                    <label class="checkbox" v-if="4 == parseInt(filleds[questionIndex].soal.tipe_soal)">
                                      <span class="checkbox__input">
                                        <input :checked="filleds[questionIndex].jawab_complex.includes(jawab.id)" type="checkbox" :value="jawab.id"  name="checkbox"  @change="changeCheckbox($event, index)" :disabled="isLoadinger || isLoading">
                                        <span class="checkbox__control">
                                          <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' aria-hidden="true" focusable="false">
                                            <path fill='none' stroke='currentColor' stroke-width='3' d='M1.73 12.91l6.37 6.37L22.79 4.59' /></svg>
                                        </span>
                                      </span>
                                      <span class="radio__label">{{ index | charIndex }}</span>
                                    </label>
                                </td>
                                <td :style="'font-size:'+range+'px !important'" v-html="jawab.text_jawaban"></td>
                            </tr>
                            <tr v-if="[2,6].includes(filleds[questionIndex].soal.tipe_soal)">
                                <td v-if="filleds[questionIndex].soal.tipe_soal == 6">
                                    <input type="text" v-model="filleds[questionIndex].esay" @keyup="onInput"/>
                                </td>
                                <td height="auto" v-if="filleds[questionIndex].soal.tipe_soal == 2">
                                    <ckeditor v-model="filleds[questionIndex].esay" :config="editorConfig"
                                    @input="onInput"  type="inline">
                                    </ckeditor>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="button-wrapper">
                        <b-button variant="info" class="sebelum" size="md"
                        @click="prev()" v-if="questionIndex != 0" :disabled="isLoadinger || !listening">
                            <span class="cil-chevron-left"></span>
                            Sebelumnya
                        </b-button>
                        <button id="soal-ragu" class="btn btn-warning ml-auto" :disabled="isLoadinger">
                            <b-form-checkbox size="lg" :value="1" v-model="filleds[questionIndex].ragu_ragu" :disabled="isLoadinger" @change="sendRagu()">Ragu ragu</b-form-checkbox>
                        </button>

                        <b-button variant="info" class="sesudah" size="md" :disabled="isLoadinger || !listening" @click="next()" v-if="questionIndex+1 != filleds.length">
                            Selanjutnya <span class="cil-chevron-right"></span>
                        </b-button>

                        <b-button variant="success" class="sesudah" size="md" @click="$bvModal.show('modal-selesai')" v-if="questionIndex+1 == filleds.length && checkRagu() == false" :disabled="isLoadinger">
                            SELESAI <i class="cil-check"></i>
                        </b-button>
                        <b-button variant="danger" class="sesudah" size="md" :disabled="isLoadinger" v-b-modal.modal-1 v-if="questionIndex+1 == filleds.length && checkRagu() == true">
                            SELESAI <i class="cil-check"></i>
                        </b-button>
                    </div>
                </div>
            </div>
        </div>
        <b-modal id="modal-selesai" centered class="shadow" @hide="isKonfirm = false">
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
                        'isi' : (fiel.jawab != 0 || fiel.esay != '' || fiel.jawab_complex.length != 0),
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
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import { mapActions, mapState, mapGetters, mapMutations} from 'vuex'
import AudioPlayer from '../../components/siswa/AudioPlayer.vue'
import _ from 'lodash'
import { successToas, errorToas} from '../../entities/notif'
import RenderString from '../../components/siswa/RenderString'

export default {
    name: 'KerjakannUjian',
    components: {
        AudioPlayer,
        Loading
    },
    data() {
        return {
            questionIndex: '',
            selected: '',
            selected_complex: [],
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
            editorConfig: {
                allowedContent: true,
                toolbarGroups : [
                    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                    { name: 'styles', groups: [ 'styles' ] },
                ]
            }
        }
    },
    filters: {
        charIndex(i) {
            return String.fromCharCode(97 + i)
        }
    },
    computed: {
        ...mapGetters(['isLoading', 'isLoadinger']),
        ...mapState('siswa_jadwal', {
            jadwal: state => state.banksoalAktif,
        }),
        ...mapState('siswa_user', {
            peserta: state => state.pesertaDetail
        }),
        ...mapState('siswa_ujian', {
            jawabanPeserta: state => state.jawabanPeserta,
            filleds: state => state.filledUjian.data,
            detail: state => state.filledUjian.detail
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
        ...mapActions('siswa_ujian',['takeFilled','submitJawaban','submitJawabanEssy', 'selesaiUjianPeserta', 'updateRaguJawaban']),
        async filledAllSoal() {
            try {
                await this.takeFilled()
            } catch (error) {
                this.$bvToast.toast(error.message, errorToas())
            }
        },
        selectOption(index) {
            const fill = this.filleds[this.questionIndex]

            this.submitJawaban({
                jawaban_id : this.filleds[this.questionIndex].id,
                jawab : this.filleds[this.questionIndex].soal.jawabans[index].id,
                index : this.questionIndex
            })
            .catch((error) => {
                this.$bvToast.toast(error.message, errorToas())
                this.$bvToast.toast('Terjadi kesalahan, cek koneksi internet', errorToas())
            })
        },
        inputJawabEssy(val) {
            const fill = this.filleds[this.questionIndex]
            if (this.filleds[this.questionIndex].soal.tipe_soal == 2) {
                this.submitJawabanEssy({
                    jawaban_id : this.filleds[this.questionIndex].id,
                    index : this.questionIndex,
                    essy: fill.esay
                })
                .catch((error) => {
                    this.$bvToast.toast(error.message, errorToas())
                })
            } else if (this.filleds[this.questionIndex].soal.tipe_soal == 6) {
               this.submitJawabanEssy({
                    jawaban_id : this.filleds[this.questionIndex].id,
                    index : this.questionIndex,
                    isian: fill.esay
                })
                .catch((error) => {
                    this.$bvToast.toast(error.message, errorToas())
                })
            }
        },
        onInput: _.debounce(function (value) {
          this.inputJawabEssy(value)
        }, 500),
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
        async selesai() {
            try {
                await this.selesaiUjianPeserta()

                this.$router.push({ name: 'ujian.selesai' })
                clearInterval(this.interval);
            } catch (error) {
                this.$bvToast.toast(error.message, errorToas())
            }
        },
        raguRagu(val) {
            if(val === '') {
                return
            }
            this.updateRaguJawaban({
                ragu_ragu: val,
                index: this.questionIndex,
                jawaban_id : this.filleds[this.questionIndex].id
            })
            .catch((error) => {
                this.$bvToast.toast(error.message, errorToas())
            })
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
        playDirection() {
            this.listening = false
            this.direction.play()
            this.direction.onended = () => {
                this.hasdirec.push(this.filleds[this.questionIndex].soal.id)
                this.listening = true
            }
            this.$bvModal.hide('modal-direction')
        },
        sendRagu() {
            const fill = this.filleds[this.questionIndex]
            let ragu = fill.ragu_ragu == false || fill.ragu_ragu == '0' ? 1 : 0;

            this.updateRaguJawaban({
                ragu_ragu: ragu,
                jawaban_id : this.filleds[this.questionIndex].id,
                index : this.questionIndex
            })
            .catch((error) => {
                this.$bvToast.toast(error.message, errorToas())
                this.$bvToast.toast('Terjadi kesalahan, cek koneksi internet', errorToas())
            })
        },
        changeCheckbox(e, val) {
            console.log(e.target.checked)
            if (e.target.checked === false) {
                let index = this.filleds[this.questionIndex].jawab_complex.indexOf(parseInt(e.target.value))
                console.log(index)
                if (index !== -1) {
                    this.filleds[this.questionIndex].jawab_complex.splice(index, 1)
                }
            } else {
                this.filleds[this.questionIndex].jawab_complex.push(parseInt(e.target.value))
            }
            this.submitJawaban({
                jawaban_id : this.filleds[this.questionIndex].id,
                jawab_complex : this.filleds[this.questionIndex].jawab_complex,
                index : this.questionIndex
            })
            .catch((error) => {
                this.$bvToast.toast(error.message, errorToas())
                this.$bvToast.toast('Terjadi kesalahan, cek koneksi internet', errorToas())
            })
        },
        changeToZoomer(string) {
            var elem = document.createElement("div");
            elem.innerHTML = string;

            var images = elem.getElementsByTagName("img");

            for(var i=0; i < images.length; i++){
                string = string.replace(/<img.*?>/, `<img role="button" src="${images[i].src}" @click="showImage('${images[i].src}')" />`)
            }
            return string
        }
    },
    async created() {
        try {
            await this.filledAllSoal()
            this.start()
        } catch (error) {
            this.$bvToast.toast(error.message, errorToas())
        }
    },
    watch: {
        questionIndex() {
            this.selected = this.filleds[this.questionIndex].jawab
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
        async jadwal(val) {
            if(typeof this.jadwal.jadwal != 'undefined') {
                await this.filledAllSoal()
                this.start()
            }
        },
        // ragu(val) {
        //     if (val == false) {
        //         const set = 0
        //         this.raguRagu(set)
        //     } else {
        //         this.raguRagu(val)
        //     }
        // },
        direction(val) {
            if(val != '') {
                if(this.hasdirec.includes(this.filleds[this.questionIndex].soal.id)) {
                    return
                }
                this.$bvModal.show('modal-direction')
            }
        },
    }
}
</script>
<style >
	div[contenteditable] {
    outline:1px solid #d8dbe0
}
.checkbox {
     display: grid;
     grid-template-columns: min-content auto;
     grid-gap: 0.5em;
     font-size: 2rem;
     color: var(--color);
}
 .checkbox--disabled {
     color: var(--disabled);
}
 .checkbox__control {
     display: inline-grid;
     width: 1em;
     height: 1em;
     border-radius: 0.25em;
     border: 0.1em solid currentColor;
}
 .checkbox__control svg {
     transition: transform 0.1s ease-in 25ms;
     transform: scale(0);
     transform-origin: bottom left;
}
 .checkbox__input {
     display: grid;
     grid-template-areas: "checkbox";
}
 .checkbox__input > * {
     grid-area: checkbox;
}
 .checkbox__input input {
     opacity: 0;
     width: 1em;
     height: 1em;
}
 .checkbox__input input:focus + .checkbox__control {
     box-shadow: 0 0 0 0.05em #fff, 0 0 0.15em 0.1em currentColor;
}
 .checkbox__input input:checked + .checkbox__control svg {
     transform: scale(1);
}
 .checkbox__input input:disabled + .checkbox__control {
     color: var(--disabled);
}


</style>
