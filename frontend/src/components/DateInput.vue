<!--
  DateInput.vue — calendar date picker (rd-stock style)
  Props: v-model (String 'YYYY-MM-DD'), placeholder, min, max, disabled, firstDayOfWeek (0=Sun, 1=Mon)
-->
<template>
  <div class="dp-wrap" ref="wrapEl">

    <!-- ── Trigger field ── -->
    <div
      class="dp-field"
      :class="{ 'dp-field--open': isOpen, 'dp-field--disabled': disabled }"
      @click="!disabled && toggle()"
    >
      <svg class="dp-cal-icon" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0
          002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0
          000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
      </svg>
      <span class="dp-display" :class="{ 'dp-placeholder': !modelValue }">
        {{ displayValue || placeholder }}
      </span>
      <button v-if="modelValue && !disabled" class="dp-clear-x" @click.stop="clear" title="Clear">
        <svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0
            111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293
            4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
    </div>

    <!-- ── Calendar popup ── -->
    <Transition name="dp-pop">
      <div v-if="isOpen" class="dp-popup" @click.stop>

        <!-- Month / Year header -->
        <div class="dp-header">
          <button class="dp-nav" @click="shiftMonth(-1)" title="Previous month">‹</button>
          <div class="dp-header-mid">
            <button class="dp-hdr-btn" @click="mode = mode === 'month' ? 'day' : 'month'">
              {{ MONTHS[curMonth] }}
            </button>
            <button class="dp-hdr-btn" @click="mode = mode === 'year' ? 'day' : 'year'">
              {{ curYear }}
            </button>
          </div>
          <button class="dp-nav" @click="shiftMonth(1)" title="Next month">›</button>
        </div>

        <!-- Month picker -->
        <div v-if="mode === 'month'" class="dp-month-grid">
          <button
            v-for="(m, i) in MONTHS" :key="i"
            class="dp-mpick"
            :class="{ 'dp-mpick--active': i === curMonth }"
            @click="curMonth = i; mode = 'day'"
          >{{ m.slice(0, 3) }}</button>
        </div>

        <!-- Year picker -->
        <div v-if="mode === 'year'" class="dp-year-list">
          <button
            v-for="y in yearRange" :key="y"
            class="dp-ypick"
            :class="{ 'dp-ypick--active': y === curYear }"
            :ref="el => { if (y === curYear && el) el.scrollIntoView({ block: 'center' }) }"
            @click="curYear = y; mode = 'day'"
          >{{ y }}</button>
        </div>

        <!-- Day grid -->
        <template v-if="mode === 'day'">
          <div class="dp-weekdays">
            <span v-for="d in weekdayLabels" :key="d">{{ d }}</span>
          </div>
          <div class="dp-days">
            <button
              v-for="(cell, i) in cells" :key="i"
              class="dp-day"
              :class="{
                'dp-day--other':    cell.other,
                'dp-day--today':    cell.isToday,
                'dp-day--selected': cell.isSelected,
                'dp-day--disabled': cell.isDisabled,
              }"
              :disabled="cell.isDisabled"
              @click="pick(cell)"
            >{{ cell.d }}</button>
          </div>
        </template>

        <!-- Footer -->
        <div class="dp-footer">
          <button class="dp-footer-btn dp-today-btn" @click="pickToday">Today</button>
          <button class="dp-footer-btn dp-clear-btn" @click="clear">Clear</button>
        </div>

      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue:     { type: String,  default: '' },
  placeholder:    { type: String,  default: 'dd/mm/yyyy' },
  min:            { type: String,  default: '' },
  max:            { type: String,  default: '' },
  disabled:       { type: Boolean, default: false },
  firstDayOfWeek: { type: Number,  default: 1 },  // 1 = Monday
})
const emit = defineEmits(['update:modelValue'])

const MONTHS = ['January','February','March','April','May','June',
                'July','August','September','October','November','December']
