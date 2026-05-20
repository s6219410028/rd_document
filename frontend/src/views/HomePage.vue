<template>
  <div>
    <div class="page-header">
      <h1>ระบบจัดการเอกสาร R&D</h1>
      <p>T.MAN PHARMA CO., LTD. — แผนกวิจัยและพัฒนา</p>
    </div>

    <div class="form-cards">
      <div class="form-card" @click="$router.push('/stability')">
        <div class="form-icon icon-blue">📋</div>
        <div class="form-info">
          <div class="form-code code-blue">F-RD-FD-012</div>
          <div class="form-title">แบบฟอร์มการศึกษาความคงสภาพของผลิตภัณฑ์</div>
          <div class="form-subtitle">Stability Program</div>
        </div>
        <div class="form-count">{{ counts.stability }} รายการ</div>
        <button class="btn-new btn-blue">+ สร้างใหม่</button>
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
    </div>

    <!-- Recent Records -->
    <div class="recent-section">
      <h2>รายการล่าสุด</h2>

      <div class="recent-group">
        <h3>Stability Program (F-RD-FD-012)</h3>
        <div v-if="stability.length === 0" class="empty">ยังไม่มีข้อมูล</div>
        <table v-else class="data-table">
          <thead><tr><th>ID</th><th>ชื่อผลิตภัณฑ์</th><th>วันที่สร้าง</th><th>จัดการ</th></tr></thead>
          <tbody>
            <tr v-for="r in stability" :key="r.id">
              <td>#{{ r.id }}</td>
              <td>{{ r.product_name || '-' }}</td>
              <td>{{ formatDate(r.created_at) }}</td>
              <td>
                <button class="btn-sm btn-view" @click.stop="$router.push(`/stability/${r.id}`)">เปิด</button>
                <button class="btn-sm btn-del" @click.stop="deleteRecord('stability', r.id)">ลบ</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="recent-group">
        <h3>Dissolution Profile (F-RD-FD-006)</h3>
        <div v-if="dissolution.length === 0" class="empty">ยังไม่มีข้อมูล</div>
        <table v-else class="data-table">
          <thead><tr><th>ID</th><th>เลขที่ใบส่งวิเคราะห์</th><th>วันที่สร้าง</th><th>จัดการ</th></tr></thead>
          <tbody>
            <tr v-for="r in dissolution" :key="r.id">
              <td>#{{ r.id }}</td>
              <td>{{ r.analysis_number || '-' }}</td>
              <td>{{ formatDate(r.created_at) }}</td>
              <td>
                <button class="btn-sm btn-view" @click.stop="$router.push(`/dissolution/${r.id}`)">เปิด</button>
                <button class="btn-sm btn-del" @click.stop="deleteRecord('dissolution', r.id)">ลบ</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="recent-group">
        <h3>Quality Check (F-RD-FD-005)</h3>
        <div v-if="qualityCheck.length === 0" class="empty">ยังไม่มีข้อมูล</div>
        <table v-else class="data-table">
          <thead><tr><th>ID</th><th>ชื่อผลิตภัณฑ์</th><th>วันที่สร้าง</th><th>จัดการ</th></tr></thead>
          <tbody>
            <tr v-for="r in qualityCheck" :key="r.id">
              <td>#{{ r.id }}</td>
              <td>{{ r.product_name || '-' }}</td>
              <td>{{ formatDate(r.created_at) }}</td>
              <td>
                <button class="btn-sm btn-view" @click.stop="$router.push(`/quality-check/${r.id}`)">เปิด</button>
                <button class="btn-sm btn-del" @click.stop="deleteRecord('qualityCheck', r.id)">ลบ</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from '../api/index.js'

const stability = ref([])
const dissolution = ref([])
const qualityCheck = ref([])

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

function formatDate(dt) {
  if (!dt) return '-'
  return new Date(dt).toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(loadAll)
</script>

<style scoped>
.page-header { text-align: center; padding: 32px 0 24px; }
.page-header h1 { font-size: 28px; font-weight: 700; color: var(--accent-blue); }
.page-header p { color: var(--text-muted); margin-top: 4px; }

.form-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 40px; }
.form-card {
  background: var(--bg-card);
  border-radius: 12px; padding: 20px;
  box-shadow: var(--shadow-card); cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s, background 0.25s;
  display: flex; flex-direction: column; gap: 10px;
  border: 1px solid var(--border-divider);
}
.form-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-card-hover); }

.form-icon { width: 48px; height: 48px; border-radius: 12px; font-size: 24px; display: flex; align-items: center; justify-content: center; }
.icon-blue  { background: var(--accent-blue-light); }
.icon-green { background: var(--accent-green-light); }
.icon-orange{ background: var(--accent-orange-light); }

.form-code { font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 4px; display: inline-block; }
.code-blue   { color: var(--accent-blue);   background: var(--accent-blue-light); }
.code-green  { color: var(--accent-green);  background: var(--accent-green-light); }
.code-orange { color: var(--accent-orange); background: var(--accent-orange-light); }

.form-title    { font-size: 14px; font-weight: 600; color: var(--text-label); margin-top: 4px; }
.form-subtitle { font-size: 12px; color: var(--text-muted); }
.form-count    { font-size: 13px; color: var(--text-secondary); }

.btn-new { border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-family: inherit; font-size: 13px; color: white; }
.btn-blue   { background: var(--accent-blue); }
.btn-blue:hover   { background: var(--accent-blue-hover); }
.btn-green  { background: var(--accent-green); }
.btn-green:hover  { background: var(--accent-green-hover); }
.btn-orange { background: var(--accent-orange); }
.btn-orange:hover { background: var(--accent-orange-hover); }

.recent-section h2 { font-size: 20px; font-weight: 700; color: var(--accent-blue); margin-bottom: 20px; }
.recent-group {
  background: var(--bg-card);
  border-radius: 10px; padding: 20px; margin-bottom: 16px;
  box-shadow: var(--shadow-card);
  border: 1px solid var(--border-divider);
  transition: background 0.25s;
}
.recent-group h3 { font-size: 15px; font-weight: 600; color: var(--text-label); margin-bottom: 12px; border-bottom: 1px solid var(--border-divider); padding-bottom: 8px; }
.empty { color: var(--text-placeholder); font-size: 14px; padding: 12px 0; }

.data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.data-table th { text-align: left; padding: 8px 12px; background: var(--bg-table-head); font-weight: 600; color: var(--text-secondary); }
.data-table td { padding: 8px 12px; border-bottom: 1px solid var(--border-divider); color: var(--text-label); }
.data-table tr:hover td { background: var(--bg-hover); }

.btn-sm { padding: 4px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-family: inherit; margin-right: 4px; transition: background 0.15s; }
.btn-view { background: var(--badge-view-bg); color: var(--badge-view-text); }
.btn-view:hover { background: var(--accent-blue-mid); }
.btn-del  { background: var(--badge-del-bg);  color: var(--badge-del-text); }
.btn-del:hover  { background: var(--result-fail-bg); }

@media (max-width: 768px) { .form-cards { grid-template-columns: 1fr; } }
</style>
