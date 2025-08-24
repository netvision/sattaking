<template>
  <div class="p-4 md:p-8">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">
          <i class="fas fa-trophy text-green-600 mr-2"></i>
          Manage Results
        </h1>
      </div>
    </div>
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-xl shadow flex items-center p-4 gap-4">
        <div class="bg-blue-100 text-blue-600 rounded-full p-3">
          <i class="fas fa-list-ol fa-lg"></i>
        </div>
        <div>
          <div class="text-2xl font-bold">{{ todayResults.length }}</div>
          <div class="text-gray-500 text-sm">Results Today</div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow flex items-center p-4 gap-4">
        <div class="bg-green-100 text-green-600 rounded-full p-3">
          <i class="fas fa-robot fa-lg"></i>
        </div>
        <div>
          <div class="text-2xl font-bold">{{ autoResults }}</div>
          <div class="text-gray-500 text-sm">Auto Results</div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow flex items-center p-4 gap-4">
        <div class="bg-yellow-100 text-yellow-600 rounded-full p-3">
          <i class="fas fa-lock fa-lg"></i>
        </div>
        <div>
          <div class="text-2xl font-bold">{{ lockedResults }}</div>
          <div class="text-gray-500 text-sm">Locked Results</div>
        </div>
      </div>
    </div>

    <!-- Slot Cards with Inline Result Entry -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
      <div v-for="slot in slotsStore.todaySlots" :key="slot.id" class="bg-white rounded-2xl shadow-lg p-6 flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-semibold text-gray-900 text-lg flex items-center gap-2">
              <i class="fas fa-ticket-alt text-orange-400"></i> {{ slot.title }}
            </h3>
            <div class="flex items-center gap-2 mt-2">
              <span class="inline-flex items-center px-2 py-1 rounded bg-orange-100 text-orange-700 font-semibold text-sm">
                <i class="fas fa-clock mr-1"></i> Scheduled: {{ formatTime(slot.scheduled_time) }}
              </span>
            </div>
          </div>
          <span v-if="slot.is_auto" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
            <i class="fas fa-robot mr-1"></i> Auto
          </span>
        </div>
        <form @submit.prevent="submitInlineResult(slot.id)" class="flex flex-col gap-2 mt-2">
          <div class="flex flex-row gap-2 items-center">
            <input
              v-model="inlineResults[slot.id]"
              type="number"
              min="0"
              max="99"
              required
              :disabled="isSlotLocked(slot)"
              class="rounded-lg border-2 border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 bg-white px-4 py-2 text-base text-gray-900 placeholder-gray-400 shadow transition-all duration-150 disabled:bg-gray-100 disabled:text-gray-400 flex-1"
              placeholder="Result (0-99)"
              style="text-align: center;"
            />
            <button
              type="submit"
              :class="[
                'rounded-lg text-white font-semibold px-4 py-2 flex items-center justify-center gap-2 transition border',
                isSlotLocked(slot)
                  ? 'bg-gray-400 border-gray-400 cursor-not-allowed'
                  : 'bg-blue-600 hover:bg-blue-700 border-blue-700'
              ]"
              :disabled="resultsStore.loading || isSlotLocked(slot)"
              :title="isSlotLocked(slot) ? 'Slot is locked. Scheduled time has passed.' : 'Set Result'"
              style="min-width: 90px; margin-left: auto;"
            >
              <i class="fas fa-trophy"></i>
              <span>Set</span>
            </button>
          </div>
          <div v-if="inlineErrors[slot.id]" class="text-red-500 text-xs mt-1">{{ inlineErrors[slot.id] }}</div>
        </form>
        <div v-if="todayResults.find(r => r.slot_id === slot.id)" class="mt-2 flex items-center gap-2">
          <span class="text-green-600 font-semibold text-sm">
            Result: {{ todayResults.find(r => r.slot_id === slot.id)?.result }}
          </span>
          <span v-if="todayResults.find(r => r.slot_id === slot.id)?.locked" class="ml-2 px-2 py-0.5 rounded bg-yellow-100 text-yellow-700 text-xs font-semibold flex items-center gap-1">
            <i class="fas fa-lock"></i> Locked
          </span>
          <button
            v-if="!todayResults.find(r => r.slot_id === slot.id)?.locked"
            @click="lockResult(todayResults.find(r => r.slot_id === slot.id))"
            class="ml-2 px-2 py-0.5 rounded bg-gray-200 hover:bg-yellow-200 text-yellow-800 text-xs font-semibold flex items-center gap-1 border border-yellow-300"
          >
            <i class="fas fa-lock"></i> Lock
          </button>
        </div>
      </div>
    </div>

    <!-- Monthly Results Table Section -->
    <div class="bg-white rounded-2xl shadow-lg overflow-x-auto p-0 md:p-4 mt-10">
      <div class="flex flex-wrap items-center gap-4 mb-4">
        <label class="text-sm font-medium text-gray-700 mr-2">Month:</label>
        <input
          v-model="monthlyTable.month"
          type="month"
          class="form-input w-auto"
          @change="fetchMonthlyResults"
        />
        <div class="ml-4 flex items-center gap-2">
          <label class="text-sm text-gray-600">Column colors:</label>
          <select v-model="columnColorMode" class="form-select text-sm">
            <option value="distinct">Distinct</option>
            <option value="uniform">Uniform</option>
          </select>
        </div>
      </div>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="sticky top-0 z-10">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 sticky left-0 z-20">Date</th>
            <th
              v-for="(slot, idx) in slotsStore.allSlots"
              :key="slot.id"
              class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider"
              :style="getThStyle(idx)"
            >
              {{ slot.title }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr
            v-for="(date, rowIdx) in monthlyTable.dates"
            :key="date"
            :class="[rowIdx % 2 === 0 ? 'bg-white' : 'bg-gray-50', 'border-b-2', 'border-gray-300', 'transition-colors', 'duration-150']"
            @mouseenter="hoveredRow = date"
            @mouseleave="hoveredRow = null"
          >
            <td class="px-4 py-3 font-semibold text-gray-700 sticky left-0 bg-white z-10 border-b-2 border-gray-300" :style="getTdStyle(0, date)">
              {{ formatTableDate(date) }}
            </td>
            <td
              v-for="(slot, idx) in slotsStore.allSlots"
              :key="slot.id"
              class="px-4 py-3 text-center transition-colors duration-150 border-b-2 border-gray-300"
              :style="getTdStyle(idx, date)"
            >
              <span v-if="monthlyTable.results[date] && monthlyTable.results[date][slot.id] !== undefined"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-full font-semibold"
                    :style="{ color: getTdTextColor(idx), backgroundColor: 'transparent' }">
                {{ monthlyTable.results[date][slot.id] }}
              </span>
              <span v-else class="text-gray-300">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Loading State -->
    <div v-if="resultsStore.loading" class="flex justify-center py-12">
      <div class="spinner w-12 h-12"></div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useResultsStore } from '@/stores/results'
