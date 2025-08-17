<template>
  <div :class="wrapperClasses">
    <div class="magic-inner relative z-10">
      <!-- Header -->
  <div class="flex items-start justify-between">
        <div class="pr-2">
          <h4 class="text-lg font-semibold tracking-wide" :class="primary ? 'text-white drop-shadow' : 'text-gray-800'">
            {{ slot.title }}
          </h4>
          <p class="text-xs mt-1 flex items-center gap-1 font-medium"
             :class="primary ? 'text-white/80' : 'text-gray-500'">
            <i class="fas fa-clock" /> {{ formatTime(slot.scheduled_time) }}
            <span v-if="statusTag" :class="statusTag.class" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold ml-1">
              <i :class="statusTag.icon" /> {{ statusTag.label }}
            </span>
          </p>
        </div>
        <div class="flex flex-col items-end gap-2 relative">
          <span v-if="!timePassed && !showTodayResult" class="countdown-chip">
            <i class="fas fa-hourglass-start mr-1"></i>{{ countdownClock }}
          </span>
          <span v-if="!slot.is_active" class="badge badge-danger mt-1">Inactive</span>
        </div>
      </div>

  <!-- Removed separate awaiting box to keep consistent dimensions -->

      <!-- Results Grid -->
      <div class="mt-4 grid grid-cols-2 gap-4 items-stretch">
        <!-- Yesterday -->
        <div class="text-center rounded-md p-2"
             :class="primary ? 'bg-white/10 border border-white/20' : 'bg-gray-50 border border-gray-200'">
          <div class="text-[10px] tracking-wide uppercase font-semibold mb-1"
               :class="primary ? 'text-amber-200/80' : 'text-gray-500'">Yesterday</div>
          <div class="text-2xl font-extrabold tabular-nums"
               :class="primary ? 'text-white drop-shadow-sm' : 'text-gray-800'">
            {{ prevDisplay }}
          </div>
        </div>
        <!-- Today -->
        <div class="text-center rounded-md p-2"
             :class="primary ? 'bg-white/10 border border-white/20' : 'bg-gray-50 border border-gray-200'">
          <div class="text-[10px] tracking-wide uppercase font-semibold mb-1"
               :class="primary ? 'text-amber-200/80' : 'text-gray-500'">Today</div>
          <template v-if="showTodayResult">
            <div class="text-6xl md:text-7xl font-extrabold text-yellow-500 mb-2">{{ todayResult.result }}</div>
            <div class="text-[10px] font-medium text-gray-600">{{ formatTime(slot.scheduled_time) }}</div>
            <div v-if="trendIcon" class="mt-1 text-xs flex items-center gap-1" :class="trendClass">
              <i :class="trendIcon" /> {{ trendLabel }}
            </div>
          </template>
          <template v-else>
            <div class="text-6xl md:text-7xl text-gray-400 mb-2">
              <i v-if="timePassed" class="fas fa-spinner animate-spin"></i>
              <i v-else class="fas fa-clock"></i>
            </div>
            <div class="text-xs font-medium text-gray-600">{{ timePassed ? 'Awaiting' : 'Scheduled' }}</div>
          </template>
        </div>
      </div>

      <!-- Footer Links -->
      <div class="mt-4 flex justify-between items-center text-xs">
        <router-link :to="`/results/slot/${slot.id}`" class="font-medium flex items-center gap-1 hover:underline"
                     :class="primary ? 'text-white/90 hover:text-white' : 'text-blue-600'">
          <i class="fas fa-history" /> History
        </router-link>
        <div class="flex items-center gap-2">
          <span v-if="slot.is_active" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">
            <i class="fas fa-circle text-[6px]"></i> Active
          </span>
          <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-700">
            <i class="fas fa-circle text-[6px]"></i> Inactive
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { formatTime, formatDateTime } from '@/utils/dateTime'

