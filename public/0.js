(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[0],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/siswa/AudioPlayer.vue?vue&type=script&lang=js&":
/*!****************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/siswa/AudioPlayer.vue?vue&type=script&lang=js& ***!
  \****************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  props: {
    file: {
      type: String,
      "default": null
    },
    autoPlay: {
      type: Boolean,
      "default": false
    },
    loop: {
      type: Boolean,
      "default": false
    },
    show: {
      type: Boolean,
      "default": true
    }
  },
  data: function data() {
    return {
      audio: undefined,
      currentSeconds: 0,
      durationSeconds: 0,
      innerLoop: false,
      loaded: false,
      playing: false,
      previousVolume: 35,
      showVolume: false,
      volume: 100
    };
  },
  computed: {
    percentComplete: function percentComplete() {
      return parseInt(this.currentSeconds / this.durationSeconds * 100);
    },
    muted: function muted() {
      return this.volume / 100 === 0;
    }
  },
  filters: {
    convertTimeHHMMSS: function convertTimeHHMMSS(val) {
      var hhmmss = new Date(val * 1000).toISOString().substr(11, 8);
      return hhmmss.indexOf("00:") === 0 ? hhmmss.substr(3) : hhmmss;
    }
  },
  watch: {
    playing: function playing(value) {
      if (value) {
        return this.audio.play();
      }

      this.audio.pause();
    },
    volume: function volume(value) {
      this.showVolume = false;
      this.audio.volume = this.volume / 100;
    }
  },
  methods: {
    download: function download() {
      this.stop();
      window.open(this.file, 'download');
    },
    load: function load() {
      if (this.audio.readyState >= 2) {
        this.loaded = true;
        this.durationSeconds = parseInt(this.audio.duration);
        return this.playing = this.autoPlay;
      }

      throw new Error('Failed to load sound file.');
    },
    mute: function mute() {
      if (this.muted) {
        return this.volume = this.previousVolume;
      }

      this.previousVolume = this.volume;
      this.volume = 0;
    },
    seek: function seek(e) {
      if (!this.playing || e.target.tagName === 'SPAN') {
        return;
      }

      var el = e.target.getBoundingClientRect();
      var seekPos = (e.clientX - el.left) / el.width;
      this.audio.currentTime = parseInt(this.audio.duration * seekPos);
    },
    stop: function stop() {
      this.playing = false;
      this.audio.currentTime = 0;
    },
    update: function update(e) {
      this.currentSeconds = parseInt(this.audio.currentTime);
    }
  },
  created: function created() {
    this.innerLoop = this.loop;
  },
  mounted: function mounted() {
    var _this = this;

    this.audio = this.$el.querySelectorAll('audio')[0];
    this.audio.addEventListener('timeupdate', this.update);
    this.audio.addEventListener('loadeddata', this.load);
    this.audio.addEventListener('pause', function () {
      _this.playing = false;
    });
    this.audio.addEventListener('play', function () {
      _this.playing = true;
    });
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/Kerjakan.vue?vue&type=script&lang=js&":
/*!********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/pages/siswa/Kerjakan.vue?vue&type=script&lang=js& ***!
  \********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var vue_loading_overlay__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue-loading-overlay */ "./node_modules/vue-loading-overlay/dist/vue-loading.min.js");
/* harmony import */ var vue_loading_overlay__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(vue_loading_overlay__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var vue_loading_overlay_dist_vue_loading_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! vue-loading-overlay/dist/vue-loading.css */ "./node_modules/vue-loading-overlay/dist/vue-loading.css");
/* harmony import */ var vue_loading_overlay_dist_vue_loading_css__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(vue_loading_overlay_dist_vue_loading_css__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm.js");
/* harmony import */ var _components_siswa_AudioPlayer_vue__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../components/siswa/AudioPlayer.vue */ "./resources/js/components/siswa/AudioPlayer.vue");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! lodash */ "./node_modules/lodash/lodash.js");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _entities_notif__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../../entities/notif */ "./resources/js/entities/notif.js");


function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//