import { useSlotsStore } from '@/stores/slots'
import { format } from 'date-fns'
import { useToast } from 'vue-toastification'

const resultsStore = useResultsStore()
const slotsStore = useSlotsStore()
const toast = useToast()

const filters = reactive({
  date: format(new Date(), 'yyyy-MM-dd'),
  month: format(new Date(), 'yyyy-MM'),
  type: '',
  locked: ''
})

const todayResults = computed(() => resultsStore.todayResults)
const autoResults = computed(() => todayResults.value.filter(r => r.is_auto).length)
const lockedResults = computed(() => todayResults.value.filter(r => r.locked).length)

const pendingSlots = computed(() => {
  return slotsStore.todaySlots.filter(slot => {
    return !todayResults.value.some(result => result.slot_id === slot.id)
  })
})

const inlineResults = reactive({})
const inlineErrors = reactive({})
const submitInlineResult = async (slotId) => {
  inlineErrors[slotId] = ''
  const value = inlineResults[slotId]
  if (value === undefined || value === null || value === '' || isNaN(value) || value < 0 || value > 99) {
    inlineErrors[slotId] = 'Please enter a valid result (0-99)';
    return;
  }
  const now = new Date();
  const pad = (n) => n.toString().padStart(2, '0');
  const declaredAt = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
  const payload = {
    slot_id: slotId,
    result: Number(value),
    declared_at: declaredAt
  }
  const res = await resultsStore.createResult(payload)
  if (res && res.success) {
    inlineResults[slotId] = ''
    await loadData()
  } else {
    inlineErrors[slotId] = res && res.message ? res.message : 'Failed to set result.'
  }
}

const availableSlots = computed(() => pendingSlots.value)

