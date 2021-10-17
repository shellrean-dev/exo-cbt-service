<template>
  <div class="container md:mx-auto flex flex-col justify-center space-y-4 lg:flex-row lg:space-y-0 lg:space-x-4 -mt-12 sm:-mt-24">
    <div class="w-full lg:py-4 lg:px-4 mb-20">
      <div class="bg-white border-gray-300 shadow sm:shadow-2xl rounded-t-2xl rounded-b-2xl"  v-if="filleds && typeof filleds[questionIndex] != 'undefined'">
        <div class="pt-4 pb-2 pr-2 flex justify-between border-b border-gray-300 mb-2 items-center" v-show="!focus">
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
				    <button class="py-1 px-1 rounded-md bg-gray-100 text-gray-600 border border-gray-300 hover:shadow-lg"
            @click="modalQuestion = true"
            v-show="!focus"
            :disabled="!listening">
					    <app-line-icon></app-line-icon>
				    </button>
				    <button class="py-1 px-1 rounded-md bg-gray-100 text-gray-600 border border-gray-300 hover:shadow-lg"
            @click="focus = !focus"
            >
              <expand-line-icon v-show="!focus"></expand-line-icon>
              <minimize-line-icon v-show="focus"></minimize-line-icon>
				    </button>
			    </div>
		    </div>
		    <div class="py-8 px-2 sm:px-8"
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
          <div class="my-2"
          v-if="[1,2,3].includes(parseInt(filleds[questionIndex].soal.layout))"
          >
            <RenderString
            :string="changeToZoomer(filleds[questionIndex].soal.pertanyaan)" />
          </div>
          <div class="my-2"
          v-if="[4].includes(parseInt(filleds[questionIndex].soal.layout))"
          >
            <div class="flex space-x-4">
              <div class="flex-1">
                <RenderString
                :string="changeToZoomer(filleds[questionIndex].soal.pertanyaan)" />
              </div>
              <div class="w-96">
                <div class="flex flex-col space-y-3 mt-5">
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
                    <textarea
                    class="border w-full h-24 border-gray-300 rounded-md text-gray-700 py-1 px-4"
                    v-if="filleds[questionIndex].soal.tipe_soal == 2"
                    v-model="filleds[questionIndex].esay"
                    @input="onInput"
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
			    <div class="flex flex-col space-y-3 mt-5"
          v-if="filleds[questionIndex].soal.layout == 1"
          >
            <template
            v-if="[5].includes(filleds[questionIndex].soal.tipe_soal)"
            >
              <div class="flex"
                   v-for="(jawab, index) in filleds[questionIndex].soal.jawabans"
                   :key="'jawaban_menjodohkan_index_'+index"
              >
                <div class="py-4 px-4 rounded-xl border-2 flex-1 cursor-pointer hover:shadow-lg"
                     :class="{
                        'border-green-400' : menjodohkan.left == index,
                        'border-gray-200' : menjodohkan.left != index,
                        'border-green-500 shadow-xl' : menjodohkan.matchIndex == index
                     }"
                v-html="jawab.a.text"
                     v-on:click="menjodohkanLeftClick(index)"
                >
                </div>
                <div class="flex items-center text-gray-400">
                  <div class="flex items-center"
                       v-if="menjodohkan.matchIndex == index">
                    <div class="h-4 w-2 bg-green-500 rounded-r-2xl"></div>
                    <div class="h-1 w-10 bg-green-500">
                    </div>
                    <div class="h-4 w-2 bg-green-500 rounded-l-2xl"></div>
                  </div>
                  <div class="flex items-center"
                       v-else>
                    <div class="h-4 w-2 bg-gray-400 rounded-r-2xl"></div>
                    <div class="h-1 w-10 bg-gray-400">
                    </div>
                    <div class="h-4 w-2 bg-gray-400 rounded-l-2xl"></div>
                  </div>
                </div>
                <div class="py-4 px-4 rounded-xl border-2 flex-1 cursor-pointer hover:shadow-lg"
                     :class="{
                        'border-green-400' : menjodohkan.right == index,
                        'border-gray-200' : menjodohkan.right != index,
                        'border-green-500 shadow-xl' : menjodohkan.matchIndex == index
                     }"
                v-html="jawab.b.text"
                     v-on:click="menjodohkanRightClick(index)"
                >
                </div>
              </div>
            </template>
            <template
            v-if="[1,3,4].includes(filleds[questionIndex].soal.tipe_soal)"
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
            </template>
            <div
            v-if="[2,6].includes(filleds[questionIndex].soal.tipe_soal)"
            >
              <input type="text"
              class="border border-gray-300 rounded-md text-gray-700 py-1 px-4"
              v-if="filleds[questionIndex].soal.tipe_soal == 6"
              v-model="filleds[questionIndex].esay"
              @keyup="onInput"/>
              <textarea
              class="border w-full h-24 border-gray-300 rounded-md text-gray-700 py-1 px-4"
              v-if="filleds[questionIndex].soal.tipe_soal == 2"
              v-model="filleds[questionIndex].esay"
              @input="onInput"
              ></textarea>
            </div>
            <template
              v-if="[7].includes(filleds[questionIndex].soal.tipe_soal)">
              <div class="flex space-x-1"
                   v-for="(jawab,index) in filleds[questionIndex].soal.jawabans"
                   :key="'optional_mengurutkan_index'+index">
                <div class="py-4 px-4 rounded-xl border-2 flex-1 cursor-pointer hover:shadow-lg"
                     :class="mengurutkan == index ? 'border-green-400' : 'border-gray-200'"
                     v-html="jawab.text_jawaban"
                     v-on:click="mengurutkanClick(index)">
                </div>
              </div>
            </template>
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
              <textarea
              class="border w-full h-24 border-gray-300 rounded-md text-gray-700 py-1 px-4"
              v-if="filleds[questionIndex].soal.tipe_soal == 2"
              v-model="filleds[questionIndex].esay"
              @input="onInput"
              ></textarea>
            </div>
          </div>
		      <div
          v-if="filleds[questionIndex].soal.layout == 3"
          >
            <table class="border-collapse border border-gray-400  w-full">
              <tr v-if="[8].includes(parseInt(filleds[questionIndex].soal.tipe_soal))">
                <th>Pernyataan</th>
                <th>Benar</th>
                <th>Salah</th>
              </tr>
              <tr v-if="[9].includes(parseInt(filleds[questionIndex].soal.tipe_soal))">
                <th>Pernyataan</th>
                <th>Setuju</th>
                <th>Tidak</th>
                <th>Argument mu</th>
              </tr>
              <tr
              v-for="(jawab,index) in filleds[questionIndex].soal.jawabans"
              :key="index"
              >
                <td class="border border-gray-400" width="50px"
                v-if="[1,3].includes(parseInt(filleds[questionIndex].soal.tipe_soal))"
                >
                  <div class="flex items-center m-2">
                    <input :id="'radio1'+index"
                    v-model="selected" type="radio" name="jwb"
                    :value="jawab.id" class="hidden"
                    :disabled="isLoadinger || isLoading"
                    @change="selectOption(index)"/>
                    <label :for="'radio1'+index" class="flex items-center cursor-pointer text-xl">
                      <span class="w-6 h-6 text-sm inline-block mr-2 rounded-full border border-gray-400 flex-no-shrink flex items-center justify-center uppercase">{{ charIndex(index) }}</span>
                    </label>
                  </div>
                </td>
                <td class="border border-gray-400" width="50px"
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
                </td>
                <td class="border border-gray-400 pl-2"
                v-html="jawab.text_jawaban"
                ></td>
                <template
                  v-if="[8].includes(parseInt(filleds[questionIndex].soal.tipe_soal))">
                  <td class="border border-gray-400" width="80px">
                    <div class="flex items-center m-2">
                      <input :id="'radio1'+index"
                             :name="'radio1'+index"
                             :checked="filleds[questionIndex].benar_salah[jawab.id] == 1"
                             type="radio"
                             value="1" class="hidden"
                             :disabled="isLoadinger || isLoading"
                             @change="changeRadioBenarSalah($event, jawab.id)"/>
                      <label :for="'radio1'+index" class="flex items-center cursor-pointer text-xl">
                        <span class="w-6 h-6 text-sm inline-block mr-2 rounded-full border border-gray-400 flex-no-shrink flex items-center justify-center uppercase"></span>
                      </label>
                    </div>
                  </td>
                  <td class="border border-gray-400" width="80px">
                    <div class="flex items-center m-2">
                      <input :id="'radio2'+index"
                             :name="'radio2'+index"
                             :checked="filleds[questionIndex].benar_salah[jawab.id] == 0"
                             type="radio"
                             value="0" class="hidden"
                             :disabled="isLoadinger || isLoading"
                             @change="changeRadioBenarSalah($event, jawab.id)"/>
                      <label :for="'radio2'+index" class="flex items-center cursor-pointer text-xl">
                        <span class="w-6 h-6 text-sm inline-block mr-2 rounded-full border border-gray-400 flex-no-shrink flex items-center justify-center uppercase"></span>
                      </label>
                    </div>
                  </td>
                </template>
                <template
                  v-if="[9].includes(parseInt(filleds[questionIndex].soal.tipe_soal))">
                  <td class="border border-gray-400" width="80px">
                    <div class="flex items-center m-2">
                      <input :id="'radio1'+index"
                             :name="'radio1'+index"
                             :checked="filleds[questionIndex].setuju_tidak[jawab.id]['val'] == 1"
                             type="radio"
                             value="1" class="hidden"
                             :disabled="isLoadinger || isLoading"
                             @change="changeRadioSetujuTidak($event, jawab.id)"/>
                      <label :for="'radio1'+index" class="flex items-center cursor-pointer text-xl">
                        <span class="w-6 h-6 text-sm inline-block mr-2 rounded-full border border-gray-400 flex-no-shrink flex items-center justify-center uppercase"></span>
                      </label>
                    </div>
                  </td>
                  <td class="border border-gray-400" width="80px">
                    <div class="flex items-center m-2">
                      <input :id="'radio2'+index"
                             :name="'radio2'+index"
                             :checked="filleds[questionIndex].setuju_tidak[jawab.id]['val'] == 0"
                             type="radio"
                             value="0" class="hidden"
                             :disabled="isLoadinger || isLoading"
                             @change="changeRadioSetujuTidak($event, jawab.id)"/>
                      <label :for="'radio2'+index" class="flex items-center cursor-pointer text-xl">
                        <span class="w-6 h-6 text-sm inline-block mr-2 rounded-full border border-gray-400 flex-no-shrink flex items-center justify-center uppercase"></span>
                      </label>
                    </div>
                  </td>
                  <td class="border border-gray-400" width="250px">
                    <textarea
                      class="border w-full h-24 border-gray-300 rounded-md text-gray-700 py-1 px-4"
                      v-model="filleds[questionIndex].setuju_tidak[jawab.id]['argument']"
                      @input="onInputSetujuTidak"
                    ></textarea>
                  </td>
                </template>
              </tr>
              <tr
              v-if="[2,6].includes(filleds[questionIndex].soal.tipe_soal)"
              >
                <td>
                  <input type="text"
                  class="border border-gray-300 rounded-md text-gray-700 py-1 px-4"
                  v-if="filleds[questionIndex].soal.tipe_soal == 6"
                  v-model="filleds[questionIndex].esay"
                  @keyup="onInput"/>
                  <textarea
                  class="border w-full h-24 border-gray-300 rounded-md text-gray-700 py-1 px-4"
                  v-if="filleds[questionIndex].soal.tipe_soal == 2"
                  v-model="filleds[questionIndex].esay"
                  @input="onInput"
                  ></textarea>
                </td>
              </tr>
            </table>
          </div>
				</div>
		    <div class="py-4 px-2 sm:px-4 flex justify-between border-t border-gray-300 items-center"  v-show="!focus">
			    <button class="py-1 px-3 border-2 rounded-md hover:shadow-lg sm:flex sm:items-center sm:space-x-2"
          :class="isLoadinger ? 'bg-red-200 text-white border-red-200' : 'bg-red-400 text-white border-red-400'"
          :disabled="isLoadinger || !listening"
          v-if="questionIndex != 0"
          @click="prev()"
          >
            <prev-line-icon></prev-line-icon> <span class="hidden sm:block">Sebelumnya</span>
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
			    <button class="py-1 px-3 border-2 rounded-md hover:shadow-lg sm:flex sm:items-center sm:space-x-2"
          :class="isLoadinger ? 'bg-blue-200 text-white border-blue-200' : 'bg-blue-400 text-white border-blue-400'"
          :disabled="isLoadinger || !listening"
          v-if="questionIndex+1 != filleds.length"
          @click="next()"
          >
				    <span class="hidden sm:block">Selanjutnya</span> <next-line-icon></next-line-icon>
			    </button>
          <button class="py-1 px-3 border-2 rounded-md hover:shadow-lg sm:flex sm:items-center sm:space-x-2"
          :class="isLoadinger ? 'bg-green-200 text-white border-green-200' : 'bg-green-400 text-white border-green-400'"
          :disabled="isLoadinger || !listening"
          v-if="questionIndex+1 == filleds.length && checkRagu() == false"
          @click="modalConfirm = true"
          >
				    <span class="hidden sm:block">Selanjutnya</span> <next-line-icon></next-line-icon>
			    </button>
          <button class="py-1 px-3 border-2 rounded-md hover:shadow-lg sm:flex sm:items-center sm:space-x-2"
          v-if="questionIndex+1 == filleds.length && checkRagu() == true && checkIsian() == false"
          :class="isLoadinger ? 'bg-red-200 text-white border-red-200' : 'bg-red-400 text-white border-red-400'"
          :disabled="isLoadinger || !listening"
          @click="doubtExistAlert"
          >
				    <span class="hidden sm:block">Selanjutnya</span> <next-line-icon></next-line-icon>
			    </button>
          <button class="py-1 px-3 border-2 rounded-md hover:shadow-lg sm:flex sm:items-center sm:space-x-2"
                  v-if="questionIndex+1 == filleds.length && checkRagu() == false && checkIsian() == true"
                  :class="isLoadinger ? 'bg-red-200 text-white border-red-200' : 'bg-red-400 text-white border-red-400'"
                  :disabled="isLoadinger || !listening"
                  @click="kosongExistAlert"
          >
            <span class="hidden sm:block">Selanjutnya</span> <next-line-icon></next-line-icon>
          </button>
		    </div>
      </div>
    </div>
    <modal-confirm v-if="modalConfirm" @close="modalConfirm = false" @finish="selesai"></modal-confirm>
    <modal-question v-if="modalQuestion" @close="modalQuestion = false" @toland="toLand"></modal-question>
    <modal-direction v-if="modalDirection" @close="playDirection" @mute="modalDirection = false"></modal-direction>
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
    onInputSetujuTidak: _.debounce(function (value) {
      this.sendAnswerSetujuTidak(value)
    }, 300),
    doubtExistAlert() {
      this.$swal('Hei..', 'Jawabanmu masih ada ' + this.hei.ragu +' yang ragu-ragu, kamu bisa cek pada nomor yang berwarna kuning.','warning')
    },
    kosongExistAlert() {
      this.$swal('Hei..', 'Jawabanmu masih ada ' + this.hei.kosong +' yang belum diisi, kamu bisa cek pada nomor yang berwarna abu.','warning')
    }
  },
  async created() {
    try {
      await this.filledAllSoal()
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
      clearInterval(this.$store.state.siswa_ujian.interval)
      if (typeof val != 'undefined') {
        this.time = val.sisa_waktu
        this.$store.state.siswa_ujian.interval = setInterval( () => {
          if (this.time > 0) {
            this.time--
          } else {
            this.selesai()
          }
        }, 1000 )
      }
    },
    async jadwal(val) {
      console.log('jadwal watcher was called')
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
