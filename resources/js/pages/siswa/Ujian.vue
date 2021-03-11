<template>
  <div class="container md:mx-auto flex flex-col justify-center space-y-4 lg:flex-row lg:space-y-0 lg:space-x-4 -mt-12 sm:-mt-24">
    <div class="w-full lg:w-3/4 lg:py-4 lg:px-4 mb-20">
      <div class="bg-white border-2 border-gray-300 shadow rounded-t-lg rounded-b-lg"  v-if="filleds && typeof filleds[questionIndex] != 'undefined'">
        <div class="pt-2 pb-2 pr-2 flex justify-between border-b border-gray-300 mb-2 items-center" v-show="!focus">
          <div class="flex items-center">
			      <p class="font-bold w-12 h-10 bg-gray-200 flex items-center pl-2 text-lg rounded-r-full text-gray-700 border-t border-r border-b border-gray-300">{{ questionIndex+1 }}</p>
            <p class="font-medium text-gray-700 px-2">{{ tipeSoalText() }}</p>
          </div>
          <div class="flex justify-end space-x-2 mb-2 items-center">
			      <div class="rounded-md text-white font-bold flex" v-html="prettyTime">
			      </div>
          </div>
        </div>
		    <div class="py-2 px-2 my-2 border-b border-gray-300 flex justify-between">
          <div class="flex flex-col space-y-1">
           <p class="text-xs text-gray-600">Ukuran soal</p>
            <input type="range" id="vol" name="vol" min="0" max="3" value="1" @change="onChangeRange">
          </div>
			    <div class="flex space-x-1">
				    <button @click="modalQuestion = true" class="py-1 px-1 rounded-md bg-gray-100 text-gray-600 border border-gray-300 hover:shadow-lg" v-show="!focus">
					    <app-line-icon></app-line-icon>
				    </button>
				    <button @click="focus = !focus" class="py-1 px-1 rounded-md bg-gray-100 text-gray-600 border border-gray-300 hover:shadow-lg">
              <expand-line-icon v-show="!focus"></expand-line-icon>
              <minimize-line-icon v-show="focus"></minimize-line-icon>
				    </button>
			    </div>
		    </div>
		    <div class="py-2 px-2"
        :class="textSize"
        v-if="typeof filleds[questionIndex] != 'undefined'">
          <div class="my-2"
          v-if="audio != ''"
          >
            <audio-player
            v-if="listening"
            :file="'/storage/audio/'+audio"
            ></audio-player>
          </div>
          <div class="my-2">
            <RenderString
            :string="changeToZoomer(filleds[questionIndex].soal.pertanyaan)" />
          </div>
			    <div class="flex flex-col space-y-3 mt-5"
          v-if="filleds[questionIndex].soal.layout == 1"
          >
				    <div class="flex space-x-1"
            v-for="(jawab,index) in filleds[questionIndex].soal.jawabans"
            :key="index">
					    <div
              v-if="[1,3].includes(parseInt(filleds[questionIndex].soal.tipe_soal))">
					      <div class="flex items-center mr-4 mb-4">
						      <input :id="'radio1'+index"
                  v-model="selected" type="radio" name="jwb"
                  :value="jawab.id" class="hidden"
                  :disabled="isLoadinger || isLoading"
                  @change="selectOption(index)"/>
						      <label :for="'radio1'+index" class="flex items-center cursor-pointer text-xl">
						        <span class="w-6 h-6 text-sm inline-block mr-2 rounded-full border border-gray-400 flex-no-shrink flex items-center justify-center uppercase">{{ charIndex(index) }}</span>
						      </label>
					      </div>
					    </div>
              <div
              v-if="4 == parseInt(filleds[questionIndex].soal.tipe_soal)"
              >
						    <div class="bg-white border-2 rounded border-gray-400 w-6 h-6 flex flex-shrink-0 justify-center items-center mr-2 focus-within:border-blue-500">
				          <input
                  :checked="filleds[questionIndex].jawab_complex.includes(jawab.id)"
                  :value="jawab.id"
                  :disabled="isLoadinger || isLoading"
                  @change="changeCheckbox($event, index)"
                  type="checkbox" class="opacity-0 absolute">
				          <svg class="fill-current hidden w-4 h-4 text-green-500 pointer-events-none" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
			          </div>
              </div>
					    <div v-html="jawab.text_jawaban"></div>
            </div>
            <div
            v-if="[2,6].includes(filleds[questionIndex].soal.tipe_soal)"
            >
              <input type="text"
              class="border border-gray-300 rounded-md text-gray-700 py-1 px-4"
              v-if="filleds[questionIndex].soal.tipe_soal == 6"
              v-model="filleds[questionIndex].esay"
              @keyup="onInput"/>
              <ckeditor
              class="border border-gray-300"
              v-model="filleds[questionIndex].esay"
              :config="editorConfig"
              @input="onInput"
              v-if="filleds[questionIndex].soal.tipe_soal == 2"
              type="inline">
              </ckeditor>
            </div>
				  </div>
          <div class="grid grid-rows-none sm:grid-rows-3 sm:grid-flow-col gap-4 mt-4"
          v-if="filleds[questionIndex].soal.layout == 2"
          >
            <div
            v-for="(jawab,index) in filleds[questionIndex].soal.jawabans"
            :key="index"
            >
              <div class="flex space-x-1">
                <div
                v-if="[1,3].includes(parseInt(filleds[questionIndex].soal.tipe_soal))"
                >
                  <div class="flex items-center mr-4 mb-4">
                    <input :id="'radio1'+index"
                    v-model="selected" type="radio" name="jwb"
                    :value="jawab.id" class="hidden"
                    :disabled="isLoadinger || isLoading"
                    @change="selectOption(index)"/>
                    <label :for="'radio1'+index" class="flex items-center cursor-pointer text-xl">
                      <span class="w-6 h-6 text-sm inline-block mr-2 rounded-full border border-gray-400 flex-no-shrink flex items-center justify-center uppercase">{{ charIndex(index) }}</span>
                    </label>
                  </div>
                </div>
                <div
                v-if="4 == parseInt(filleds[questionIndex].soal.tipe_soal)"
                >
                  <div class="bg-white border-2 rounded border-gray-400 w-6 h-6 flex flex-shrink-0 justify-center items-center mr-2 focus-within:border-blue-500">
                    <input
                    :checked="filleds[questionIndex].jawab_complex.includes(jawab.id)"
                    :value="jawab.id"
                    :disabled="isLoadinger || isLoading"
                    @change="changeCheckbox($event, index)"
                    type="checkbox" class="opacity-0 absolute">
                    <svg class="fill-current hidden w-4 h-4 text-green-500 pointer-events-none" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
                  </div>
                </div>
                <div v-html="jawab.text_jawaban"></div>
              </div>
            </div>
            <div
            v-if="[2,6].includes(filleds[questionIndex].soal.tipe_soal)"
            >
              <input type="text"
              class="border border-gray-300 rounded-md text-gray-700 py-1 px-4"
              v-if="filleds[questionIndex].soal.tipe_soal == 6"
              v-model="filleds[questionIndex].esay"
              @keyup="onInput"/>
              <ckeditor
              class="border border-gray-300"
              v-model="filleds[questionIndex].esay"
              :config="editorConfig"
              @input="onInput"
              v-if="filleds[questionIndex].soal.tipe_soal == 2"
              type="inline">
              </ckeditor>
            </div>
          </div>
				</div>
		    <div class="py-2 px-2 flex justify-between border-t border-gray-300 items-center"  v-show="!focus">
			    <button class="py-1 px-3 border-2 rounded-md hover:shadow-lg"
          :class="isLoadinger ? 'bg-blue-200 text-white border-blue-200' : 'bg-blue-400 text-white border-blue-400'"
          :disabled="isLoadinger || !listening"
          v-if="questionIndex != 0"
          @click="prev()"
          >
				    <prev-line-icon></prev-line-icon>
			    </button>
			    <div>
						<label class="flex justify-start items-start">
			        <div class="bg-white border-2 rounded border-gray-400 w-6 h-6 flex flex-shrink-0 justify-center items-center mr-2 focus-within:border-blue-500">
				        <input type="checkbox" class="opacity-0 absolute"
                :value="questionIndex"
                :checked="filleds[questionIndex].ragu_ragu == '1'"
                :disabled="isLoadinger"
                @change="sendRagu" >
				        <svg class="fill-current hidden w-4 h-4 text-green-500 pointer-events-none" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
			        </div>
			        <div class="select-none">ragu-ragu</div>
			      </label>
			    </div>
			    <button class="py-1 px-3 border-2 rounded-md hover:shadow-lg"
          :class="isLoadinger ? 'bg-blue-200 text-white border-blue-200' : 'bg-blue-400 text-white border-blue-400'"
          :disabled="isLoadinger || !listening"
          v-if="questionIndex+1 != filleds.length"
          @click="next()"
          >
				    <next-line-icon></next-line-icon>
			    </button>
          <button class="py-1 px-3 border-2 rounded-md hover:shadow-lg"
          :class="isLoadinger ? 'bg-green-200 text-white border-green-200' : 'bg-green-400 text-white border-green-400'"
          :disabled="isLoadinger || !listening"
          v-if="questionIndex+1 == filleds.length && checkRagu() == false"
          @click="modalConfirm = true"
          >
				    <next-line-icon></next-line-icon>
			    </button>
          <button class="py-1 px-3 border-2 rounded-md hover:shadow-lg"
          v-if="questionIndex+1 == filleds.length && checkRagu() == true"
          :class="isLoadinger ? 'bg-red-200 text-white border-red-200' : 'bg-red-400 text-white border-red-400'"
          :disabled="isLoadinger || !listening"
          @click="doubtExistAlert"
          >
				    <next-line-icon></next-line-icon>
			    </button>
		    </div>
      </div>
    </div>
    <modal-confirm v-if="modalConfirm" @close="modalConfirm = false" @finish="selesai"></modal-confirm>
    <modal-question v-if="modalQuestion" @close="modalQuestion = false" @toland="toLand"></modal-question>
    <modal-direction v-if="modalDirection" @close="modalDirection = false"></modal-direction>
  </div>