const filteredResults = computed(() => {
  let results = filters.date === format(new Date(), 'yyyy-MM-dd') 
    ? todayResults.value 
    : resultsStore.archiveResults

  if (filters.type === 'auto') {
    results = results.filter(r => r.is_auto)
  } else if (filters.type === 'manual') {
    results = results.filter(r => !r.is_auto)
  }

  if (filters.locked === '1') {
    results = results.filter(r => r.locked)
  } else if (filters.locked === '0') {
    results = results.filter(r => !r.locked)
  }

  return results
})

const formatDate = (datetime) => {
  if (!datetime) return '-';
  const date = new Date(datetime);
  if (isNaN(date.getTime())) return '-';
  return format(date, 'MMM dd, yyyy');
}

const formatTime = (datetime) => {
  if (!datetime) return '-';
  if (datetime instanceof Date && !isNaN(datetime.getTime())) {
    return format(datetime, 'HH:mm');
  }
  if (/^\d{2}:\d{2}(:\d{2})?$/.test(datetime)) {
    const [h, m, s] = datetime.split(':');
    const now = new Date();
    const date = new Date(now.getFullYear(), now.getMonth(), now.getDate(), Number(h), Number(m), Number(s || 0));
    return format(date, 'HH:mm');
  }
  const date = new Date(datetime);
  if (isNaN(date.getTime())) return '-';
  return format(date, 'HH:mm');
}

const applyFilters = async () => {
  if (filters.date === format(new Date(), 'yyyy-MM-dd')) {
    await resultsStore.fetchTodayResults()
  } else {
    await resultsStore.fetchArchiveResults(filters.date)
  }
}

const resetFilters = async () => {
  const now = new Date();
  filters.date = format(now, 'yyyy-MM-dd');
  filters.month = format(now, 'yyyy-MM');
  filters.type = '';
  filters.locked = '';
  await resultsStore.fetchTodayResults();
}

const lockResult = async (result) => {
  if (confirm(`Are you sure you want to lock the result for "${result.slot.title}"? This action cannot be undone.`)) {
    await resultsStore.lockResult(result.id)
    await loadData()
  }
}

const loadData = async () => {
  await Promise.all([
    resultsStore.fetchTodayResults(),
    slotsStore.fetchTodaySlots(),
    slotsStore.fetchAllSlots()
  ])
}

function prefillAllInlineResults() {
  slotsStore.todaySlots.forEach(slot => {
    const result = resultsStore.getResultBySlotId(slot.id);
    if (result && (inlineResults[slot.id] === undefined || inlineResults[slot.id] === null || inlineResults[slot.id] === '')) {
      inlineResults[slot.id] = result.result;
    }
  });
}

onMounted(() => {
  loadData().then(() => prefillAllInlineResults());
});
watch([
  () => resultsStore.todayResults,
  () => slotsStore.todaySlots
], prefillAllInlineResults, { deep: true });

function isSlotLocked(slot) {
  if (!slot || !slot.scheduled_time) return false;
  const [h, m, s] = slot.scheduled_time.split(':');
  const now = new Date();
  const slotDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), Number(h), Number(m), Number(s || 0));
  return now > slotDate;
}

// --- Monthly Results Table State & Logic ---
const monthlyTable = reactive({
  month: format(new Date(), 'yyyy-MM'),
  dates: [],
  results: {}
});

async function fetchMonthlyResults() {
  if (!slotsStore.allSlots.length) {
    await slotsStore.fetchAllSlots();
  }
  const [year, month] = monthlyTable.month.split('-').map(Number);
  const daysInMonth = new Date(year, month, 0).getDate();
  monthlyTable.dates = Array.from({ length: daysInMonth }, (_, i) =>
    format(new Date(year, month - 1, i + 1), 'yyyy-MM-dd')
  );
  // Fetch all results for the month using the new API
  const res = await resultsStore.fetchMonthResults(monthlyTable.month);
  let results = [];
  if (res && res.success && Array.isArray(res.data)) {
    results = res.data;
  }
  console.log('Monthly month API response:', res);
  console.log('Results array for monthly table:', results);
  monthlyTable.results = {};
  results.forEach(r => {
    const d = format(new Date(r.declared_at), 'yyyy-MM-dd');
    if (!monthlyTable.results[d]) monthlyTable.results[d] = {};
    monthlyTable.results[d][r.slot_id] = r.result;
  });
}

onMounted(fetchMonthlyResults);
watch(
  () => monthlyTable.month,
  fetchMonthlyResults
);

// --- Monthly Table Helpers: date formatting and column colors ---
function formatTableDate(dateStr) {
  try {
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return format(d, 'EEE, MMM d');
  } catch (_) {
    return dateStr;
  }
}

