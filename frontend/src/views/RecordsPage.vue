<template>
  <div>
    <div class="page-header">
      <div class="header-left">
        <router-link to="/" class="back-btn">← หน้าหลัก</router-link>
        <h1><span class="sl">//</span> ข้อมูลทั้งหมด</h1>
      </div>
      <div class="header-stats">
        <span class="stat-chip chip-orange">QC: {{ qualityCheck.length }} รายการ</span>
        <span class="stat-chip chip-green">Dissolution: {{ dissolution.length }} รายการ</span>
      </div>
    </div>

    <!-- Filter bar -->
    <div class="filter-bar">
      <div class="tab-group">
        <button
          v-for="tab in tabs" :key="tab.key"
          class="tab-btn" :class="{ active: activeTab === tab.key, [tab.cls]: true }"
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
          <span class="tab-count">{{ tab.count }}</span>
        </button>
      </div>
      <div class="filter-inputs">
        <input v-model="filterName"   type="text" class="filter-input" placeholder="ค้นหาชื่อ..." />
        <input v-model="filterNumber" type="text" class="filter-input" placeholder="เลขที่ใบส่งวิเคราะห์..." />
      </div>
      <div class="sort-group">
        <button class="sort-btn" :class="{ 'sort-active': sortField === 'send_date' }" @click="toggleSort('send_date')">
          วันที่ส่ง <span class="sort-arrow">{{ sortField === 'send_date' ? (sortDir === 'asc' ? '▲' : '▼') : '↕' }}</span>
        </button>
        <button class="sort-btn" :class="{ 'sort-active': sortField === 'urgency' }" @click="toggleSort('urgency')">
          ความเร่งด่วน <span class="sort-arrow">{{ sortField === 'urgency' ? (sortDir === 'asc' ? '▲' : '▼') : '↕' }}</span>
        </button>
      </div>
    </div>

    <!-- Loading / empty -->
    <div v-if="loading" class="state-msg">กำลังโหลด...</div>
    <div v-else-if="filtered.length === 0" class="state-msg">ไม่พบข้อมูล</div>

    <!-- Table -->
    <div v-else class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:50px">ID</th>
            <th style="width:120px">ประเภท</th>
            <th style="width:140px">เลขที่ใบวิเคราะห์</th>
            <th>ชื่อผลิตภัณฑ์</th>
            <th style="width:100px">Lot No.</th>
            <th style="width:120px">วันที่ส่ง</th>
            <th style="width:160px">หัวข้อวิเคราะห์</th>
            <th style="width:92px">ความเร่งด่วน</th>
            <th style="width:130px">สถานะ</th>
            <th style="width:120px">ผลวิเคราะห์</th>
            <th style="width:140px">ผู้ส่ง / สังเกตโดย</th>
            <th style="width:120px">วันที่สร้าง</th>
            <th style="width:130px">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in filtered" :key="r._type + r.id">
            <td class="td-id">#{{ r.id }}</td>
            <td>
              <span v-if="r._type === 'qc'" class="type-badge badge-orange">QC-005</span>
              <span v-else class="type-badge badge-green">Dis-006</span>
            </td>
            <td class="td-mono">{{ r.analysis_number || '-' }}</td>
            <td class="td-name">
              {{ r.product_name || '-' }}
              <span
                v-if="r._type === 'dissolution' && r.our_products_count > 1"
                class="multi-badge"
              >+{{ r.our_products_count - 1 }}</span>
            </td>
            <td class="td-mono">{{ r.lot_no || '-' }}</td>
            <td>{{ formatDate(r.send_date) }}</td>
            <td class="td-params">
              <template v-if="r._type === 'qc'">{{ getActiveParams(r) }}</template>
              <span v-else class="td-na">-</span>
            </td>
            <td>
              <span v-if="r._type === 'qc' && r.urgency_level" :class="['urgency-badge', urgencyBadgeClass(r.urgency_level)]">{{ urgencyLabel(r.urgency_level) }}</span>
              <span v-else class="td-na">-</span>
            </td>
            <td>
              <span :class="['track-badge', trackingClass(r)]">{{ trackingLabel(r) }}</span>
            </td>
            <td>
              <span v-if="r._type === 'qc' && r.result === 'pass'" class="result-badge badge-pass">ผ่าน</span>
              <span v-else-if="r._type === 'qc' && r.result === 'fail'" class="result-badge badge-fail">ไม่ผ่าน</span>
              <span v-else-if="r._type === 'qc'" class="result-badge badge-none">-</span>
              <span v-else-if="r.f2_result" class="f2-val">F₂ = {{ r.f2_result }}</span>
              <span v-else>-</span>
            </td>
            <td>
              <template v-if="r._type === 'qc'">{{ r.requester || '-' }}</template>
              <template v-else>
                <div>{{ r.sender || '-' }}</div>
                <div v-if="r.observed_by" class="sub-text">สังเกต: {{ r.observed_by }}</div>
              </template>
            </td>
            <td>{{ formatDate(r.created_at) }}</td>
            <td class="td-actions">
              <button v-if="role === 'tester'" class="btn-sm btn-test" @click="router.push(r._type === 'qc' ? `/quality-check/${r.id}/test` : `/dissolution/${r.id}/test`)">ทดสอบ</button>
              <button v-else class="btn-sm btn-view" @click="openRecord(r)">เปิด</button>
              <button
                v-if="r._type === 'qc' && r.status === 'in_progress' && role !== 'tester'"
                class="btn-sm btn-lock"
                @click="acceptResult(r)"
              >✓ รับผล</button>
              <button v-if="role !== 'tester' && r.locked != 1" class="btn-sm btn-del" @click="deleteRecord(r)">ลบ</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../api/index.js'
