<template>
  <div>
    <div class="page-header">
      <h1><span class="sl">//</span> ระบบจัดการเอกสาร RD</h1>
      <p>T.MAN PHARMA CO., LTD. — แผนกวิจัยและพัฒนา</p>
    </div>
    <div class="form-cards">
      <div class="form-card" @click="$router.push('/quality-check')">
        <div class="form-icon icon-orange">🔬</div>
        <div class="form-info">
          <div class="form-code code-orange">F-RD-FD-005</div>
          <div class="form-title">แบบฟอร์มการตรวจสอบคุณภาพวัตถุดิบและเภสัชภัณฑ์</div>
          <div class="form-subtitle">ในขั้นตอนการวิจัยและพัฒนา</div>
        </div>
        <div class="form-count">{{ counts.qualityCheck }} รายการ</div>
        <button class="btn-new btn-orange">+ สร้างใหม่</button>
      </div>

      <div class="form-card" @click="$router.push('/dissolution')">
        <div class="form-icon icon-green">🧪</div>
        <div class="form-info">
          <div class="form-code code-green">F-RD-FD-006</div>
          <div class="form-title">แบบฟอร์มส่งวิเคราะห์ Dissolution profile</div>
          <div class="form-subtitle">ของผลิตภัณฑ์ในขั้นตอนการวิจัยและพัฒนา</div>
        </div>
        <div class="form-count">{{ counts.dissolution }} รายการ</div>
        <button class="btn-new btn-green">+ สร้างใหม่</button>
      </div>


      <!-- <div class="form-card" @click="$router.push('/stability')">
        <div class="form-icon icon-blue">📋</div>
        <div class="form-info">
          <div class="form-code code-blue">F-RD-FD-012</div>
          <div class="form-title">แบบฟอร์มการศึกษาความคงสภาพของผลิตภัณฑ์</div>
          <div class="form-subtitle">Stability Program</div>
        </div>
        <div class="form-count">{{ counts.stability }} รายการ</div>
        <button class="btn-new btn-blue">+ สร้างใหม่</button>
      </div> -->

    </div>

    <!-- Recent Records -->
    <div class="recent-section">
      <div class="recent-header">
        <h2><span class="sl">//</span> รายการล่าสุด</h2>
        <button class="btn-all-records" @click="$router.push('/records')">ดูข้อมูลทั้งหมด →</button>
      </div>

      <div class="recent-group">
        <div class="group-header">
          <h3><span class="sl">//</span> Quality Check <span class="form-code-tag">F-RD-FD-005</span></h3>
        </div>
        <div class="tbl-filter-bar">
          <div class="tbl-filter-inputs">
            <div class="tbl-search-wrap">
              <span class="tbl-search-icon">🔍</span>
              <input v-model="qcFilterName" class="tbl-filter-input" type="text" placeholder="ชื่อผลิตภัณฑ์..." @click.stop />
            </div>
            <div class="tbl-search-wrap">
              <span class="tbl-search-icon">📋</span>
              <input v-model="qcFilterNumber" class="tbl-filter-input" type="text" placeholder="เลขที่ใบส่งวิเคราะห์..." @click.stop />
            </div>
          </div>
          <div class="sort-group">
            <button class="sort-btn" :class="{ 'sort-active': qcSortField === 'send_date' }" @click.stop="toggleQcSort('send_date')">
              วันที่ส่ง <span class="sort-arrow">{{ qcSortField === 'send_date' ? (qcSortDir === 'asc' ? '▲' : '▼') : '↕' }}</span>
            </button>
            <button class="sort-btn" :class="{ 'sort-active': qcSortField === 'urgency' }" @click.stop="toggleQcSort('urgency')">
              ความเร่งด่วน <span class="sort-arrow">{{ qcSortField === 'urgency' ? (qcSortDir === 'asc' ? '▲' : '▼') : '↕' }}</span>
            </button>
          </div>
        </div>
        <div v-if="qualityCheck.length === 0" class="empty">ยังไม่มีข้อมูล</div>
        <template v-else>
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>ชื่อผลิตภัณฑ์</th>
                <th style="width:110px">PDF อัปโหลด</th>
                <th style="width:90px">ความเร่งด่วน</th>
                <th style="width:130px">สถานะ</th>
                <th>วันที่สร้าง</th>
                <th>จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in filteredQualityCheck" :key="r.id">
                <td>#{{ r.id }}</td>
                <td>{{ r.product_name || '-' }}</td>
                <td class="td-progress">
                  <template v-if="qcProgressMap[r.id]?.total > 0">
                    <div class="prog-label">
                      {{ qcProgressMap[r.id].uploaded }}/{{ qcProgressMap[r.id].total }}
                      <span class="prog-pct">{{ qcProgressMap[r.id].pct }}%</span>
                    </div>
                    <div class="prog-track">
                      <div
                        class="prog-fill"
                        :class="{ 'prog-done': qcProgressMap[r.id].pct === 100 }"
                        :style="{ width: qcProgressMap[r.id].pct + '%' }"
                      ></div>
                    </div>
                  </template>
                  <span v-else class="prog-none">ยังไม่ได้เลือก</span>
                </td>
                <td>
                  <span v-if="urgencyLabel(r.urgency_level)" :class="['urg-badge', urgencyClass(r.urgency_level)]">{{ urgencyLabel(r.urgency_level) }}</span>
                  <span v-else class="td-na">-</span>
                </td>
                <td><span :class="['st-badge', statusInfo(r).cls]">{{ statusInfo(r).label }}</span></td>
                <td>{{ formatDate(r.created_at) }}</td>
                <td>
                  <button v-if="role === 'tester'" class="btn-sm btn-test" @click.stop="$router.push(`/quality-check/${r.id}/test`)">ทดสอบ</button>
                  <template v-else>
                    <button class="btn-sm btn-view" @click.stop="$router.push(`/quality-check/${r.id}`)">เปิด</button>
                    <button v-if="r.status === 'in_progress'" class="btn-sm btn-accept" @click.stop="acceptQcResult(r.id)">✓ รับผล</button>
                    <button class="btn-sm btn-del" @click.stop="deleteRecord('qualityCheck', r.id)">ลบ</button>
                  </template>
                </td>
              </tr>
              <tr v-if="filteredQualityCheck.length === 0">
                <td colspan="7" class="empty">ไม่พบรายการที่ค้นหา</td>
              </tr>
            </tbody>
          </table>
        </template>
      </div>

      <div class="recent-group">
        <div class="group-header">
          <h3><span class="sl">//</span> Dissolution Profile <span class="form-code-tag">F-RD-FD-006</span></h3>
        </div>
        <div class="tbl-filter-bar">
          <div class="tbl-filter-inputs">
            <div class="tbl-search-wrap">
              <span class="tbl-search-icon">🔍</span>
              <input v-model="disFilterName" class="tbl-filter-input" type="text" placeholder="ชื่อผลิตภัณฑ์..." @click.stop />
            </div>
            <div class="tbl-search-wrap">
              <span class="tbl-search-icon">📋</span>
              <input v-model="disFilterNumber" class="tbl-filter-input" type="text" placeholder="เลขที่ใบส่งวิเคราะห์..." @click.stop />
            </div>
          </div>
          <div class="sort-group">
            <button class="sort-btn" :class="{ 'sort-active': disSortField === 'send_date' }" @click.stop="toggleDisSort('send_date')">
              วันที่ส่ง <span class="sort-arrow">{{ disSortField === 'send_date' ? (disSortDir === 'asc' ? '▲' : '▼') : '↕' }}</span>
            </button>
            <button class="sort-btn" :class="{ 'sort-active': disSortField === 'urgency' }" @click.stop="toggleDisSort('urgency')">
              ความเร่งด่วน <span class="sort-arrow">{{ disSortField === 'urgency' ? (disSortDir === 'asc' ? '▲' : '▼') : '↕' }}</span>
            </button>
          </div>
        </div>
        <div v-if="dissolution.length === 0" class="empty">ยังไม่มีข้อมูล</div>
        <template v-else>
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>เลขที่ใบส่งวิเคราะห์</th>
                <th style="width:90px">ความเร่งด่วน</th>
                <th style="width:130px">สถานะ</th>
                <th>วันที่สร้าง</th>
                <th>จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in filteredDissolution" :key="r.id">
                <td>#{{ r.id }}</td>
                <td>{{ r.analysis_number || '-' }}</td>
                <td>
                  <span v-if="urgencyLabel(r.urgency_level)" :class="['urg-badge', urgencyClass(r.urgency_level)]">{{ urgencyLabel(r.urgency_level) }}</span>
                  <span v-else class="td-na">-</span>
                </td>
                <td><span :class="['st-badge', statusInfo(r).cls]">{{ statusInfo(r).label }}</span></td>
                <td>{{ formatDate(r.created_at) }}</td>
                <td>
                  <button v-if="role === 'tester'" class="btn-sm btn-test" @click.stop="$router.push(`/dissolution/${r.id}/test`)">ทดสอบ</button>
                  <template v-else>
                    <button class="btn-sm btn-view" @click.stop="$router.push(`/dissolution/${r.id}`)">เปิด</button>
                    <button class="btn-sm btn-del" @click.stop="deleteRecord('dissolution', r.id)">ลบ</button>
                  </template>
                </td>
              </tr>
              <tr v-if="filteredDissolution.length === 0">
                <td colspan="6" class="empty">ไม่พบรายการที่ค้นหา</td>
              </tr>
            </tbody>
          </table>
        </template>
      </div>

      <div class="recent-group">
        <div class="group-header">
          <h3><span class="sl">//</span> Stability Program <span class="form-code-tag">F-RD-FD-012</span></h3>
          <div class="search-box">
            <span class="search-icon">🔍</span>
            <input v-model="stabFilterName" class="search-input" type="text" placeholder="ค้นหาชื่อผลิตภัณฑ์..." @click.stop />
          </div>
        </div>
        <div v-if="stability.length === 0" class="empty">ยังไม่มีข้อมูล</div>
        <template v-else>
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>ชื่อผลิตภัณฑ์</th>
                <th>วันที่สร้าง</th>
                <th>จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in filteredStability" :key="r.id">
                <td>#{{ r.id }}</td>
                <td>{{ r.product_name || '-' }}</td>
                <td>{{ formatDate(r.created_at) }}</td>
                <td>
                  <button class="btn-sm btn-view" @click.stop="$router.push(`/stability/${r.id}`)">เปิด</button>
                  <button v-if="role !== 'tester'" class="btn-sm btn-del" @click.stop="deleteRecord('stability', r.id)">ลบ</button>
                </td>
              </tr>
              <tr v-if="filteredStability.length === 0">
                <td colspan="4" class="empty">ไม่พบรายการที่ค้นหา</td>
              </tr>
            </tbody>
          </table>
        </template>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from '../api/index.js'
