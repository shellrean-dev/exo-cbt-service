import { mapActions, mapState, mapGetters } from 'vuex'
import { showSweetError } from './alert'

/**
 * vuex states
 * @type {Object}
 */
const vuex_state = {
    ...mapGetters([
        'isLoading',
        'isLoadinger'
    ]),
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
    })
}

/**
 * vuex methods
 * @type {Object}
 */
const vuex_methods = {
    ...mapActions('siswa_ujian',[
        'takeFilled',
        'submitJawaban',
        'submitJawabanEssy',
        'selesaiUjianPeserta',
        'updateRaguJawaban',
        'submitJawabanMenjodohkan',
        'submitJawabanMengurutkan'
    ]),
}

/**
 * vue data
 * @type {Object}
 */
const vue_data = {
    modalConfirm: false,
    modalQuestion: false,
    modalDirection: false,
    questionIndex: '',
    selected: '',
    selected_complex: [],
    selected_benar_salah: {
        0: 0,
        1: 1
    },
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
    },
    focus: false,
    textSize: 'text-base',
    menjodohkan: {
        left: null,
        right: null,
        matchIndex: 0
    },
    showOptionMenjodohkan: true,
    mengurutkan: null
}

/**
 * vue computed
 * @type {Object}
 */
const vue_computed = {
    prettyTime
}

/**
 * vue methods
 * @type {Object}
 */
const vue_methods = {
    charIndex,
    filledAllSoal,
    selectOption,
    inputJawabEssy,
    prev,
    next,
    toggle,
    toLand,
    start,
    selesai,
    raguRagu,
    checkRagu,
    playDirection,
    sendRagu,
    changeCheckbox,
    changeToZoomer,
    tipeSoalText,
    showError,
    onChangeRange,
    changeRadioBenarSalah,
    mengurutkanClick,
    menjodohkanLeftClick,
    menjodohkanRightClick
}

/**
 * Let's play the game
 *
 */
export {
    vuex_state,
    vuex_methods,
    vue_data,
    vue_computed,
    vue_methods,
}

/**
 * get character from index
 * @type vue method
 */
function charIndex(i) {
    return String.fromCharCode(97 + i)
}

/**
 * create pretty time
 * @type vue computed
 */
function prettyTime () {
    let sec_num = parseInt(this.time, 10)
    let hours   = Math.floor(sec_num / 3600)
    let minutes = Math.floor((sec_num - (hours * 3600)) / 60)
    let seconds = sec_num - (hours * 3600) - (minutes * 60)
    if (hours < 10) {hours = '0'+hours}
    if (minutes < 10) {minutes = '0'+minutes}
    if (seconds < 10) {seconds = '0'+seconds}
    return `<span class="rounded-l-md py-1 pl-2 pr-1 bg-blue-400">${hours}</span>
            <span class="py-1 px-1 bg-blue-100 text-blue-600">${minutes}</span>
            <span class="py-1 pl-1 pr-2 bg-blue-100 rounded-r-md text-blue-600">${seconds}</span>`
}

/**
 * Show Err
 * @type vue method
 */
function showError(err) {
    showSweetError(this, err)
}

/**
 * When range is change
 * @type vue method
 */
function onChangeRange(e) {
    switch (parseInt(e.target.value)) {
        case 0:
            this.textSize = 'text-sm'
            break;
        case 1:
            this.textSize = 'text-base'
            break;
        case 2:
            this.textSize = 'text-lg'
            break;
        case 3:
            this.textSize = 'text-xl'
            break
        default:
            this.textSize = 'text-base'
    }
}

/**
 * get filled data
 * @type vue method
 */
async function filledAllSoal() {
    try {
        await this.takeFilled()
    } catch (error) {
        this.showError(error)
    }
}

/**
 * when use click option
 * @type vue method
 */
function selectOption(index) {
    const fill = this.filleds[this.questionIndex]

    this.submitJawaban({
        jawaban_id : this.filleds[this.questionIndex].id,
        jawab : this.filleds[this.questionIndex].soal.jawabans[index].id,
        index : this.questionIndex
    })
    .catch((error) => {
        this.showError(error)
    })
}