import { useAuth } from '../composables/useAuth.js'

const router = useRouter()
const { role } = useAuth()

const qualityCheck = ref([])
const dissolution = ref([])
const loading = ref(true)
const activeTab = ref('all')
const filterName   = ref('')
const filterNumber = ref('')
const sortField    = ref('created_at')
const sortDir      = ref('desc')

const tabs = computed(() => [
  { key: 'all',        label: 'ทั้งหมด',          cls: 'tab-all',   count: qualityCheck.value.length + dissolution.value.length },
  { key: 'qc',         label: 'Quality Check',     cls: 'tab-orange', count: qualityCheck.value.length },
  { key: 'dissolution',label: 'Dissolution',       cls: 'tab-green',  count: dissolution.value.length },
])

const combined = computed(() => {
  const qc = qualityCheck.value.map(r => ({ ...r, _type: 'qc' }))
  const ds = dissolution.value.map(r => ({ ...r, _type: 'dissolution' }))
  return [...qc, ...ds]
})

const filtered = computed(() => {
  let list = combined.value
  if (activeTab.value === 'qc') list = list.filter(r => r._type === 'qc')
  if (activeTab.value === 'dissolution') list = list.filter(r => r._type === 'dissolution')

  const name = filterName.value.trim().toLowerCase()
  const num  = filterNumber.value.trim().toLowerCase()
  if (name) list = list.filter(r =>
    (r.product_name || '').toLowerCase().includes(name) ||
    (r.requester   || '').toLowerCase().includes(name) ||
    (r.sender      || '').toLowerCase().includes(name)
  )
  if (num) list = list.filter(r =>
    (r.analysis_number || '').toLowerCase().includes(num)
  )

  return [...list].sort((a, b) => {
    let va, vb
    if (sortField.value === 'send_date') {
      va = a.send_date || ''; vb = b.send_date || ''
    } else if (sortField.value === 'urgency') {
      va = Number(a.urgency_level) || 0; vb = Number(b.urgency_level) || 0
    } else {
      va = a.created_at || ''; vb = b.created_at || ''
    }
    if (va < vb) return sortDir.value === 'asc' ? -1 : 1
    if (va > vb) return sortDir.value === 'asc' ?  1 : -1
    return 0
  })
})