</template>
<script>
import {
  vuex_state,
  vuex_methods,
  vue_data,
  vue_computed,
  vue_methods
} from '../../entities/ujian'
import NextLineIcon from '../../components/NextLineIcon'
import PrevLineIcon from '../../components/PrevLineIcon'
import AppLineIcon from '../../components/AppLineIcon'
import ModalConfirm from '../../components/ModalConfirm'
import ModalQuestion from '../../components/ModalQuestion'
import ModalDirection from '../../components/ModalDirection'
import ExpandLineIcon from '../../components/ExpandLineIcon'
import MinimizeLineIcon from '../../components/MinimizeLineIcon'
import RenderString from '../../components/siswa/RenderString'
import AudioPlayer from '../../components/siswa/AudioPlayer.vue'
import 'vue-loading-overlay/dist/vue-loading.css'
import _ from 'lodash'

export default {
  components: {
    NextLineIcon,
    PrevLineIcon,
    AppLineIcon,
    ExpandLineIcon,
    MinimizeLineIcon,
    AudioPlayer,
    ModalConfirm,
    ModalQuestion,
    ModalDirection
  },
  data() {
    return vue_data
  },
  computed: {
    ...vuex_state,
    ...vue_computed
  },
  methods: {
    ...vuex_methods,
    ...vue_methods,
    onInput: _.debounce(function (value) {
      this.inputJawabEssy(value)
    }, 300),
    doubtExistAlert() {
      this.$swal('Hei..', 'Jawabanmu masih ada yang ragu-ragu','error')
    },
    finishExamAlert() {

    }
  },
  async created() {
    try {
      await this.filledAllSoal()
      this.start()
    } catch (error) {
      this.showError(error)
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
    direction(val) {
      if(val != '') {
        if(this.hasdirec.includes(this.filleds[this.questionIndex].soal.id)) {
          return
        }
        this.modalDirection = true
      }
    }
  }
}
</script>