import { useRole } from '../composables/useRole.js'

const stability = ref([])
const dissolution = ref([])
const qualityCheck = ref([])
const { role } = useRole()

const qcFilterName   = ref('')
const qcFilterNumber = ref('')
const qcSortField    = ref('created_at')
const qcSortDir      = ref('desc')

const disFilterName   = ref('')
const disFilterNumber = ref('')
const disSortField    = ref('created_at')
const disSortDir      = ref('desc')

const stabFilterName = ref('')

function applySort(list, field, dir) {
  return [...list].sort((a, b) => {
    let va, vb
    if (field === 'send_date') {
      va = a.send_date || ''; vb = b.send_date || ''
    } else if (field === 'urgency') {
      va = Number(a.urgency_level) || 0; vb = Number(b.urgency_level) || 0
    } else {
      va = a.created_at || ''; vb = b.created_at || ''
    }
    if (va < vb) return dir === 'asc' ? -1 : 1
    if (va > vb) return dir === 'asc' ?  1 : -1
    return 0
  })
}

function toggleQcSort(field) {
  if (qcSortField.value === field) { qcSortDir.value = qcSortDir.value === 'asc' ? 'desc' : 'asc' }
  else { qcSortField.value = field; qcSortDir.value = 'desc' }
}

function toggleDisSort(field) {
  if (disSortField.value === field) { disSortDir.value = disSortDir.value === 'asc' ? 'desc' : 'asc' }
  else { disSortField.value = field; disSortDir.value = 'desc' }
}