function toggleSort(field) {
  if (sortField.value === field) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDir.value = 'desc'
  }
}

async function loadAll() {
  loading.value = true
  try {
    [qualityCheck.value, dissolution.value] = await Promise.all([
      api.qualityCheck.list(),
      api.dissolution.list(),
    ])
  } catch {
    // backend unavailable
  }
  loading.value = false
}

function openRecord(r) {
  if (r._type === 'qc') router.push(`/quality-check/${r.id}`)
  else router.push(`/dissolution/${r.id}`)
}

async function deleteRecord(r) {
  if (!confirm('ยืนยันการลบรายการนี้?')) return
  try {
    if (r._type === 'qc') await api.qualityCheck.delete(r.id)
    else await api.dissolution.delete(r.id)
    loadAll()
  } catch {
    alert('เกิดข้อผิดพลาดในการลบ')
  }
}

const paramLabels = {
  assay: 'Assay', identification: 'Identification', appearance: 'Appearance',
  dissolution: 'Dissolution', ph: 'pH', microbial: 'Microbial', other: 'Other',
}

function parseJSON(val) {
  if (!val) return null
  if (typeof val === 'object') return val
  try { return JSON.parse(val) } catch { return null }
}

function getActiveParams(r) {
  const params = parseJSON(r.params)
  if (!params) return '-'
  const active = Object.entries(params).filter(([, v]) => v).map(([k]) => paramLabels[k] || k)
  const custom = parseJSON(r.custom_params)
  if (Array.isArray(custom)) custom.filter(cp => cp.label).forEach(cp => active.push(cp.label))
  return active.length > 0 ? active.join(', ') : '-'
}

function urgencyLabel(level) {
  if (level == 3) return 'สูง'
  if (level == 2) return 'ปานกลาง'
  if (level == 1) return 'ต่ำ'
  return '-'
}

function urgencyBadgeClass(level) {
  if (level == 3) return 'urgency-high'
  if (level == 2) return 'urgency-medium'
  if (level == 1) return 'urgency-low'
  return ''
}

const STATUS_LABEL = {
  pending:     'ส่งวิเคราะห์',
  in_progress: 'กำลังวิเคราะห์',
  pending_rd:  'รอรับผล',
  complete:    'รับผลเรียบร้อย',
}

function trackingLabel(r) {
  if (r.status && STATUS_LABEL[r.status]) return STATUS_LABEL[r.status]
  if (r.locked == 1) return 'ปิดแล้ว'
  if (r._type === 'qc') {
    if (r.result) return 'รอยืนยัน'
    if (r.upload_count > 0) return 'กำลังวิเคราะห์'
    return 'รอวิเคราะห์'
  }
  return r.f2_result ? 'มีผลแล้ว' : 'รอผล'
}

function trackingClass(r) {
  const map = {
    'ส่งวิเคราะห์':        'track-pending',
    'รอผลการวิเคราะห์':   'track-analyzing',
    'รอรับผล':             'track-waiting-rd',
    'รับผลเรียบร้อย':      'track-done',
    'ปิดแล้ว': 'track-closed', 'รอยืนยัน': 'track-pending',
    'กำลังวิเคราะห์': 'track-analyzing', 'มีผลแล้ว': 'track-done',
    'รอผล': 'track-waiting', 'รอวิเคราะห์': 'track-waiting',
  }
  return map[trackingLabel(r)] || 'track-waiting'
}

async function acceptResult(r) {
  if (!confirm(`ยืนยันการรับผลรายการ #${r.id}?\nสถานะจะเปลี่ยนเป็น "รับผลเรียบร้อย" และไม่สามารถแก้ไขได้`)) return
  try {
    await api.qualityCheck.patch(r.id, { status: 'complete' })
    await loadAll()
  } catch {
    alert('เกิดข้อผิดพลาด')
  }
}