/**
 * when user input esay
 * @type vue method
 */
function inputJawabEssy(val) {
    const fill = this.filleds[this.questionIndex]
    if (this.filleds[this.questionIndex].soal.tipe_soal == 2) {
        this.submitJawabanEssy({
            jawaban_id : this.filleds[this.questionIndex].id,
            index : this.questionIndex,
            essy: fill.esay
        })
        .catch((error) => {
            this.showError(error)
        })
    } else if (this.filleds[this.questionIndex].soal.tipe_soal == 6) {
       this.submitJawabanEssy({
            jawaban_id : this.filleds[this.questionIndex].id,
            index : this.questionIndex,
            isian: fill.esay
        })
        .catch((error) => {
            this.showError(error)
        })
    }
}

/**
 * Do prev question
 * @type vu method
 */
function prev() {
    if (this.filleds.length > 0) this.questionIndex--
}

/**
 * Do next question
 * @type vue method
 */
function next() {
    if (this.questionIndex < this.filleds.length) this.questionIndex++
}

/**
 * Toggle sidebar
 * @type vue method
 */
function toggle() {
    this.sidebar = !this.sidebar;
}

/**
 * Go to question number
 * @type vue method
 */
function toLand(index) {
    this.questionIndex = index
}

/**
 * Start counting time
 * @type vue method
 */
function start () {
    this.timer = setInterval( () => {
        if (this.time > 0) {
             this.time--
        } else {

        }
    }, 1000 )
}

/**
 * Finishing the test
 * @type vue method
 */
async function  selesai() {
    try {
        await this.selesaiUjianPeserta()
        this.$store.state.siswa_ujian.filledUjian = []
        this.questionIndex = ''
        clearInterval(this.$store.state.siswa_ujian.interval)
        this.$router.push({ name: 'ujian.selesai' })
    } catch (error) {
        this.showError(error)
    }
}

/**
 * Set user doubt
 * @type vue method
 */
function raguRagu(val) {
    if(val === '') {
        return
    }
    this.updateRaguJawaban({
        ragu_ragu: val,
        index: this.questionIndex,
        jawaban_id : this.filleds[this.questionIndex].id
    })
    .catch((error) => {
        this.showError(error)
    })
}

/**
 * Check is doubt
 * @type vue method
 */
function checkRagu() {
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
}

/**
 * Play direction
 * @type vue method
 */
function playDirection() {
    this.listening = false
    this.direction.play()
    this.direction.onended = () => {
        this.hasdirec.push(this.filleds[this.questionIndex].soal.id)
        this.listening = true
    }
    this.modalDirection = false
}

/**
 * Send doublt answer
 * @type vue method
 */
function sendRagu(e) {
    let ragu = 0
    if (e.target.checked) {
       ragu = 1
    }

    this.updateRaguJawaban({
        ragu_ragu: ragu,
        jawaban_id : this.filleds[this.questionIndex].id,
        index : this.questionIndex
    })
    .then((res) => {
        this.$store.state.siswa_ujian.filledUjian.data[parseInt(e.target.value)].ragu_ragu = ragu
    })
    .catch((error) => {
        this.showError(error)
    })
}

/**
 * Change checkbox
 * @type vue method
 */
function changeCheckbox(e, val) {
    if (e.target.checked === false) {
        let index = this.filleds[this.questionIndex].jawab_complex.indexOf(parseInt(e.target.value))
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
        this.showError(error)
    })
}

/**
 * Change radio button benar salah
 * @param e
 * @param val
 */
function changeRadioBenarSalah(e, val) {
    this.filleds[this.questionIndex].benar_salah[val] = parseInt(e.target.value);
    this.submitJawaban({
        jawaban_id : this.filleds[this.questionIndex].id,
        benar_salah : this.filleds[this.questionIndex].benar_salah,
        index : this.questionIndex
    }).catch(error => this.showError(error))
}

/**
 * Change to zoomer data
 * @type vue method
 */