const filteredQualityCheck = computed(() => {
  let list = qualityCheck.value
  const name = qcFilterName.value.trim().toLowerCase()
  const num  = qcFilterNumber.value.trim().toLowerCase()
  if (name) list = list.filter(r => (r.product_name || '').toLowerCase().includes(name))
  if (num)  list = list.filter(r => (r.analysis_number || '').toLowerCase().includes(num))
  return applySort(list, qcSortField.value, qcSortDir.value)
})

const filteredDissolution = computed(() => {
  let list = dissolution.value
  const name = disFilterName.value.trim().toLowerCase()
  const num  = disFilterNumber.value.trim().toLowerCase()
  if (name) list = list.filter(r => (r.product_name || '').toLowerCase().includes(name))
  if (num)  list = list.filter(r => (r.analysis_number || '').toLowerCase().includes(num))
  return applySort(list, disSortField.value, disSortDir.value)
})

const filteredStability = computed(() => {
  const q = stabFilterName.value.trim().toLowerCase()
  if (!q) return stability.value
  return stability.value.filter(r => (r.product_name || '').toLowerCase().includes(q))
})

function qcProgress(r) {
  let params = r.params
  if (typeof params === 'string') { try { params = JSON.parse(params) } catch { params = {} } }
  let customParams = r.custom_params
  if (typeof customParams === 'string') { try { customParams = JSON.parse(customParams) } catch { customParams = [] } }
  const total = (params ? Object.values(params).filter(Boolean).length : 0)
              + ((customParams || []).filter(cp => cp.checked).length)
  const uploaded = Number(r.upload_count) || 0
  return { uploaded, total, pct: total > 0 ? Math.min(100, Math.round((uploaded / total) * 100)) : 0 }
}