export default {
  name: 'SlotCard',
  props: {
    slot: { type: Object, required: true },
    todayResult: { type: Object, default: null },
    prevResult: { type: [String, Number, null], default: null },
    primary: { type: Boolean, default: false }
  },
  setup(props) {
    const now = ref(new Date())
    let timer = null

    const scheduledDate = computed(() => {
      const d = new Date()
      const [h, m] = props.slot.scheduled_time.split(':')
      d.setHours(parseInt(h), parseInt(m), 0, 0)
      return d
    })

    const timePassed = computed(() => now.value >= scheduledDate.value)

    const totalLeadMs = computed(() => scheduledDate.value.getTime() - startOfDayMs())
    const remainingMs = computed(() => scheduledDate.value.getTime() - now.value.getTime())

    const progressPercent = computed(() => {
      // progress from start of day to scheduled time
      const elapsed = now.value.getTime() - startOfDayMs()
      if (totalLeadMs.value <= 0) return 100
      return Math.min(100, Math.max(0, (elapsed / totalLeadMs.value) * 100))
    })

    function startOfDayMs() {
      const d = new Date()
      d.setHours(0,0,0,0)
      return d.getTime()
    }

    const countdownLabel = computed(() => {
      if (timePassed.value) return '0m'
      const ms = remainingMs.value
      const mins = Math.max(0, Math.round(ms / 60000))
      if (mins >= 60) {
        const h = Math.floor(mins / 60)
        const m = mins % 60
        return `${h}h ${m}m`
      }
      return `${mins}m`
    })

    const countdownClock = computed(() => {
      if (timePassed.value) return '00:00'
      let totalSec = Math.max(0, Math.floor(remainingMs.value / 1000))
      const h = Math.floor(totalSec / 3600)
      totalSec %= 3600
      const m = Math.floor(totalSec / 60)
      const s = totalSec % 60
      const pad = (n) => n.toString().padStart(2,'0')
      return h > 0 ? `${pad(h)}:${pad(m)}:${pad(s)}` : `${pad(m)}:${pad(s)}`
    })

    const prevDisplay = computed(() => props.prevResult ?? '--')

    // Trend (compares today vs yesterday if both numeric)
    const trendIcon = computed(() => {
      if (!props.todayResult || props.prevResult == null || props.prevResult === '--') return null
      const today = parseInt(props.todayResult.result)
      const prev = parseInt(props.prevResult)
      if (isNaN(today) || isNaN(prev)) return null
      if (today > prev) return 'fas fa-arrow-up'
      if (today < prev) return 'fas fa-arrow-down'
      return 'fas fa-equals'
    })
    const trendLabel = computed(() => {
      if (!trendIcon.value) return ''
      if (trendIcon.value.includes('arrow-up')) return 'Higher'
      if (trendIcon.value.includes('arrow-down')) return 'Lower'
      return 'Same'
    })
    const trendClass = computed(() => {
      if (!trendIcon.value) return ''
      if (trendIcon.value.includes('arrow-up')) return 'text-emerald-600'
      if (trendIcon.value.includes('arrow-down')) return 'text-red-600'
      return 'text-gray-500'
    })

    const showTodayResult = computed(() => !!props.todayResult && timePassed.value)

    const statusTag = computed(() => {
      if (showTodayResult.value) return { label: 'Declared', class: 'bg-emerald-500/20 text-emerald-700', icon: 'fas fa-check-circle' }
      if (timePassed.value && !props.todayResult) return { label: 'Pending', class: 'bg-orange-500/20 text-orange-700', icon: 'fas fa-hourglass-half' }
      return { label: 'Upcoming', class: 'bg-blue-500/20 text-blue-700', icon: 'fas fa-clock' }
    })

    const wrapperClasses = computed(() => {
      const base = 'relative rounded-xl overflow-hidden transition-all duration-500 group'
      if (props.primary) {
        return base + ' magic-primary-card'
      }
      return base + ' bg-white shadow-md hover:shadow-xl border border-gray-200 hover:border-blue-400/60'
    })

    const shortTime = (dt) => {
      try {
        return formatTime(dt)
      } catch (e) {
        return dt
      }
    }

    onMounted(() => {
  timer = setInterval(() => { now.value = new Date() }, 1000) // update every second for accurate countdown
    })
    onBeforeUnmount(() => timer && clearInterval(timer))

    return {
      formatTime,
      formatDateTime,
      timePassed,
  countdownLabel,
  countdownClock,
      prevDisplay,
      trendIcon,
      trendLabel,
      trendClass,
      statusTag,
      wrapperClasses,
  shortTime,
  showTodayResult
    }
  }
}
</script>