/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'KerjakannUjian',
  components: {
    AudioPlayer: _components_siswa_AudioPlayer_vue__WEBPACK_IMPORTED_MODULE_4__["default"],
    Loading: vue_loading_overlay__WEBPACK_IMPORTED_MODULE_1___default.a
  },
  data: function data() {
    return {
      questionIndex: '',
      selected: '',
      patt: 17,
      sidebar: false,
      ragu: '',
      time: 0,
      isKonfirm: false,
      interval: '',
      audio: '',
      direction: '',
      listening: true,
      hasdirec: [],
      range: 16,
      editorConfig: {
        allowedContent: true
      }
    };
  },
  filters: {
    charIndex: function charIndex(i) {
      return String.fromCharCode(97 + i);
    }
  },
  computed: _objectSpread(_objectSpread(_objectSpread(_objectSpread(_objectSpread({}, Object(vuex__WEBPACK_IMPORTED_MODULE_3__["mapGetters"])(['isLoading', 'isLoadinger'])), Object(vuex__WEBPACK_IMPORTED_MODULE_3__["mapState"])('siswa_jadwal', {
    jadwal: function jadwal(state) {
      return state.banksoalAktif;
    }
  })), Object(vuex__WEBPACK_IMPORTED_MODULE_3__["mapState"])('siswa_user', {
    peserta: function peserta(state) {
      return state.pesertaDetail;
    }
  })), Object(vuex__WEBPACK_IMPORTED_MODULE_3__["mapState"])('siswa_ujian', {
    jawabanPeserta: function jawabanPeserta(state) {
      return state.jawabanPeserta;
    },
    filleds: function filleds(state) {
      return state.filledUjian.data;
    },
    detail: function detail(state) {
      return state.filledUjian.detail;
    }
  })), {}, {
    prettyTime: function prettyTime() {
      var sec_num = parseInt(this.time, 10);
      var hours = Math.floor(sec_num / 3600);
      var minutes = Math.floor((sec_num - hours * 3600) / 60);
      var seconds = sec_num - hours * 3600 - minutes * 60;
      return hours + ':' + minutes + ':' + seconds;
    }
  }),
  methods: _objectSpread(_objectSpread({}, Object(vuex__WEBPACK_IMPORTED_MODULE_3__["mapActions"])('siswa_ujian', ['takeFilled', 'submitJawaban', 'submitJawabanEssy', 'selesaiUjianPeserta', 'updateRaguJawaban'])), {}, {
    filledAllSoal: function filledAllSoal() {
      var _this = this;

      return _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee() {
        var payld;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                payld = {
                  peserta_id: _this.peserta.id,
                  banksoal: _this.jadwal.banksoal_id,
                  jadwal_id: _this.jadwal.ujian_id
                };
                _context.prev = 1;
                _context.next = 4;
                return _this.takeFilled(payld);

              case 4:
                _context.next = 9;
                break;

              case 6:
                _context.prev = 6;
                _context.t0 = _context["catch"](1);

                _this.$bvToast.toast(_context.t0.message, Object(_entities_notif__WEBPACK_IMPORTED_MODULE_6__["errorToas"])());

              case 9:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, null, [[1, 6]]);
      }))();
    },
    selectOption: function selectOption(index) {
      var _this2 = this;

      var fill = this.filleds[this.questionIndex];
      this.submitJawaban({
        jawaban_id: this.filleds[this.questionIndex].id,
        jawab: this.filleds[this.questionIndex].soal.jawabans[index].id,
        correct: this.filleds[this.questionIndex].soal.jawabans[index].correct,
        index: this.questionIndex
      })["catch"](function (error) {
        _this2.$bvToast.toast(error.message, Object(_entities_notif__WEBPACK_IMPORTED_MODULE_6__["errorToas"])());
      });
    },
    inputJawabEssy: function inputJawabEssy(val) {
      var _this3 = this;

      var fill = this.filleds[this.questionIndex];
      this.submitJawabanEssy({
        jawaban_id: this.filleds[this.questionIndex].id,
        index: this.questionIndex,
        essy: fill.esay
      })["catch"](function (error) {
        _this3.$bvToast.toast(error.message, Object(_entities_notif__WEBPACK_IMPORTED_MODULE_6__["errorToas"])());
      });
    },
    onInput: lodash__WEBPACK_IMPORTED_MODULE_5___default.a.debounce(function (value) {
      this.inputJawabEssy(value);
    }, 500),
    prev: function prev() {
      if (this.filleds.length > 0) this.questionIndex--;
    },
    next: function next() {
      if (this.questionIndex < this.filleds.length) this.questionIndex++;
    },
    toggle: function toggle() {
      this.sidebar = !this.sidebar;
    },
    toLand: function toLand(index) {
      this.questionIndex = index;
    },
    start: function start() {
      var _this4 = this;

      this.timer = setInterval(function () {
        if (_this4.time > 0) {
          _this4.time--;
        } else {}
      }, 1000);
    },
    selesai: function selesai() {
      var _this5 = this;

      return _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee2() {
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                _context2.prev = 0;
                _context2.next = 3;
                return _this5.selesaiUjianPeserta({
                  peserta_id: _this5.peserta.id,
                  jadwal_id: _this5.detail.jadwal_id
                });

              case 3:
                _this5.$router.push({
                  name: 'ujian.selesai'
                });

                clearInterval(_this5.interval);
                _context2.next = 10;
                break;

              case 7:
                _context2.prev = 7;
                _context2.t0 = _context2["catch"](0);

                _this5.$bvToast.toast(_context2.t0.message, Object(_entities_notif__WEBPACK_IMPORTED_MODULE_6__["errorToas"])());

              case 10:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2, null, [[0, 7]]);
      }))();
    },
    raguRagu: function raguRagu(val) {
      var _this6 = this;

      if (val === '') {
        return;
      }

      this.updateRaguJawaban({
        ragu_ragu: val,
        index: this.questionIndex,
        jawaban_id: this.filleds[this.questionIndex].id
      })["catch"](function (error) {
        _this6.$bvToast.toast(error.message, Object(_entities_notif__WEBPACK_IMPORTED_MODULE_6__["errorToas"])());
      });
    },
    checkRagu: function checkRagu() {
      var ragger = 0;
      this.filleds.filter(function (element) {
        if (element.ragu_ragu == "1") {
          ragger++;
        }
      });

      if (ragger > 0) {
        return true;
      }

      return false;
    },
    playDirection: function playDirection() {
      var _this7 = this;

      this.listening = false;
      this.direction.play();

      this.direction.onended = function () {
        _this7.hasdirec.push(_this7.filleds[_this7.questionIndex].soal.id);

        _this7.listening = true;
      };

      this.$bvModal.hide('modal-direction');
    }
  }),
  created: function created() {
    var _this8 = this;

    return _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee3() {
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee3$(_context3) {
        while (1) {
          switch (_context3.prev = _context3.next) {
            case 0:
              if (!(typeof _this8.jadwal.jadwal != 'undefined')) {
                _context3.next = 4;
                break;
              }

              _context3.next = 3;
              return _this8.filledAllSoal();

            case 3:
              _this8.start();

            case 4:
            case "end":
              return _context3.stop();
          }
        }
      }, _callee3);
    }))();
  },
  watch: {
    questionIndex: function questionIndex() {
      this.selected = this.filleds[this.questionIndex].jawab;
      this.ragu = this.filleds[this.questionIndex].ragu_ragu;

      if (this.filleds[this.questionIndex].soal.audio != null) {
        this.audio = this.filleds[this.questionIndex].soal.audio;
      } else {
        this.audio = '';
      }

      if (this.filleds[this.questionIndex].soal.direction != null) {
        this.direction = new Audio('/storage/audio/' + this.filleds[this.questionIndex].soal.direction);
      } else {
        if (this.direction != '') {
          this.direction.pause();
        }

        this.direction = '';
      }
    },
    filleds: function filleds() {
      this.questionIndex = 0;
    },
    detail: function detail(val) {
      var _this9 = this;

      this.time = val.sisa_waktu;
      this.interval = setInterval(function () {
        if (_this9.time > 0) {} else {
          _this9.selesai();
        }
      }, 5000);
    },
    jadwal: function jadwal(val) {
      var _this10 = this;

      return _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee4() {
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee4$(_context4) {
          while (1) {
            switch (_context4.prev = _context4.next) {
              case 0:
                if (!(typeof _this10.jadwal.jadwal != 'undefined')) {
                  _context4.next = 4;
                  break;
                }

                _context4.next = 3;
                return _this10.filledAllSoal();

              case 3:
                _this10.start();

              case 4:
              case "end":
                return _context4.stop();
            }
          }
        }, _callee4);
      }))();
    },
    ragu: function ragu(val) {
      if (val == false) {
        var set = 0;
        this.raguRagu(set);
      } else {
        this.raguRagu(val);
      }
    },
    direction: function direction(val) {
      if (val != '') {
        if (this.hasdirec.includes(this.filleds[this.questionIndex].soal.id)) {
          return;
        }

        this.$bvModal.show('modal-direction');
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/css-loader/index.js!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/sass-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/siswa/AudioPlayer.vue?vue&type=style&index=0&lang=scss&":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--7-2!./node_modules/sass-loader/dist/cjs.js??ref--7-3!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/siswa/AudioPlayer.vue?vue&type=style&index=0&lang=scss& ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, ".player-wrapper {\n  align-items: center;\n  display: flex;\n}\n.player {\n  background-color: #fff;\n  border-radius: 5px;\n  border: 1px solid #e0e0e0;\n  color: #404040;\n  display: inline-block;\n  line-height: 1.5625;\n}\n.player-controls {\n  display: flex;\n}\n.player-controls > div {\n  border-right: 1px solid #e0e0e0;\n}\n.player-controls > div:last-child {\n  border-right: none;\n}\n.player-controls > div a {\n  color: #404040;\n  display: block;\n  line-height: 0;\n  padding: 1em;\n  text-decoration: none;\n}\n.player-progress {\n  background-color: #e0e0e0;\n  cursor: pointer;\n  height: 50%;\n  min-width: 200px;\n  position: relative;\n}\n.player-progress .player-seeker {\n  background-color: #404040;\n  bottom: 0;\n  left: 0;\n  position: absolute;\n  top: 0;\n}\n.player-time {\n  display: flex;\n  justify-content: space-between;\n}\n.player-time .player-time-current {\n  font-weight: 700;\n  padding-left: 5px;\n}\n.player-time .player-time-total {\n  opacity: 0.5;\n  padding-right: 5px;\n}", ""]);

// exports


/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/Kerjakan.vue?vue&type=style&index=0&lang=css&":
/*!***************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/pages/siswa/Kerjakan.vue?vue&type=style&index=0&lang=css& ***!
  \***************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "\n.custom-control.custom-radio, .custom-control.custom-checkbox {\n    line-height: 34px !important;\n}\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/sass-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/siswa/AudioPlayer.vue?vue&type=style&index=0&lang=scss&":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--7-2!./node_modules/sass-loader/dist/cjs.js??ref--7-3!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/siswa/AudioPlayer.vue?vue&type=style&index=0&lang=scss& ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../../node_modules/css-loader!../../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../../node_modules/postcss-loader/src??ref--7-2!../../../../node_modules/sass-loader/dist/cjs.js??ref--7-3!../../../../node_modules/vue-loader/lib??vue-loader-options!./AudioPlayer.vue?vue&type=style&index=0&lang=scss& */ "./node_modules/css-loader/index.js!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/sass-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/siswa/AudioPlayer.vue?vue&type=style&index=0&lang=scss&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/Kerjakan.vue?vue&type=style&index=0&lang=css&":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/pages/siswa/Kerjakan.vue?vue&type=style&index=0&lang=css& ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../../node_modules/css-loader??ref--6-1!../../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../../node_modules/postcss-loader/src??ref--6-2!../../../../node_modules/vue-loader/lib??vue-loader-options!./Kerjakan.vue?vue&type=style&index=0&lang=css& */ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/Kerjakan.vue?vue&type=style&index=0&lang=css&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/siswa/AudioPlayer.vue?vue&type=template&id=81809b58&":
/*!********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/siswa/AudioPlayer.vue?vue&type=template&id=81809b58& ***!
  \********************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "player-wrapper", attrs: { id: "audio" } }, [
    _vm.show
      ? _c("div", { staticClass: "player" }, [
          _c("div", { staticClass: "player-controls" }, [
            _c("div", [
              _c(
                "a",
                {
                  attrs: { title: "Play/Pause", href: "#" },
                  on: {
                    click: function($event) {
                      $event.preventDefault()
                      _vm.playing = !_vm.playing
                    }
                  }
                },
                [
                  _c(
                    "svg",
                    {
                      attrs: {
                        width: "18px",
                        xmlns: "http://www.w3.org/2000/svg",
                        viewBox: "0 0 20 20"
                      }
                    },
                    [
                      !_vm.playing
                        ? _c("path", {
                            attrs: {
                              fill: "currentColor",
                              d:
                                "M15,10.001c0,0.299-0.305,0.514-0.305,0.514l-8.561,5.303C5.51,16.227,5,15.924,5,15.149V4.852c0-0.777,0.51-1.078,1.135-0.67l8.561,5.305C14.695,9.487,15,9.702,15,10.001z"
                            }
                          })
                        : _c("path", {
                            attrs: {
                              fill: "currentColor",
                              d:
                                "M15,3h-2c-0.553,0-1,0.048-1,0.6v12.8c0,0.552,0.447,0.6,1,0.6h2c0.553,0,1-0.048,1-0.6V3.6C16,3.048,15.553,3,15,3z M7,3H5C4.447,3,4,3.048,4,3.6v12.8C4,16.952,4.447,17,5,17h2c0.553,0,1-0.048,1-0.6V3.6C8,3.048,7.553,3,7,3z"
                            }
                          })
                    ]
                  )
                ]
              )
            ]),
            _vm._v(" "),
            _c("div", [
              _c(
                "div",
                {
                  staticClass: "player-progress",
                  attrs: { title: "Time played : Total time" }
                },
                [
                  _c("div", {
                    staticClass: "player-seeker",
                    style: { width: this.percentComplete + "%" }
                  })
                ]
              ),
              _vm._v(" "),
              _c("div", { staticClass: "player-time" }, [
                _c("div", { staticClass: "player-time-current" }, [
                  _vm._v(
                    _vm._s(_vm._f("convertTimeHHMMSS")(this.currentSeconds))
                  )
                ]),
                _vm._v(" "),
                _c("div", { staticClass: "player-time-total" }, [
                  _vm._v(
                    _vm._s(_vm._f("convertTimeHHMMSS")(this.durationSeconds))
                  )
                ])
              ])
            ])
          ]),
          _vm._v(" "),
          _c("audio", {
            ref: "audiofile",
            staticStyle: { display: "none" },
            attrs: { loop: _vm.innerLoop, src: _vm.file, preload: "auto" }
          })
        ])
      : _vm._e()
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/Kerjakan.vue?vue&type=template&id=1449404c&":
/*!************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/pages/siswa/Kerjakan.vue?vue&type=template&id=1449404c& ***!
  \************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    [
      _c("loading", {
        attrs: { active: _vm.isLoading, "is-full-page": true },
        on: {
          "update:active": function($event) {
            _vm.isLoading = $event
          }
        }
      }),
      _vm._v(" "),
      _vm.filleds
        ? _c("div", { staticClass: "container exam mt--5" }, [
            _c("div", { staticClass: "card" }, [
              _c("div", { staticClass: "card-body" }, [
                _c("div", { staticClass: "bar-top" }, [
                  _c("span", [_vm._v("SOAL NOMOR")]),
                  _vm._v(" "),
                  _c(
                    "div",
                    { staticClass: "soal-title", attrs: { id: "page" } },
                    [_vm._v(_vm._s(_vm.questionIndex + 1))]
                  ),
                  _vm._v(" "),
                  _c("div", {
                    staticStyle: { display: "none" },
                    attrs: { id: "page-count" }
                  }),
                  _vm._v(" "),
                  _c("div", { staticClass: "right" }, [
                    _c(
                      "div",
                      { staticClass: "btn-group" },
                      [
                        _c(
                          "button",
                          {
                            staticClass: "btn btn-outline-danger btn-soal",
                            attrs: { type: "button" }
                          },
                          [
                            _c("i", { staticClass: "cil-clock" }),
                            _vm._v(" " + _vm._s(_vm.prettyTime))
                          ]
                        ),
                        _vm._v(" "),
                        _c(
                          "b-button",
                          {
                            directives: [
                              {
                                name: "b-modal",
                                rawName: "v-b-modal.nomorSoal",
                                modifiers: { nomorSoal: true }
                              }
                            ],
                            staticClass: "btn-soal",
                            attrs: { variant: "info", disabled: !_vm.listening }
                          },
                          [
                            _c("span", { staticClass: "cil-apps" }),
                            _vm._v(" Daftar Soal")
                          ]
                        )
                      ],
                      1
                    )
                  ])
                ]),
                _vm._v(" "),
                _c(
                  "div",
                  { staticClass: "bar-text" },
                  [
                    _c("span", [_vm._v("Ukuran Soal :\n                    ")]),
                    _vm._v(" "),
                    _c("b-form-input", {
                      attrs: { type: "range", min: "12", max: "30" },
                      model: {
                        value: _vm.range,
                        callback: function($$v) {
                          _vm.range = $$v
                        },
                        expression: "range"
                      }
                    })
                  ],
                  1
                ),
                _vm._v(" "),
                typeof _vm.filleds[_vm.questionIndex] != "undefined"
                  ? _c(
                      "div",
                      { staticClass: "soal-wrapper", attrs: { id: "content" } },
                      [
                        _c(
                          "table",
                          { staticClass: "table table-borderless table-sm" },
                          [
                            _vm.audio != ""
                              ? _c("tr", [
                                  _c(
                                    "td",
                                    { attrs: { colspan: "2" } },
                                    [
                                      _vm.listening
                                        ? _c("audio-player", {
                                            attrs: {
                                              file:
                                                "/storage/audio/" + _vm.audio
                                            }
                                          })
                                        : _vm._e()
                                    ],
                                    1
                                  )
                                ])
                              : _vm._e(),
                            _vm._v(" "),
                            _c("tr", [
                              _c("td", {
                                style:
                                  "font-size:" + _vm.range + "px !important",
                                attrs: { colspan: "2" },
                                domProps: {
                                  innerHTML: _vm._s(
                                    _vm.filleds[_vm.questionIndex].soal
                                      .pertanyaan
                                  )
                                }
                              })
                            ]),
                            _vm._v(" "),
                            _vm._l(
                              _vm.filleds[_vm.questionIndex].soal.jawabans,
                              function(jawab, index) {
                                return _c("tr", { key: index }, [
                                  _c(
                                    "td",
                                    {
                                      style:
                                        "font-size:" +
                                        _vm.range +
                                        "px !important",
                                      attrs: { width: "50px" }
                                    },
                                    [
                                      _c(
                                        "b-form-radio",
                                        {
                                          attrs: {
                                            size: "lg",
                                            name: "jwb",
                                            value: jawab.id
                                          },
                                          on: {
                                            change: function($event) {
                                              return _vm.selectOption(index)
                                            }
                                          },
                                          model: {
                                            value: _vm.selected,
                                            callback: function($$v) {
                                              _vm.selected = $$v
                                            },
                                            expression: "selected"
                                          }
                                        },
                                        [
                                          _c(
                                            "span",
                                            { staticClass: "text-uppercase" },
                                            [
                                              _vm._v(
                                                _vm._s(
                                                  _vm._f("charIndex")(index)
                                                )
                                              )
                                            ]
                                          ),
                                          _vm._v(
                                            ".\n                                "
                                          )
                                        ]
                                      )
                                    ],
                                    1
                                  ),
                                  _vm._v(" "),
                                  _c("td", {
                                    style:
                                      "font-size:" +
                                      _vm.range +
                                      "px !important",
                                    domProps: {
                                      innerHTML: _vm._s(jawab.text_jawaban)
                                    }
                                  })
                                ])
                              }
                            ),
                            _vm._v(" "),
                            _vm.filleds[_vm.questionIndex].soal.tipe_soal == 2
                              ? _c("tr", [
                                  _c(
                                    "td",
                                    { attrs: { height: "auto" } },
                                    [
                                      _c("ckeditor", {
                                        attrs: { config: _vm.editorConfig },
                                        on: { input: _vm.onInput },
                                        model: {
                                          value:
                                            _vm.filleds[_vm.questionIndex].esay,
                                          callback: function($$v) {
                                            _vm.$set(
                                              _vm.filleds[_vm.questionIndex],
                                              "esay",
                                              $$v
                                            )
                                          },
                                          expression:
                                            "filleds[questionIndex].esay"
                                        }
                                      })
                                    ],
                                    1
                                  )
                                ])
                              : _vm._e()
                          ],
                          2
                        )
                      ]
                    )
                  : _vm._e(),
                _vm._v(" "),
                _c(
                  "div",
                  { staticClass: "button-wrapper" },
                  [
                    _vm.questionIndex != 0
                      ? _c(
                          "b-button",
                          {
                            staticClass: "sebelum",
                            attrs: {
                              variant: "info",
                              size: "md",
                              disabled: _vm.isLoadinger || !_vm.listening
                            },
                            on: {
                              click: function($event) {
                                return _vm.prev()
                              }
                            }
                          },
                          [
                            _c("span", { staticClass: "cil-chevron-left" }),
                            _vm._v(
                              "\n                        Sebelumnya\n                    "
                            )
                          ]
                        )
                      : _vm._e(),
                    _vm._v(" "),
                    _c(
                      "button",
                      {
                        staticClass: "btn btn-warning ml-auto",
                        attrs: { id: "soal-ragu" }
                      },
                      [
                        _c(
                          "b-form-checkbox",
                          {
                            attrs: { size: "lg", value: "1" },
                            model: {
                              value: _vm.ragu,
                              callback: function($$v) {
                                _vm.ragu = $$v
                              },
                              expression: "ragu"
                            }
                          },
                          [_vm._v("Ragu ragu")]
                        )
                      ],
                      1
                    ),
                    _vm._v(" "),
                    _vm.questionIndex + 1 != _vm.filleds.length
                      ? _c(
                          "b-button",
                          {
                            staticClass: "sesudah",
                            attrs: {
                              variant: "info",
                              size: "md",
                              disabled: _vm.isLoadinger || !_vm.listening
                            },
                            on: {
                              click: function($event) {
                                return _vm.next()
                              }
                            }
                          },
                          [
                            _vm._v("\n                        Selanjutnya "),
                            _c("span", { staticClass: "cil-chevron-right" })
                          ]
                        )
                      : _vm._e(),
                    _vm._v(" "),
                    _vm.questionIndex + 1 == _vm.filleds.length &&
                    _vm.checkRagu() == false
                      ? _c(
                          "b-button",
                          {
                            staticClass: "sesudah",
                            attrs: {
                              variant: "success",
                              size: "md",
                              disabled: _vm.isLoadinger
                            },
                            on: {
                              click: function($event) {
                                return _vm.$bvModal.show("modal-selesai")
                              }
                            }
                          },
                          [
                            _vm._v("\n                        SELESAI "),
                            _c("i", { staticClass: "cil-check" })
                          ]
                        )
                      : _vm._e(),
                    _vm._v(" "),
                    _vm.questionIndex + 1 == _vm.filleds.length &&
                    _vm.checkRagu() == true
                      ? _c(
                          "b-button",
                          {
                            directives: [
                              {
                                name: "b-modal",
                                rawName: "v-b-modal.modal-1",
                                modifiers: { "modal-1": true }
                              }
                            ],
                            staticClass: "sesudah",
                            attrs: { variant: "danger", size: "md" }
                          },
                          [
                            _vm._v("\n                        SELESAI "),
                            _c("i", { staticClass: "cil-check" })
                          ]
                        )
                      : _vm._e()
                  ],
                  1
                )
              ])
            ])
          ])
        : _vm._e(),
      _vm._v(" "),
      _c("b-modal", {
        staticClass: "shadow",
        attrs: { id: "modal-selesai", centered: "" },
        scopedSlots: _vm._u([
          {
            key: "modal-header",
            fn: function(ref) {
              var close = ref.close
              return [_c("h5", [_vm._v("Konfirmasi")])]
            }
          },
          {
            key: "default",
            fn: function(ref) {
              var hide = ref.hide
              return [
                _c(
                  "b-form-checkbox",
                  {
                    attrs: { size: "lg" },
                    model: {
                      value: _vm.isKonfirm,
                      callback: function($$v) {
                        _vm.isKonfirm = $$v
                      },
                      expression: "isKonfirm"
                    }
                  },
                  [_vm._v("Saya sudah selesai mengerjakan")]
                )
              ]
            }
          },
          {
            key: "modal-footer",
            fn: function(ref) {
              var cancel = ref.cancel
              return [
                _c(
                  "div",
                  { staticClass: "button-wrapper" },
                  [
                    _c(
                      "b-button",
                      {
                        attrs: {
                          size: "sm",
                          variant: "success",
                          disabled: !_vm.isKonfirm
                        },
                        on: {
                          click: function($event) {
                            return _vm.selesai()
                          }
                        }
                      },
                      [_vm._v("\n                Selesai\n              ")]
                    ),
                    _vm._v(" "),
                    _c(
                      "b-button",
                      {
                        attrs: { size: "sm", variant: "danger" },
                        on: {
                          click: function($event) {
                            return cancel()
                          }
                        }
                      },
                      [_vm._v("\n                Cancel\n              ")]
                    )
                  ],
                  1
                )
              ]
            }
          }
        ])
      }),
      _vm._v(" "),
      _c("b-modal", {
        staticClass: "shadow",
        attrs: { id: "nomorSoal", title: "Nomor Soal", size: "lg" },
        scopedSlots: _vm._u([
          {
            key: "modal-footer",
            fn: function(ref) {
              var cancel = ref.cancel
              return [
                _c(
                  "b-button",
                  {
                    attrs: { size: "sm", variant: "info" },
                    on: {
                      click: function($event) {
                        return cancel()
                      }
                    }
                  },
                  [_vm._v("\n            Tutup\n          ")]
                )
              ]
            }
          },
          {
            key: "default",
            fn: function(ref) {
              var hide = ref.hide
              return [
                _c(
                  "ul",
                  { staticClass: "nomor-soal", attrs: { id: "nomor-soal" } },
                  _vm._l(_vm.filleds, function(fiel, index) {
                    return _c("li", { key: index }, [
                      _c(
                        "a",
                        {
                          class: {
                            isi: fiel.jawab != 0 || fiel.esay != null,
                            ragu: fiel.ragu_ragu == 1,
                            active: index == _vm.questionIndex
                          },
                          attrs: { href: "#", disabled: _vm.isLoadinger },
                          on: {
                            click: function($event) {
                              $event.preventDefault()
                              return _vm.toLand(index)
                            }
                          }
                        },
                        [
                          _vm._v(
                            "\n                        " +
                              _vm._s(index + 1) +
                              " \n                        "
                          ),
                          _c("span")
                        ]
                      )
                    ])
                  }),
                  0
                )
              ]
            }
          }
        ])
      }),
      _vm._v(" "),
      _c("b-modal", {
        staticClass: "shadow",
        attrs: { id: "modal-direction", centered: "", title: "Direction" },
        scopedSlots: _vm._u([
          {
            key: "modal-footer",
            fn: function(ref) {
              var cancel = ref.cancel
              return [
                _c(
                  "div",
                  { staticClass: "button-wrapper" },
                  [
                    _c(
                      "b-button",
                      {
                        attrs: { size: "sm", variant: "info" },
                        on: {
                          click: function($event) {
                            return _vm.playDirection()
                          }
                        }
                      },
                      [_vm._v("\n                Oke\n              ")]
                    )
                  ],
                  1
                )
              ]
            }
          },
          {
            key: "default",
            fn: function(ref) {
              var hide = ref.hide
              return [_vm._v("\n            Listen for direction\n        ")]
            }
          }
        ])
      })
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/components/siswa/AudioPlayer.vue":
/*!*******************************************************!*\
  !*** ./resources/js/components/siswa/AudioPlayer.vue ***!
  \*******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _AudioPlayer_vue_vue_type_template_id_81809b58___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AudioPlayer.vue?vue&type=template&id=81809b58& */ "./resources/js/components/siswa/AudioPlayer.vue?vue&type=template&id=81809b58&");
/* harmony import */ var _AudioPlayer_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AudioPlayer.vue?vue&type=script&lang=js& */ "./resources/js/components/siswa/AudioPlayer.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _AudioPlayer_vue_vue_type_style_index_0_lang_scss___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AudioPlayer.vue?vue&type=style&index=0&lang=scss& */ "./resources/js/components/siswa/AudioPlayer.vue?vue&type=style&index=0&lang=scss&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _AudioPlayer_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _AudioPlayer_vue_vue_type_template_id_81809b58___WEBPACK_IMPORTED_MODULE_0__["render"],
  _AudioPlayer_vue_vue_type_template_id_81809b58___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/siswa/AudioPlayer.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/siswa/AudioPlayer.vue?vue&type=script&lang=js&":
/*!********************************************************************************!*\
  !*** ./resources/js/components/siswa/AudioPlayer.vue?vue&type=script&lang=js& ***!
  \********************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AudioPlayer_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./AudioPlayer.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/siswa/AudioPlayer.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AudioPlayer_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/siswa/AudioPlayer.vue?vue&type=style&index=0&lang=scss&":
/*!*****************************************************************************************!*\
  !*** ./resources/js/components/siswa/AudioPlayer.vue?vue&type=style&index=0&lang=scss& ***!
  \*****************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_2_node_modules_sass_loader_dist_cjs_js_ref_7_3_node_modules_vue_loader_lib_index_js_vue_loader_options_AudioPlayer_vue_vue_type_style_index_0_lang_scss___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader!../../../../node_modules/css-loader!../../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../../node_modules/postcss-loader/src??ref--7-2!../../../../node_modules/sass-loader/dist/cjs.js??ref--7-3!../../../../node_modules/vue-loader/lib??vue-loader-options!./AudioPlayer.vue?vue&type=style&index=0&lang=scss& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/sass-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/siswa/AudioPlayer.vue?vue&type=style&index=0&lang=scss&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_2_node_modules_sass_loader_dist_cjs_js_ref_7_3_node_modules_vue_loader_lib_index_js_vue_loader_options_AudioPlayer_vue_vue_type_style_index_0_lang_scss___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_2_node_modules_sass_loader_dist_cjs_js_ref_7_3_node_modules_vue_loader_lib_index_js_vue_loader_options_AudioPlayer_vue_vue_type_style_index_0_lang_scss___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_2_node_modules_sass_loader_dist_cjs_js_ref_7_3_node_modules_vue_loader_lib_index_js_vue_loader_options_AudioPlayer_vue_vue_type_style_index_0_lang_scss___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_2_node_modules_sass_loader_dist_cjs_js_ref_7_3_node_modules_vue_loader_lib_index_js_vue_loader_options_AudioPlayer_vue_vue_type_style_index_0_lang_scss___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_style_loader_index_js_node_modules_css_loader_index_js_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_2_node_modules_sass_loader_dist_cjs_js_ref_7_3_node_modules_vue_loader_lib_index_js_vue_loader_options_AudioPlayer_vue_vue_type_style_index_0_lang_scss___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/components/siswa/AudioPlayer.vue?vue&type=template&id=81809b58&":
/*!**************************************************************************************!*\
  !*** ./resources/js/components/siswa/AudioPlayer.vue?vue&type=template&id=81809b58& ***!
  \**************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AudioPlayer_vue_vue_type_template_id_81809b58___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./AudioPlayer.vue?vue&type=template&id=81809b58& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/siswa/AudioPlayer.vue?vue&type=template&id=81809b58&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AudioPlayer_vue_vue_type_template_id_81809b58___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AudioPlayer_vue_vue_type_template_id_81809b58___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/pages/siswa/Kerjakan.vue":
/*!***********************************************!*\
  !*** ./resources/js/pages/siswa/Kerjakan.vue ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Kerjakan_vue_vue_type_template_id_1449404c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Kerjakan.vue?vue&type=template&id=1449404c& */ "./resources/js/pages/siswa/Kerjakan.vue?vue&type=template&id=1449404c&");
/* harmony import */ var _Kerjakan_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Kerjakan.vue?vue&type=script&lang=js& */ "./resources/js/pages/siswa/Kerjakan.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _Kerjakan_vue_vue_type_style_index_0_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Kerjakan.vue?vue&type=style&index=0&lang=css& */ "./resources/js/pages/siswa/Kerjakan.vue?vue&type=style&index=0&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _Kerjakan_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _Kerjakan_vue_vue_type_template_id_1449404c___WEBPACK_IMPORTED_MODULE_0__["render"],
  _Kerjakan_vue_vue_type_template_id_1449404c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/pages/siswa/Kerjakan.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/pages/siswa/Kerjakan.vue?vue&type=script&lang=js&":
/*!************************************************************************!*\
  !*** ./resources/js/pages/siswa/Kerjakan.vue?vue&type=script&lang=js& ***!
  \************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Kerjakan_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./Kerjakan.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/Kerjakan.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Kerjakan_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/pages/siswa/Kerjakan.vue?vue&type=style&index=0&lang=css&":
/*!********************************************************************************!*\
  !*** ./resources/js/pages/siswa/Kerjakan.vue?vue&type=style&index=0&lang=css& ***!
  \********************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Kerjakan_vue_vue_type_style_index_0_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader!../../../../node_modules/css-loader??ref--6-1!../../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../../node_modules/postcss-loader/src??ref--6-2!../../../../node_modules/vue-loader/lib??vue-loader-options!./Kerjakan.vue?vue&type=style&index=0&lang=css& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/Kerjakan.vue?vue&type=style&index=0&lang=css&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Kerjakan_vue_vue_type_style_index_0_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Kerjakan_vue_vue_type_style_index_0_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Kerjakan_vue_vue_type_style_index_0_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Kerjakan_vue_vue_type_style_index_0_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Kerjakan_vue_vue_type_style_index_0_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/pages/siswa/Kerjakan.vue?vue&type=template&id=1449404c&":
/*!******************************************************************************!*\
  !*** ./resources/js/pages/siswa/Kerjakan.vue?vue&type=template&id=1449404c& ***!
  \******************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Kerjakan_vue_vue_type_template_id_1449404c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./Kerjakan.vue?vue&type=template&id=1449404c& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/Kerjakan.vue?vue&type=template&id=1449404c&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Kerjakan_vue_vue_type_template_id_1449404c___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Kerjakan_vue_vue_type_template_id_1449404c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);