function changeToZoomer(string) {
    var elem = document.createElement("div");
    elem.innerHTML = string;

    var images = elem.getElementsByTagName("img");

    for(var i=0; i < images.length; i++){
        let str = images[i].outerHTML.replace(/\//g, "\\/");
        let reg = new RegExp(str.slice(0, -1)+'.*?>', 'g')
        string = string.replace(reg, `<img role="button" src="${images[i].src}" @click="showImage('${images[i].src}')" />`)
    }
    return string
}

/**
 * Get tipe soal text
 * @type vue method
 */
function tipeSoalText() {
    const type = [
        'Unknown',
        'Pilihan Ganda',
        'Uraian',
        'Listening',
        'Pilihan Ganda Kompleks',
        'Menjodohkan',
        'Isian Singkat',
        'Mengurutkan',
        'Benar/Salah',
        'Setuju/Tidak setuju'
    ]


    let idx = this.filleds[this.questionIndex]
    if (typeof idx != 'undefined') {
        idx = idx.soal.tipe_soal
        return type[idx]
    }
    return 'Unknown'
}

/**
 * Mengurutkan klik
 * @param index
 */
function mengurutkanClick(index) {
    if (this.mengurutkan == null) {
        this.mengurutkan = index;
        return;
    }

    if (this.mengurutkan == index) {
        this.mengurutkan = null;
        return;
    }
    let tmp = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans));
    tmp[this.mengurutkan] = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans[index]));
    tmp[index] = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans[this.mengurutkan]));

    this.filleds[this.questionIndex].soal.jawabans = tmp;
    this.mengurutkan = null;

    this.submitJawabanMengurutkan({
        jawaban_id : this.filleds[this.questionIndex].id,
        mengurutkan: tmp.map(item => item.id),
        index : this.questionIndex
    }).catch((error) => {
        this.showError(error)
    })
}

/**
 * Click left area menjodohkan
 * @param index
 */
function menjodohkanLeftClick(index) {
    if (this.menjodohkan.left == index) {
        this.menjodohkan.left = null;
        return;
    }
    this.menjodohkan.left = index
    if (this.menjodohkan.right == null) {
        return;
    }
    let tmp = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans));
    // tmp[this.menjodohkan.right].a = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans[index].a));
    // tmp[index].a = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans[this.menjodohkan.right].a));

    tmp[this.menjodohkan.right].b = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans[index].b));
    tmp[index].b = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans[this.menjodohkan.right].b));

    this.filleds[this.questionIndex].soal.jawabans = tmp;

    this.menjodohkan.matchIndex = index
    setTimeout(() => {
        this.menjodohkan.matchIndex = null
    }, 500)

    this.menjodohkan.left = null
    this.menjodohkan.right = null

    this.submitJawabanMenjodohkan({
        jawaban_id : this.filleds[this.questionIndex].id,
        menjodohkan: tmp.map(item => [item.a.id, item.b.id]),
        index : this.questionIndex
    }).catch((error) => {
        this.showError(error)
    })
}

/**
 * Click right area menjodohkan
 */
function menjodohkanRightClick(index) {
    if (this.menjodohkan.right == index) {
        this.menjodohkan.right = null;
        return;
    }
    this.menjodohkan.right = index
    if (this.menjodohkan.left == null) {
        return;
    }
    let tmp = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans));
    // tmp[this.menjodohkan.left].b = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans[index].b));
    // tmp[index].b = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans[this.menjodohkan.left].b));

    tmp[this.menjodohkan.left].a = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans[index].a));
    tmp[index].a = JSON.parse(JSON.stringify(this.filleds[this.questionIndex].soal.jawabans[this.menjodohkan.left].a));

    this.filleds[this.questionIndex].soal.jawabans = tmp;

    this.menjodohkan.matchIndex = index
    setTimeout(() => {
       this.menjodohkan.matchIndex = null
    }, 500)

    this.menjodohkan.left = null
    this.menjodohkan.right = null

    this.submitJawabanMenjodohkan({
        jawaban_id : this.filleds[this.questionIndex].id,
        menjodohkan: tmp.map(item => [item.a.id, item.b.id]),
        index : this.questionIndex
    }).catch((error) => {
        this.showError(error)
    })
}
