<template>
	<div id="audio" class="player-wrapper">
		<div class="player" v-if="show">
			<div class="player-controls">
				<div>
					<a v-on:click.prevent="playing = !playing" title="Play/Pause" href="#">
						<svg width="18px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
							<path v-if="!playing" fill="currentColor" d="M15,10.001c0,0.299-0.305,0.514-0.305,0.514l-8.561,5.303C5.51,16.227,5,15.924,5,15.149V4.852c0-0.777,0.51-1.078,1.135-0.67l8.561,5.305C14.695,9.487,15,9.702,15,10.001z"/>
							<path v-else fill="currentColor" d="M15,3h-2c-0.553,0-1,0.048-1,0.6v12.8c0,0.552,0.447,0.6,1,0.6h2c0.553,0,1-0.048,1-0.6V3.6C16,3.048,15.553,3,15,3z M7,3H5C4.447,3,4,3.048,4,3.6v12.8C4,16.952,4.447,17,5,17h2c0.553,0,1-0.048,1-0.6V3.6C8,3.048,7.553,3,7,3z"/>
						</svg>
					</a>
				</div>
				<div>
					<div class="player-progress" title="Time played : Total time">
						<div :style="{ width: this.percentComplete + '%' }" class="player-seeker"></div>
					</div>
					<div class="player-time">
						<div class="player-time-current">{{ this.currentSeconds | convertTimeHHMMSS }}</div>
						<div class="player-time-total">{{ this.durationSeconds | convertTimeHHMMSS }}</div>
					</div>
				</div>
			</div>
			<audio :loop="innerLoop" ref="audiofile" :src="file" preload="auto" style="display: none;"></audio>
		</div>
	</div>
</template>
<script>
export default {
	props: {
		file: {
			type: String,
			default: null
		},
		autoPlay: {
			type: Boolean,
			default: false
		},
		loop: {
			type: Boolean,
			default: false
		},
		show: {
			type: Boolean,
			default: true
		}
	},
	data: () => ({
		audio: undefined,
		currentSeconds: 0,
		durationSeconds: 0,
		innerLoop: false,
		loaded: false,
		playing: false,
		previousVolume: 35,
		showVolume: false,
		volume: 100
	}),
	computed: {
		percentComplete() {
			return parseInt(this.currentSeconds / this.durationSeconds * 100);
		},
		muted() {
			return this.volume / 100 === 0;
		}
	},
	filters: {
		convertTimeHHMMSS(val) {
			let hhmmss = new Date(val * 1000).toISOString().substr(11, 8);

			return hhmmss.indexOf("00:") === 0 ? hhmmss.substr(3) : hhmmss;
		}
	},
	watch: {
		playing(value) {
			if (value) { 
				return this.audio.play(); 
			}
			this.audio.pause();
		},
		volume(value) {
			this.showVolume = false;
			this.audio.volume = this.volume / 100;
		}
	},
	methods: {
		download() {
			this.stop();
			window.open(this.file, 'download');
		},
		load() {
			if (this.audio.readyState >= 2) {
				this.loaded = true;
				this.durationSeconds = parseInt(this.audio.duration);
				return this.playing = this.autoPlay;
			}

			throw new Error('Failed to load sound file.');
		},
		mute() {
			if (this.muted) {
				return this.volume = this.previousVolume;
			}

			this.previousVolume = this.volume;
			this.volume = 0;
		},
		seek(e) {
			if (!this.playing || e.target.tagName === 'SPAN') {
				return;
			}
			
			const el = e.target.getBoundingClientRect();
			const seekPos = (e.clientX - el.left) / el.width;

			this.audio.currentTime = parseInt(this.audio.duration * seekPos);
		},
		stop() {
			this.playing = false;
			this.audio.currentTime = 0;
		},
		update(e) {
			this.currentSeconds = parseInt(this.audio.currentTime);
		}
	},
	created() {
		this.innerLoop = this.loop;
	},
	mounted() {
		this.audio = this.$el.querySelectorAll('audio')[0];
		this.audio.addEventListener('timeupdate', this.update);
		this.audio.addEventListener('loadeddata', this.load);
		this.audio.addEventListener('pause', () => { this.playing = false; });
		this.audio.addEventListener('play', () => { this.playing = true; });
	}
}
</script>
<style lang="scss">
	$player-bg: #fff;
$player-border-color: darken($player-bg, 12%);
$player-link-color: darken($player-bg, 75%);
$player-progress-color: $player-border-color;
$player-seeker-color: $player-link-color;
$player-text-color: $player-link-color;

.player-wrapper {
	align-items: center;
	display: flex;
}

.player {
	background-color: $player-bg;
	border-radius: 5px;
	border: 1px solid $player-border-color;
	color: $player-text-color;
	display: inline-block;
	line-height: 1.5625;
}

.player-controls {
	display: flex;
	
	> div {
		border-right: 1px solid $player-border-color;
		
		&:last-child {
			border-right: none;
		}
		
		a {
			color: $player-link-color;
			display: block;
			line-height: 0;
			padding: 1em;
			text-decoration: none;
		}
	}
}

.player-progress {
	background-color: $player-progress-color;
	cursor: pointer;
	height: 50%;
	min-width: 200px;
	position: relative;
	
	.player-seeker {
		background-color: $player-seeker-color;
		bottom: 0;
		left: 0;
		position: absolute;
		top: 0;
	}
}

.player-time {
	display: flex;
	justify-content: space-between;

	.player-time-current {
		font-weight: 700;
		padding-left: 5px;
	}

	.player-time-total {
		opacity: 0.5;
		padding-right: 5px;
	}
}
</style>