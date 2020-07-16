<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-8">
                <div class="page-inner mt--5">
                    <div class="card card-bg">
                        <div class="card-header">
                            <h4>{{datad.alias}}</h4>                      
                        </div>
                        <div class="card-body">
                            <div class="card-body">
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <td width="140px">Tanggal</td>
                                        <td>{{ datad.tanggal }}</td>
                                    </tr>
                                    <tr>
                                        <td>Mulai</td>
                                        <td>{{ datad.mulai }}</td>
                                    </tr>
                                    <tr>
                                        <td>Alokasi waktu</td>
                                        <td>
                                            {{ Math.floor(datad.lama / 60) }} Menit
                                        </td>
                                    </tr>
                                </table>
                                <div class="alert alert-info mt-2" v-if="disable">
                                    <p>Tombol MULAI hanya akan muncul apabila waktu sekarang sudah melewati waktu mulai tes</p>
                                </div>
                                <button type="button" class="btn btn-info w-100 rounded-pill" @click="start" 
                                v-if="!disable" :disabled="isLoading">
                                    {{ isLoading ? 'Loading...' : 'MULAI' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { mapActions, mapState, mapGetters } from 'vuex'
import { successToas, errorToas} from '../../entities/notif'

export default {
    name: 'PrepareUjian',
    data() {
        return {
            disable: true,
            time: '',
            starter: '',
            durasi: '',
            datad: {}
        }
    },
    computed: {
        ...mapGetters(['isLoading']),
        ...mapState('siswa_ujian',{
            ujian: state => state.ujian
        }),
        ...mapState('siswa_jadwal', {
            jadwal: state => state.banksoalAktif
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
        getDataUjian() {
            try {
                this.changeData()
            } catch (error) {
                this.$bvToast.toast(error.message, errorToas())
            } 
        },
        startTime() {
            setInterval( () => {
                this.time = new Date()
            }, 1000 )
        },
        changeData() {
            if(typeof this.ujian.jadwal_id != 'undefined') {
                if(this.jadwal.length != 'undefined') {
                    let index;
                    if(this.jadwal.length == 1) {
                        index = 0;    
                    } else {
                        index = this.jadwal.map(item => item.id).indexOf(this.ujian.jadwal_id)
                    }
                    if(index !== -1) {
                        this.datad = this.jadwal[index]
                        const date = new Date()
                        const ye = date.getFullYear()
                        const mo = date.getMonth()
                        const da = date.getDate()
                        const mulai = this.datad.mulai
                        const splicer = mulai.split(":")
                        const h = parseInt(splicer[0])
                        const i = parseInt(splicer[1])
                        const s = parseInt(splicer[2])
                        const rest = new Date(ye,mo,da,h,i,s)
                        this.starter = rest
                        this.startTime()
                    }
                }
            }
        }
    },
    created() {
        this.getDataUjian()
    },
    watch: {
        ujian() {
            this.getDataUjian()
        },
        time() {
            if(this.starter < this.time) {
                this.disable = false
            }
        },
        jadwal() {
            this.changeData()
        }
    }
}
</script>