<style scoped>
.magic-primary-card {
  background: radial-gradient(circle at 20% 15%, rgba(255,255,255,0.15), transparent 60%),
              linear-gradient(135deg,#f59e0b 0%,#ec4899 55%,#6366f1 110%);
  box-shadow: 0 10px 25px -5px rgba(99,102,241,0.35), 0 4px 8px -2px rgba(99,102,241,0.25);
  border: 1px solid rgba(255,255,255,0.25);
}
.magic-primary-card:before, .magic-primary-card:after {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  border-radius: inherit;
}
.magic-primary-card:before {
  background: linear-gradient(120deg, rgba(255,255,255,0.4), transparent 40%, transparent 60%, rgba(255,255,255,0.35));
  opacity: .25;
}
.magic-primary-card:after {
  background: radial-gradient(circle at 80% 120%, rgba(255,255,255,0.18), transparent 70%);
  mix-blend-mode: overlay;
}
.magic-primary-card:hover {
  transform: translateY(-4px) rotateX(3deg) scale(1.01);
}
.magic-inner { padding: 1rem 1.1rem 1.05rem; }

.result-bubble {
  --bubble-width: 5.9rem;
  --bubble-height: 4.1rem;
  width: var(--bubble-width);
  height: var(--bubble-height);
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  letter-spacing: .05em;
  line-height: 1;
  position: relative;
  user-select: none;
  transition: transform .32s cubic-bezier(.34,1.56,.38,1), box-shadow .32s ease;
  box-shadow: 0 4px 12px -4px rgba(0,0,0,.28), 0 1px 4px -1px rgba(0,0,0,.25);
}
/* New cooler palette aligned with site hero */
.bubble-primary { background: linear-gradient(135deg,#6366f1 0%,#8b5cf6 55%,#ec4899 105%); }
.bubble-secondary { background: #fbbf24; /* amber-400 flat */ }
/* Muted placeholders (desaturated / darker) */
.bubble-primary-muted { background: linear-gradient(135deg,#4338ca 0%,#6d28d9 90%); filter: saturate(75%) brightness(.85); }
.bubble-secondary-muted { background: linear-gradient(135deg,#1e3a8a 0%,#4338ca 90%); filter: saturate(75%) brightness(.85); }
.result-bubble.placeholder { box-shadow: inset 0 0 0 1px rgba(255,255,255,0.12), 0 0 0 1px rgba(0,0,0,0.08); }
.result-bubble .result-value { font-size: 1.95rem; color:#fff; text-shadow: 0 1px 3px rgba(0,0,0,.35); }
.result-bubble.declared-glow { animation: subtlePulse 7s ease-in-out infinite; }
.result-bubble.declared-glow:hover { transform: translateY(-3px) scale(1.025); }
.result-bubble.declared-glow:active { transform: translateY(0) scale(.97); }
@media (max-width:640px){ .result-bubble { --bubble-width:5.2rem; --bubble-height:3.6rem; } .result-bubble .result-value { font-size:1.7rem; } }
@keyframes subtlePulse { 0%,100% { box-shadow:0 4px 12px -4px rgba(0,0,0,.28); } 50% { box-shadow:0 6px 16px -4px rgba(0,0,0,.32); } }

.declared-glow {
  animation: pulseGlow 2.4s ease-in-out infinite;
  position: relative;
}
.declared-glow:after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 9999px;
  box-shadow: 0 0 8px 3px rgba(16,185,129,0.45), 0 0 18px 8px rgba(16,185,129,0.15);
  opacity: 0.7;
  mix-blend-mode: screen;
}
@keyframes pulseGlow { 0%,100% { transform: scale(1); opacity:1;} 50% { transform: scale(1.05); opacity:.95;} }

.shimmer-box { position: relative; overflow: hidden; }
.shimmer-box:after { content:""; position:absolute; inset:0; background:linear-gradient(110deg,transparent 0%,rgba(255,255,255,.4) 45%,rgba(255,255,255,.7) 50%,rgba(255,255,255,.4) 55%,transparent 100%); animation: shimmer 2.2s linear infinite; }

@keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
@keyframes spin-slow { from { transform: rotate(0deg);} to { transform: rotate(360deg);} }
.animate-spin-slow { animation: spin-slow 3s linear infinite; }

@keyframes progressFlow { 0% { filter: hue-rotate(0deg);} 100% { filter: hue-rotate(360deg);} }
.animate-progress { animation: progressFlow 6s linear infinite; }

.countdown-chip { @apply inline-flex items-center bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[11px] font-semibold px-2 py-1 rounded-md shadow; }
</style>