const ALL_DAYS = ['Su','Mo','Tu','We','Th','Fr','Sa']

const weekdayLabels = computed(() =>
  [...ALL_DAYS.slice(props.firstDayOfWeek), ...ALL_DAYS.slice(0, props.firstDayOfWeek)]
)

const wrapEl  = ref(null)
const isOpen  = ref(false)
const mode    = ref('day')

const _now = new Date(); _now.setHours(0,0,0,0)
const todayTs = _now.getTime()

const curMonth = ref(_now.getMonth())
const curYear  = ref(_now.getFullYear())

const parseISO = str => {
  if (!str) return null
  const [y, m, d] = str.split('-').map(Number)
  const dt = new Date(y, m - 1, d); dt.setHours(0,0,0,0)
  return dt
}

const toISO = dt =>
  `${dt.getFullYear()}-${String(dt.getMonth()+1).padStart(2,'0')}-${String(dt.getDate()).padStart(2,'0')}`

const displayValue = computed(() => {
  const d = parseISO(props.modelValue)
  if (!d) return ''
  return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`
})

watch(() => props.modelValue, val => {
  const d = parseISO(val)
  if (d) { curMonth.value = d.getMonth(); curYear.value = d.getFullYear() }
}, { immediate: true })

const yearRange = computed(() => {
  const base = curYear.value
  return Array.from({ length: 21 }, (_, i) => base - 10 + i)
})

const cells = computed(() => {
  const y = curYear.value, m = curMonth.value
  const firstWd  = new Date(y, m, 1).getDay()
  const daysInM  = new Date(y, m + 1, 0).getDate()
  const prevDays = new Date(y, m, 0).getDate()
  const minDt    = parseISO(props.min)
  const maxDt    = parseISO(props.max)
  const selDt    = parseISO(props.modelValue)
  const selTs    = selDt ? selDt.getTime() : null
  const offset   = (firstWd - props.firstDayOfWeek + 7) % 7
  const list     = []

  for (let i = offset - 1; i >= 0; i--) {
    const d = prevDays - i
    const dt = new Date(y, m - 1, d); dt.setHours(0,0,0,0)
    list.push({ d, other: true, dt, isToday: false, isSelected: false, isDisabled: _isDisabled(dt, minDt, maxDt) })
  }
  for (let d = 1; d <= daysInM; d++) {
    const dt = new Date(y, m, d); dt.setHours(0,0,0,0)
    const ts = dt.getTime()
    list.push({ d, other: false, dt, isToday: ts === todayTs, isSelected: ts === selTs, isDisabled: _isDisabled(dt, minDt, maxDt) })
  }
  let nd = 1
  while (list.length < 42) {
    const dt = new Date(y, m + 1, nd++); dt.setHours(0,0,0,0)
    list.push({ d: nd - 1, other: true, dt, isToday: false, isSelected: false, isDisabled: _isDisabled(dt, minDt, maxDt) })
  }
  return list
})

function _isDisabled(dt, minDt, maxDt) {
  if (minDt && dt < minDt) return true
  if (maxDt && dt > maxDt) return true
  return false
}

function toggle() {
  mode.value = 'day'
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    const d = parseISO(props.modelValue)
    if (d) { curMonth.value = d.getMonth(); curYear.value = d.getFullYear() }
  }
}

function pick(cell) {
  if (cell.isDisabled) return
  if (cell.other) { curMonth.value = cell.dt.getMonth(); curYear.value = cell.dt.getFullYear() }
  emit('update:modelValue', toISO(cell.dt))
  isOpen.value = false
}

function pickToday() {
  const now = new Date(); now.setHours(0,0,0,0)
  curMonth.value = now.getMonth(); curYear.value = now.getFullYear()
  emit('update:modelValue', toISO(now))
  isOpen.value = false
}

function clear() {
  emit('update:modelValue', '')
  isOpen.value = false
}

function shiftMonth(delta) {
  let m = curMonth.value + delta, y = curYear.value
  if (m < 0)  { m = 11; y-- }
  if (m > 11) { m = 0;  y++ }
  curMonth.value = m; curYear.value = y
}

function onOutsideClick(e) {
  if (wrapEl.value && !wrapEl.value.contains(e.target)) {
    isOpen.value = false
    mode.value = 'day'
  }
}
onMounted(()       => document.addEventListener('mousedown', onOutsideClick))
onBeforeUnmount(() => document.removeEventListener('mousedown', onOutsideClick))
</script>

<style scoped>
/* ── Design tokens (rd-stock variables with fallbacks) ── */
.dp-wrap {
  --dp-accent:  var(--c-teal,   #00e5a0);
  --dp-surface: var(--surface,  #ffffff);
  --dp-text:    var(--c-dark,   #111827);
  --dp-muted:   var(--c-muted,  #718096);
  --dp-bg:      var(--bg,       #f4f6f9);
  --dp-border:  #1e2340;
  --dp-font:    var(--font, 'Noto Sans Thai', system-ui, sans-serif);
  --dp-r:       6px;
  --dp-shadow:  0 8px 24px rgba(0,0,0,.14);
  position: relative;
  display: inline-block;
  font-family: var(--dp-font);
}

/* ── Trigger field ── */
.dp-field {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--dp-surface);
  border: 1px solid var(--dp-border);
  border-radius: var(--dp-r);
  padding: 7px 10px;
  cursor: pointer;
  user-select: none;
  min-width: 148px;
  transition: border-color .15s, box-shadow .15s;
}
.dp-field:hover { border-color: var(--dp-accent); }
.dp-field--open {
  border-color: var(--dp-accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--dp-accent) 18%, transparent);
}
.dp-field--disabled { opacity: .5; cursor: not-allowed; pointer-events: none; }
.dp-cal-icon  { width: 14px; height: 14px; color: var(--dp-muted); flex-shrink: 0; }
.dp-display   { flex: 1; font-size: 13px; color: var(--dp-text); white-space: nowrap; }
.dp-placeholder { color: var(--dp-muted); }
.dp-clear-x {
  background: none; border: none; cursor: pointer; padding: 2px;
  color: var(--dp-muted); border-radius: 3px; display: grid; place-items: center;
  transition: color .1s, background .1s;
}
.dp-clear-x:hover { color: #ef4444; background: rgba(239,68,68,.08); }

/* ── Popup ── */
.dp-popup {
  position: absolute;
  top: calc(100% + 5px);
  left: 0;
  z-index: 9999;
  background: var(--dp-surface);
  border: 1px solid var(--dp-border);
  border-radius: var(--dp-r);
  box-shadow: var(--dp-shadow);
  width: 272px;
  padding: 12px;
  box-sizing: border-box;
}

.dp-pop-enter-active, .dp-pop-leave-active { transition: opacity .14s, transform .14s; }
.dp-pop-enter-from, .dp-pop-leave-to       { opacity: 0; transform: translateY(-5px); }

/* ── Header ── */
.dp-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}
.dp-nav {
  background: none;
  border: 1px solid var(--dp-border);
  color: var(--dp-muted);
  width: 26px; height: 26px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 17px;
  line-height: 1;
  display: grid; place-items: center;
  transition: all .12s;
}
.dp-nav:hover { background: rgba(0,229,160,.08); color: var(--dp-accent); border-color: rgba(0,229,160,.3); }
.dp-header-mid { display: flex; gap: 4px; }
.dp-hdr-btn {
  background: none;
  border: 1px solid transparent;
  border-radius: 5px;
  padding: 4px 8px;
  font-size: 13px;
  font-weight: 700;
  color: var(--dp-text);
  cursor: pointer;
  font-family: var(--dp-font);
  transition: all .12s;
}
.dp-hdr-btn:hover { background: var(--dp-bg); border-color: var(--dp-border); }

/* ── Weekday row ── */
.dp-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  margin-bottom: 3px;
}
.dp-weekdays span {
  text-align: center;
  font-size: 11px;
  font-weight: 700;
  color: var(--dp-muted);
  letter-spacing: .5px;
  padding: 3px 0;
  text-transform: uppercase;
}

/* ── Day cells ── */
.dp-days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
}
.dp-day {
  background: none;
  border: 1px solid transparent;
  border-radius: 5px;
  font-size: 12px;
  color: var(--dp-text);
  cursor: pointer;
  padding: 5px 0;
  text-align: center;
  font-family: var(--dp-font);
  transition: all .1s;
}
.dp-day:hover:not(:disabled)       { background: rgba(0,229,160,.08); border-color: rgba(0,229,160,.3); color: var(--dp-accent); }
.dp-day--other                     { color: var(--dp-muted); opacity: .4; }
.dp-day--today                     { border-color: var(--dp-accent); color: var(--dp-accent); font-weight: 700; }
.dp-day--selected                  { background: var(--dp-accent) !important; border-color: var(--dp-accent) !important; color: #fff !important; font-weight: 700; }
.dp-day--disabled                  { color: #ccc; cursor: not-allowed; opacity: .35; }
.dp-day--disabled:hover            { background: none; border-color: transparent; }

/* ── Month picker ── */
.dp-month-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 4px;
  padding: 4px 0;
}
.dp-mpick {
  background: none;
  border: 1px solid var(--dp-border);
  border-radius: 5px;
  padding: 7px 4px;
  font-size: 12px;
  color: var(--dp-text);
  cursor: pointer;
  font-family: var(--dp-font);
  transition: all .12s;
}
.dp-mpick:hover     { background: rgba(0,229,160,.08); border-color: rgba(0,229,160,.3); color: var(--dp-accent); }
.dp-mpick--active   { background: var(--dp-accent); border-color: var(--dp-accent); color: #fff; font-weight: 700; }

/* ── Year picker ── */
.dp-year-list {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 4px;
  max-height: 168px;
  overflow-y: auto;
  padding: 3px 0;
  scrollbar-width: thin;
}
.dp-ypick {
  background: none;
  border: 1px solid var(--dp-border);
  border-radius: 5px;
  padding: 6px 2px;
  font-size: 12px;
  color: var(--dp-text);
  cursor: pointer;
  font-family: var(--dp-font);
  transition: all .12s;
}
.dp-ypick:hover     { background: rgba(0,229,160,.08); border-color: rgba(0,229,160,.3); color: var(--dp-accent); }
.dp-ypick--active   { background: var(--dp-accent); border-color: var(--dp-accent); color: #fff; font-weight: 700; }

/* ── Footer ── */
.dp-footer {
  display: flex;
  gap: 6px;
  margin-top: 10px;
  padding-top: 9px;
  border-top: 1px solid var(--dp-border);
}
.dp-footer-btn {
  flex: 1;
  border: 1px solid;
  border-radius: 5px;
  padding: 5px 0;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  letter-spacing: .5px;
  font-family: var(--dp-font);
  transition: all .12s;
}
.dp-today-btn { background: rgba(0,229,160,.1); color: var(--dp-accent); border-color: rgba(0,229,160,.28); }
.dp-today-btn:hover { background: rgba(0,229,160,.2); }
.dp-clear-btn { background: none; color: var(--dp-muted); border-color: var(--dp-border); }
.dp-clear-btn:hover { background: rgba(239,68,68,.08); color: #ef4444; border-color: rgba(239,68,68,.3); }

@media print {
  .dp-popup  { display: none !important; }
  .dp-cal-icon, .dp-clear-x { display: none !important; }
  .dp-field {
    min-width: 0 !important;
    padding: 2px 2px !important;
    border: none !important;
    border-bottom: 1px solid #666 !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    width: auto !important;
  }
  .dp-display { font-size: 11px !important; }
}
</style>