const qcProgressMap = computed(() => {
  const map = {}
  qualityCheck.value.forEach(r => { map[r.id] = qcProgress(r) })
  return map
})

const counts = computed(() => ({
  stability: stability.value.length,
  dissolution: dissolution.value.length,
  qualityCheck: qualityCheck.value.length,
}))

async function loadAll() {
  try {
    [stability.value, dissolution.value, qualityCheck.value] = await Promise.all([
      api.stability.list(),
      api.dissolution.list(),
      api.qualityCheck.list(),
    ])
  } catch {
    // Backend not available yet
  }
}

async function deleteRecord(type, id) {
  if (!confirm('ยืนยันการลบรายการนี้?')) return
  await api[type].delete(id)
  loadAll()
}

async function acceptQcResult(id) {
  if (!confirm(`ยืนยันการรับผลรายการ #${id}?\nสถานะจะเปลี่ยนเป็น "รับผลเรียบร้อย" และไม่สามารถแก้ไขได้`)) return
  try {
    await api.qualityCheck.patch(id, { status: 'complete' })
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
  return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`
}

const STATUS_INFO = {
  pending:     { label: 'ส่งวิเคราะห์',     cls: 'st-pending'     },
  in_progress: { label: 'กำลังวิเคราะห์',   cls: 'st-in-progress' },
  pending_rd:  { label: 'รอรับผล',           cls: 'st-pending-rd'  },
  complete:    { label: 'รับผลเรียบร้อย',    cls: 'st-complete'    },
}
function statusInfo(r) { return STATUS_INFO[r.status] || STATUS_INFO.pending }

function urgencyLabel(level) {
  if (level == 3) return 'สูง'
  if (level == 2) return 'ปานกลาง'
  if (level == 1) return 'ต่ำ'
  return null
}
function urgencyClass(level) {
  if (level == 3) return 'urg-high'
  if (level == 2) return 'urg-medium'
  if (level == 1) return 'urg-low'
  return ''
}

onMounted(loadAll)
</script>

<style scoped>
/* ── Slash prefix ── */
.sl {
  color: var(--c-teal);
  margin-right: 4px;
  font-weight: 700;
}

/* ── Page header ── */
.page-header {
  text-align: center;
  padding: 32px 0 24px;
}

.page-header h1 {
  font-size: 26px;
  font-weight: 700;
  color: var(--text);
  letter-spacing: 0.3px;
}

.page-header p {
  color: var(--text3);
  margin-top: 6px;
  font-size: 13px;
}

/* ── Form cards ── */
.form-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 40px;
}

.form-card {
  background: var(--surface);
  border-radius: var(--r-lg);
  padding: 20px;
  box-shadow: var(--shadow-sm);
  cursor: pointer;
  transition: transform 0.18s, box-shadow 0.18s, background 0.25s;
  display: flex;
  flex-direction: column;
  gap: 10px;
  border: 1px solid var(--border);
  border-top: 3px solid var(--border);
}

.form-card:nth-child(1) {
  border-top-color: var(--accent-blue);
}

.form-card:nth-child(2) {
  border-top-color: var(--accent-green);
}

.form-card:nth-child(3) {
  border-top-color: var(--accent-orange);
}

.form-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}

.form-icon {
  width: 44px;
  height: 44px;
  border-radius: var(--r-md);
  font-size: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-blue {
  background: var(--accent-blue-light);
}

.icon-green {
  background: var(--accent-green-light);
}

.icon-orange {
  background: var(--accent-orange-light);
}

.form-code {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 20px;
  display: inline-block;
  letter-spacing: 0.3px;
}

.code-blue {
  color: var(--accent-blue);
  background: var(--accent-blue-light);
}

.code-green {
  color: var(--accent-green);
  background: var(--accent-green-light);
}

.code-orange {
  color: var(--accent-orange);
  background: var(--accent-orange-light);
}

.form-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--text);
  margin-top: 4px;
  line-height: 1.4;
}

.form-subtitle {
  font-size: 12px;
  color: var(--text3);
  line-height: 1.4;
}

.form-count {
  font-size: 13px;
  color: var(--text2);
  font-weight: 500;
}

.btn-new {
  border: none;
  padding: 8px 16px;
  border-radius: var(--r-sm);
  cursor: pointer;
  font-family: inherit;
  font-size: 13px;
  font-weight: 600;
  transition: opacity 0.2s, transform 0.15s;
  background: var(--c-teal);
  color: var(--c-dark);
}

.btn-new:hover {
  opacity: 0.85;
  transform: translateY(-1px);
}

/* Keep per-form identity via card top border, not button color */

/* ── Recent section ── */
.recent-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.recent-section h2 {
  font-size: 20px;
  font-weight: 700;
  color: var(--text);
  margin: 0;
}

.btn-all-records {
  background: var(--c-teal);
  color: var(--c-dark);
  border: none;
  padding: 8px 18px;
  border-radius: var(--r-sm);
  cursor: pointer;
  font-family: inherit;
  font-size: 13px;
  font-weight: 700;
  transition: opacity 0.2s;
}

.btn-all-records:hover {
  opacity: 0.85;
}

.recent-group {
  background: var(--surface);
  border-radius: var(--r-md);
  margin-bottom: 16px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  overflow: hidden;
  transition: background 0.25s;
}

.group-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 16px 10px;
  border-bottom: 1px solid var(--border);
  background: var(--surface2);
}

.recent-group h3 {
  font-size: 14px;
  font-weight: 600;
  color: var(--text);
  margin: 0;
  padding: 0;
  border-bottom: none;
  display: flex;
  align-items: center;
  gap: 6px;
  background: transparent;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r-sm);
  padding: 4px 10px;
  min-width: 220px;
  transition: border-color 0.18s;
}

.search-box:focus-within {
  border-color: var(--c-teal);
}

.search-icon {
  font-size: 13px;
  opacity: 0.5;
  flex-shrink: 0;
}

.search-input {
  border: none;
  outline: none;
  background: transparent;
  font-family: inherit;
  font-size: 13px;
  color: var(--text);
  width: 100%;
}

.search-input::placeholder {
  color: var(--text3);
}

.form-code-tag {
  font-size: 11px;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 20px;
  background: var(--border);
  color: var(--text2);
}

.empty {
  color: var(--text3);
  font-size: 14px;
  padding: 20px 16px;
  text-align: center;
}

/* ── Table ── */
.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.data-table th {
  text-align: left;
  padding: 9px 16px;
  font-weight: 600;
  color: var(--text2);
  font-size: 12px;
  letter-spacing: 0.3px;
  text-transform: uppercase;
  background: var(--surface2);
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
}

.data-table td {
  padding: 9px 16px;
  border-bottom: 1px solid var(--border);
  color: var(--text);
  vertical-align: middle;
}

.data-table tr:last-child td {
  border-bottom: none;
}

.data-table tr:hover td {
  background: var(--surface2);
}

.data-table td:first-child {
  color: var(--text3);
  font-size: 12px;
}

/* ── Action buttons ── */
.btn-sm {
  padding: 4px 12px;
  border: 1px solid var(--border);
  border-radius: 20px;
  cursor: pointer;
  font-size: 12px;
  font-family: inherit;
  margin-right: 4px;
  font-weight: 500;
  transition: background 0.15s, border-color 0.15s;
  background: var(--surface);
}

.btn-view {
  color: var(--c-blue);
  border-color: var(--c-blue);
}

.btn-view:hover {
  background: #eff6ff;
}

.btn-del {
  color: var(--c-red);
  border-color: var(--c-red);
}

.btn-del:hover {
  background: #fff1f2;
}

.btn-test { color: #059669; border-color: #34d399; }
.btn-test:hover { background: rgba(52,211,153,0.12); }
.btn-accept { color: #059669; border-color: #059669; font-weight: 600; }
.btn-accept:hover { background: #f0fdf4; }

/* ── Status badges ── */
.st-badge { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; white-space: nowrap; display: inline-block; }
.st-pending     { background: #fef3c7; color: #d97706; }
.st-in-progress { background: #dbeafe; color: #2563eb; }
.st-pending-rd  { background: #f3e8ff; color: #7c3aed; }
.st-complete    { background: #d1fae5; color: #059669; }

/* ── Urgency badges ── */
.urg-badge  { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; white-space: nowrap; display: inline-block; }
.urg-high   { background: #fef2f2; color: #dc2626; }
.urg-medium { background: #fffbeb; color: #d97706; }
.urg-low    { background: #f0fdf4; color: #16a34a; }
.td-na      { color: var(--text3); font-size: 12px; }

/* ── QC upload progress bar ── */
.td-progress { min-width: 100px; }
.prog-label {
  font-size: 12px; font-weight: 600; color: var(--text2);
  margin-bottom: 4px; display: flex; align-items: center; gap: 5px;
}
.prog-pct { color: var(--text3); font-weight: 400; font-size: 11px; }
.prog-track {
  height: 5px; background: var(--border); border-radius: 3px; overflow: hidden;
}
.prog-fill {
  height: 100%; background: var(--accent-orange); border-radius: 3px;
  transition: width 0.4s ease;
}
.prog-fill.prog-done { background: var(--accent-green); }
.prog-none { font-size: 12px; color: var(--text3); }

/* ── Table filter bar ── */
.tbl-filter-bar {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 16px; border-bottom: 1px solid var(--border);
  background: var(--surface); flex-wrap: wrap;
}
.tbl-filter-inputs { display: flex; gap: 8px; flex: 1; flex-wrap: wrap; }
.tbl-search-wrap {
  display: flex; align-items: center; gap: 6px;
  background: var(--surface2); border: 1px solid var(--border);
  border-radius: var(--r-sm); padding: 5px 10px;
  flex: 1; min-width: 160px;
  transition: border-color 0.18s;
}
.tbl-search-wrap:focus-within { border-color: var(--c-teal); }
.tbl-search-icon { font-size: 12px; opacity: 0.45; flex-shrink: 0; }
.tbl-filter-input {
  border: none; outline: none; background: transparent;
  font-family: inherit; font-size: 13px; color: var(--text); width: 100%;
}
.tbl-filter-input::placeholder { color: var(--text3); }
.sort-group { display: flex; gap: 6px; flex-shrink: 0; flex-wrap: wrap; }
.sort-btn {
  padding: 5px 12px; border-radius: 20px;
  border: 1.5px solid var(--border);
  background: var(--surface2); color: var(--text2);
  cursor: pointer; font-family: inherit; font-size: 12px; font-weight: 600;
  transition: all 0.15s; white-space: nowrap;
}
.sort-btn:hover { border-color: var(--c-teal); color: var(--text); }
.sort-btn.sort-active { background: var(--c-teal); color: var(--c-dark); border-color: var(--c-teal); }
.sort-arrow { font-size: 10px; }

@media (max-width: 768px) {
  .form-cards {
    grid-template-columns: 1fr;
  }
}
</style>