function formatDate(dt) {
  if (!dt) return '-'
  const m = String(dt).match(/^(\d{4})-(\d{2})-(\d{2})/)
  if (m) return `${m[3]}/${m[2]}/${m[1]}`
  const d = new Date(dt)
  if (isNaN(d.getTime())) return '-'
  return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`
}

onMounted(loadAll)
</script>

<style scoped>
/* ── Slash prefix ── */
.sl { color: var(--c-teal); margin-right: 4px; font-weight: 700; }

/* ── Page header ── */
.page-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 0 16px; flex-wrap: wrap; gap: 12px;
}
.header-left { display: flex; align-items: center; gap: 16px; }
.page-header h1 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0; }
.back-btn { color: var(--c-teal); text-decoration: none; font-size: 14px; font-weight: 600; white-space: nowrap; }
.back-btn:hover { opacity: 0.8; }

.header-stats { display: flex; gap: 8px; flex-wrap: wrap; }
.stat-chip { font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px; }
.chip-orange { background: var(--accent-orange-light); color: var(--accent-orange); }
.chip-green  { background: var(--accent-green-light);  color: var(--accent-green); }

/* ── Filter bar ── */
.filter-bar {
  display: flex; gap: 10px; align-items: center;
  margin-bottom: 20px; flex-wrap: wrap;
}
.tab-group { display: flex; gap: 6px; }
.tab-btn {
  padding: 7px 16px; border-radius: 20px; border: 1.5px solid var(--border);
  background: var(--surface); color: var(--text2); cursor: pointer;
  font-family: inherit; font-size: 13px; font-weight: 500;
  display: flex; align-items: center; gap: 6px;
  transition: all 0.15s;
}
.tab-btn:hover { background: var(--surface2); }
.tab-btn.active.tab-all    { background: var(--c-teal); color: var(--c-dark); border-color: var(--c-teal); font-weight: 700; }
.tab-btn.active.tab-orange { background: var(--accent-orange); color: white; border-color: var(--accent-orange); font-weight: 700; }
.tab-btn.active.tab-green  { background: var(--accent-green); color: white; border-color: var(--accent-green); font-weight: 700; }
.tab-count {
  padding: 1px 7px; border-radius: 10px;
  font-size: 11px; font-weight: 700;
  background: rgba(0,0,0,0.12);
}
.tab-btn.active .tab-count { background: rgba(0,0,0,0.15); }
.tab-btn:not(.active) .tab-count { background: var(--surface2); color: var(--text3); }

.filter-inputs { display: flex; gap: 8px; flex-wrap: wrap; }
.filter-input {
  min-width: 180px; max-width: 260px;
  border: 1.5px solid var(--border); border-radius: var(--r-sm);
  padding: 7px 12px; font-size: 13px; font-family: inherit;
  background: var(--surface); color: var(--text); outline: none;
  transition: border-color 0.2s;
}
.filter-input:focus { border-color: var(--c-teal); box-shadow: 0 0 0 3px rgba(0,229,160,0.10); }
.filter-input::placeholder { color: var(--text3); }
.sort-group { display: flex; gap: 6px; flex-shrink: 0; flex-wrap: wrap; }
.sort-btn {
  padding: 6px 14px; border-radius: 20px;
  border: 1.5px solid var(--border);
  background: var(--surface); color: var(--text2);
  cursor: pointer; font-family: inherit; font-size: 12px; font-weight: 600;
  transition: all 0.15s; white-space: nowrap;
}
.sort-btn:hover { border-color: var(--c-teal); color: var(--text); }
.sort-btn.sort-active { background: var(--c-teal); color: var(--c-dark); border-color: var(--c-teal); }
.sort-arrow { font-size: 10px; opacity: 0.8; }

/* ── State messages ── */
.state-msg {
  text-align: center; padding: 48px; color: var(--text3);
  font-size: 15px; background: var(--surface); border-radius: var(--r-md);
  border: 1px solid var(--border);
}

/* ── Table ── */
.table-wrap {
  background: var(--surface); border-radius: var(--r-md);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm); overflow-x: auto;
  transition: background 0.25s;
}
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th {
  text-align: left; padding: 10px 14px;
  background: var(--surface2); font-weight: 600;
  color: var(--text2); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
}
.data-table td {
  padding: 10px 14px; border-bottom: 1px solid var(--border);
  color: var(--text); vertical-align: middle;
}
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: var(--surface2); }

.td-id   { color: var(--text3); font-size: 12px; font-weight: 600; }
.td-mono { font-family: 'Courier New', monospace; font-size: 13px; font-weight: 600; color: var(--text); }
.td-name { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 500; }

/* ── Badges ── */
.type-badge {
  font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px;
  white-space: nowrap; letter-spacing: 0.2px;
}
.badge-orange { background: var(--accent-orange-light); color: var(--accent-orange); }
.badge-green  { background: var(--accent-green-light);  color: var(--accent-green); }

.result-badge {
  font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px;
  white-space: nowrap;
}
.badge-pass { background: var(--result-pass-bg); color: var(--result-pass-text); }
.badge-fail { background: var(--result-fail-bg); color: var(--result-fail-text); }
.badge-none { color: var(--text3); }

.f2-val { font-size: 13px; font-weight: 600; color: var(--c-teal); }

.multi-badge {
  display: inline-block;
  margin-left: 5px;
  font-size: 10px;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 10px;
  background: var(--accent-green-light);
  color: var(--accent-green);
  vertical-align: middle;
}

.sub-text {
  font-size: 11px;
  color: var(--text3);
  margin-top: 2px;
}

/* ── Action buttons ── */
.td-actions { white-space: nowrap; }
.btn-sm {
  padding: 4px 12px; border-radius: 20px; border: 1px solid var(--border);
  cursor: pointer; font-size: 12px; font-family: inherit;
  font-weight: 500; margin-right: 4px;
  background: var(--surface);
  transition: background 0.15s, border-color 0.15s;
}
.btn-view { color: var(--c-blue); border-color: var(--c-blue); }
.btn-view:hover { background: #eff6ff; }
.btn-test { color: #059669; border-color: #34d399; }
.btn-test:hover { background: rgba(52,211,153,0.12); }
.btn-del  { color: var(--c-red); border-color: var(--c-red); }
.btn-del:hover { background: #fff1f2; }
.btn-lock { color: var(--c-teal); border-color: var(--c-teal); }
.btn-lock:hover { background: rgba(0,229,160,0.1); }

/* ── New columns ── */
.td-params {
  max-width: 160px; overflow: hidden; text-overflow: ellipsis;
  white-space: nowrap; font-size: 12px; color: var(--text2);
}
.td-na { color: var(--text3); }

.urgency-badge {
  font-size: 11px; font-weight: 700; padding: 3px 9px;
  border-radius: 20px; white-space: nowrap;
}
.urgency-high   { background: #fef2f2; color: #dc2626; }
.urgency-medium { background: #fffbeb; color: #d97706; }
.urgency-low    { background: #f0fdf4; color: #16a34a; }

.track-badge {
  font-size: 11px; font-weight: 600; padding: 3px 9px;
  border-radius: 20px; white-space: nowrap;
}
.track-waiting    { background: var(--surface2);           color: var(--text3); }
.track-analyzing  { background: #eff6ff;                   color: #2563eb; }
.track-pending    { background: #fffbeb;                   color: #d97706; }
.track-waiting-rd { background: #f3e8ff;                   color: #7c3aed; }
.track-done       { background: var(--accent-green-light); color: var(--accent-green); }
.track-closed     { background: rgba(0,229,160,0.15);      color: var(--c-teal); font-weight: 700; }

.locked-badge {
  display: inline-block; font-size: 11px; font-weight: 700;
  padding: 3px 9px; border-radius: 20px;
  background: rgba(0,229,160,0.15); color: var(--c-teal);
  white-space: nowrap; margin-right: 4px;
}
</style>