const columnPalette = [
  { th: 'from-blue-50 to-blue-100', td: 'bg-blue-50', text: 'text-blue-700' },
  { th: 'from-emerald-50 to-emerald-100', td: 'bg-emerald-50', text: 'text-emerald-700' },
  { th: 'from-amber-50 to-amber-100', td: 'bg-amber-50', text: 'text-amber-700' },
  { th: 'from-violet-50 to-violet-100', td: 'bg-violet-50', text: 'text-violet-700' },
  { th: 'from-rose-50 to-rose-100', td: 'bg-rose-50', text: 'text-rose-700' },
  { th: 'from-teal-50 to-teal-100', td: 'bg-teal-50', text: 'text-teal-700' },
  { th: 'from-indigo-50 to-indigo-100', td: 'bg-indigo-50', text: 'text-indigo-700' },
  { th: 'from-lime-50 to-lime-100', td: 'bg-lime-50', text: 'text-lime-700' },
];

function getColThClass(idx) {
  const p = columnPalette[idx % columnPalette.length];
  return `${p.th} ${p.text}`;
}

function getColTdClass(idx) {
  const p = columnPalette[idx % columnPalette.length];
  return `${p.td}`;
}

function getColTextClass(idx) {
  const p = columnPalette[idx % columnPalette.length];
  return `${p.text}`;
}

// Color mode: 'distinct' = each column different, 'uniform' = all the same color (first palette)
const columnColorMode = ref('distinct')

// Track hovered row (date string)
const hoveredRow = ref(null)

// Palette expressed in hex gradients & solid colors for consistent inline styling
const columnHex = [
  { start: '#ebf8ff', end: '#e0f2fe', text: '#1e3a8a', bg: '#ebf8ff' }, // blue
  { start: '#ecfdf5', end: '#d1fae5', text: '#065f46', bg: '#ecfdf5' }, // emerald
  { start: '#fff7ed', end: '#fff3d8', text: '#92400e', bg: '#fff7ed' }, // amber
  { start: '#faf5ff', end: '#f3e8ff', text: '#6b21a8', bg: '#faf5ff' }, // violet
  { start: '#fff1f2', end: '#ffe4e6', text: '#be123c', bg: '#fff1f2' }, // rose
  { start: '#ecfeff', end: '#cffafe', text: '#065f46', bg: '#ecfeff' }, // teal-like
  { start: '#eef2ff', end: '#e0e7ff', text: '#3730a3', bg: '#eef2ff' }, // indigo
  { start: '#f7fee7', end: '#f1f5d6', text: '#4d7c0f', bg: '#f7fee7' }, // lime
]

function getThStyle(idx) {
  const mode = columnColorMode.value;
  const p = columnHex[idx % columnHex.length];
  const refP = columnHex[0];
  const use = mode === 'uniform' ? refP : p;
  return {
    background: `linear-gradient(180deg, ${use.start}, ${use.end})`,
    color: use.text,
  }
}

function getTdStyleInner(idx, date) {
  const mode = columnColorMode.value;
  const p = columnHex[idx % columnHex.length];
  const refP = columnHex[0];
  const use = mode === 'uniform' ? refP : p;
  const bg = (date && hoveredRow.value === date) ? darkenColor(use.bg, 0.06) : use.bg;
  return {
    backgroundColor: bg,
  }
}

function getTdStyle(idx, date) {
  return getTdStyleInner(idx, date)
}

function darkenColor(hex, amount = 0.05) {
  // simple hex darken
  const c = hex.replace('#','');
  const num = parseInt(c,16);
  let r = (num >> 16) & 0xFF;
  let g = (num >> 8) & 0xFF;
  let b = num & 0xFF;
  r = Math.max(0, Math.floor(r * (1 - amount)));
  g = Math.max(0, Math.floor(g * (1 - amount)));
  b = Math.max(0, Math.floor(b * (1 - amount)));
  return `#${((1<<24) + (r<<16) + (g<<8) + b).toString(16).slice(1)}`;
}



function getTdTextColorInner(idx, date) {
  const mode = columnColorMode.value;
  const p = columnHex[idx % columnHex.length];
  const refP = columnHex[0];
  const use = mode === 'uniform' ? refP : p;
  if (date && hoveredRow.value === date) {
    return '#111827'; // dark gray for better contrast on hover
  }
  return use.text;
}

function getTdTextColor(idx, date) {
  return getTdTextColorInner(idx, date)
}
</script>

