<template>
  <div>
    <div class="action-bar no-print">
      <div class="action-left">
        <router-link to="/records" class="back-btn">← รายการทั้งหมด</router-link>
        <span class="form-badge">F-RD-FD-005 REV.00</span>
        <span class="tester-badge">🔬 ผู้ทดสอบ — #{{ formId }}</span>
      </div>
      <div class="action-right">
        <button v-if="role !== 'sender'" class="btn-primary" :disabled="saving" @click="saveResult">
          {{ saving ? 'กำลังบันทึก...' : '💾 บันทึกผล' }}
        </button>
      </div>
    </div>

    <div v-if="toast" class="toast" :class="toast.type">{{ toast.msg }}</div>

    <div v-if="loading" class="state-msg">กำลังโหลด...</div>
    <div v-else-if="!form" class="state-msg">ไม่พบข้อมูล</div>

    <template v-else>
      <!-- Form summary (read-only) -->
      <div class="summary-card">
        <div class="summary-header">
          <div>
            <div class="summary-title">แบบฟอร์มการตรวจสอบคุณภาพ</div>
            <div class="summary-number">{{ form.analysis_number || '-' }}</div>
          </div>
          <div class="summary-meta">
            <div class="meta-row"><span class="meta-label">ชื่อผลิตภัณฑ์</span><span class="meta-val">{{ form.product_name || '-' }}</span></div>
            <div class="meta-row"><span class="meta-label">Lot No.</span><span class="meta-val">{{ form.lot_no || '-' }}</span></div>
            <div class="meta-row"><span class="meta-label">วันที่ส่ง</span><span class="meta-val">{{ form.send_date || '-' }}</span></div>
            <div class="meta-row"><span class="meta-label">ผู้ส่งวิเคราะห์</span><span class="meta-val">{{ form.requester || '-' }}</span></div>
          </div>
          <div class="summary-meta">
            <div class="meta-row"><span class="meta-label">Active ingredient</span><span class="meta-val">{{ form.active_ingredient || '-' }}</span></div>
            <div class="meta-row"><span class="meta-label">Dosage form</span><span class="meta-val">{{ form.dosage_form || '-' }}</span></div>
            <div class="meta-row"><span class="meta-label">Appearance</span><span class="meta-val">{{ form.appearance || '-' }}</span></div>
            <div class="meta-row">
              <span class="meta-label">ประเภท</span>
              <span class="meta-val">
                <span v-if="form.is_raw_material" class="type-chip">วัตถุดิบ</span>
                <span v-if="form.is_pharmaceutical" class="type-chip">เภสัชภัณฑ์</span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Upload section per param -->
      <div class="upload-section">
        <h2 class="section-title">อัปโหลดรายงาน PDF ต่อหัวข้อ</h2>
        <p class="section-hint">แต่ละหัวข้อที่ผู้ส่งเลือกต้องอัปโหลด 1 ไฟล์ PDF</p>

        <div v-if="activeParams.length === 0" class="empty-params">ผู้ส่งไม่ได้เลือกหัวข้อใดไว้</div>

        <div v-for="param in activeParams" :key="param.key" class="param-card">
          <div class="param-card-left">
            <div class="param-name">{{ param.label }}</div>
            <div v-if="uploadMap[param.key]" class="file-info">
              <span class="file-icon">📄</span>
              <span class="file-name">{{ uploadMap[param.key].original_name }}</span>
              <a :href="fileUrl(uploadMap[param.key].filename)" target="_blank" class="btn-preview">ดู PDF</a>
              <button class="btn-remove" @click="removeUpload(param.key)">✕ ลบ</button>
            </div>
            <div v-else class="no-file">ยังไม่ได้อัปโหลด</div>
          </div>
          <div class="param-card-right">
            <label class="upload-label" :class="{ uploaded: !!uploadMap[param.key] }">
              <input
                type="file" accept=".pdf" class="file-input"
                @change="handleFileChange(param, $event)"
                :disabled="uploadingKey === param.key"
              />
              <span v-if="uploadingKey === param.key" class="upload-btn-text">กำลังอัปโหลด...</span>
              <span v-else-if="uploadMap[param.key]" class="upload-btn-text">📎 เปลี่ยนไฟล์</span>
              <span v-else class="upload-btn-text">📎 เลือก PDF</span>
            </label>
          </div>
        </div>

        <div class="upload-summary">
          <span :class="allUploaded ? 'summary-ok' : 'summary-warn'">
            อัปโหลดแล้ว {{ uploadedCount }} / {{ activeParams.length }} หัวข้อ
          </span>
        </div>
      </div>

      <!-- Result section -->
      <div class="result-card">
        <h2 class="section-title">ผลการทดสอบ</h2>

        <!-- Read-only view for sender -->
        <template v-if="role === 'sender'">
          <div class="result-readonly">
            <span v-if="result.value === 'pass'" class="result-text pass">ผ่าน</span>
            <span v-else-if="result.value === 'fail'" class="result-text fail">ไม่ผ่าน</span>
            <span v-else class="result-text pending">ยังไม่มีผล</span>
          </div>
          <div v-if="result.value === 'fail' && result.fail_remark" class="readonly-row">
            <span class="readonly-label">หมายเหตุ:</span>
            <span class="readonly-val">{{ result.fail_remark }}</span>
          </div>
          <div class="sig-row readonly-sig">
            <div class="readonly-row">
              <span class="readonly-label">ผู้วิเคราะห์:</span>
              <span class="readonly-val">{{ result.sig_analyst || '-' }}</span>
            </div>
            <div class="readonly-row">
              <span class="readonly-label">วันที่:</span>
              <span class="readonly-val">{{ result.sig_date || '-' }}</span>
            </div>
          </div>
        </template>

        <!-- Editable form for tester -->
        <template v-else>
          <div class="result-options">
            <label class="result-opt">
              <input type="radio" v-model="result.value" value="pass" />
              <span class="result-text pass">ผ่าน</span>
            </label>
            <label class="result-opt">
              <input type="radio" v-model="result.value" value="fail" />
              <span class="result-text fail">ไม่ผ่าน</span>
            </label>
          </div>
          <div v-if="result.value === 'fail'" class="fail-remark">
            <label>หมายเหตุ:</label>
            <textarea v-model="result.fail_remark" class="input-textarea" rows="2" placeholder="ระบุสาเหตุที่ไม่ผ่าน..."></textarea>
          </div>
          <div class="sig-row">
            <div class="field-group">
              <label>ผู้วิเคราะห์:</label>
              <input v-model="result.sig_analyst" type="text" class="input-field" style="width:200px" />
            </div>
            <div class="field-group">
              <label>วันที่:</label>
              <input v-model="result.sig_date" type="date" class="input-field" style="width:160px" />
            </div>
          </div>
        </template>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/index.js'
import { useRole } from '../composables/useRole.js'

const props = defineProps({ id: String })
const route = useRoute()
const { role } = useRole()

const formId = computed(() => props.id || route.params.id)
const loading = ref(true)
const saving = ref(false)
const toast = ref(null)
const form = ref(null)
const uploads = ref([])
const uploadingKey = ref(null)

const result = reactive({ value: '', fail_remark: '', sig_analyst: '', sig_date: '' })

const paramList = [
  { key: 'assay',          label: 'Assay' },
  { key: 'identification', label: 'Identification' },
  { key: 'appearance',     label: 'Appearance' },
  { key: 'dissolution',    label: 'Dissolution' },
  { key: 'ph',             label: 'pH' },
  { key: 'microbial',      label: 'Microbial enumeration test and test for specified microorganism' },
  { key: 'other',          label: 'Other' },
]

const activeParams = computed(() => {
  if (!form.value) return []
  const f = form.value
  const standard = paramList.filter(p => f.params?.[p.key])
  const custom = (f.custom_params || [])
    .filter(cp => cp.checked)
    .map((cp, i) => ({ key: `custom_${i}`, label: cp.label }))
  return [...standard, ...custom]
})

const uploadMap = computed(() => {
  const map = {}
  uploads.value.forEach(u => { map[u.param_key] = u })
  return map
})

const uploadedCount = computed(() => Object.keys(uploadMap.value).length)
const allUploaded = computed(() => uploadedCount.value >= activeParams.value.length && activeParams.value.length > 0)

function fileUrl(filename) {
  return api.uploads.fileUrl(filename)
}

function showToast(msg, type = 'success') {
  toast.value = { msg, type }
  setTimeout(() => { toast.value = null }, 3000)
}

async function handleFileChange(param, event) {
  const file = event.target.files[0]
  if (!file) return
  uploadingKey.value = param.key
  try {
    await api.uploads.upload('quality_check', formId.value, param.key, param.label, file)
    uploads.value = await api.uploads.list('quality_check', formId.value)
    showToast(`อัปโหลด "${param.label}" สำเร็จ`)
  } catch {
    showToast('อัปโหลดไม่สำเร็จ กรุณาตรวจสอบไฟล์', 'error')
  }
  uploadingKey.value = null
  event.target.value = ''
}

async function removeUpload(paramKey) {
  const u = uploadMap.value[paramKey]
  if (!u || !confirm('ยืนยันการลบไฟล์นี้?')) return
  await api.uploads.delete(u.id)
  uploads.value = await api.uploads.list('quality_check', formId.value)
}

async function saveResult() {
  saving.value = true
  try {
    const updated = {
      ...form.value,
      result: result.value,
      fail_remark: result.fail_remark,
      sig_analyst: result.sig_analyst,
      sig_date: result.sig_date,
    }
    await api.qualityCheck.update(formId.value, updated)
    form.value = updated
    showToast('บันทึกผลสำเร็จ')
  } catch {
    showToast('เกิดข้อผิดพลาด กรุณาลองใหม่', 'error')
  }
  saving.value = false
}

onMounted(async () => {
  try {
    const res = await api.qualityCheck.get(formId.value)
    form.value = res.form_data
    result.value = res.form_data.result || ''
    result.fail_remark = res.form_data.fail_remark || ''
    result.sig_analyst = res.form_data.sig_analyst || ''
    result.sig_date = res.form_data.sig_date || ''
    uploads.value = await api.uploads.list('quality_check', formId.value)
  } catch {
    form.value = null
  }
  loading.value = false
})
</script>

<style scoped>
/* ── Action bar ── */
.action-bar { display:flex; justify-content:space-between; align-items:center; padding:12px 0; margin-bottom:16px; gap:12px; flex-wrap:wrap; }
.action-left, .action-right { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.back-btn { color:var(--c-teal, #34d399); text-decoration:none; font-size:14px; font-weight:600; }
.back-btn:hover { opacity:0.8; }
.form-badge { background:var(--accent-orange-light); color:var(--accent-orange); font-size:12px; font-weight:700; padding:3px 10px; border-radius:20px; }
.tester-badge { background:rgba(52,211,153,0.15); color:#34d399; font-size:12px; font-weight:700; padding:3px 10px; border-radius:20px; border:1px solid rgba(52,211,153,0.3); }

.btn-primary { background:var(--accent-orange); color:white; border:none; padding:9px 20px; border-radius:6px; cursor:pointer; font-family:inherit; font-size:14px; transition:background 0.2s; }
.btn-primary:hover:not(:disabled) { background:var(--accent-orange-hover); }
.btn-primary:disabled { opacity:0.6; cursor:not-allowed; }

.toast { position:fixed; top:80px; right:24px; padding:12px 20px; border-radius:8px; color:white; font-size:14px; z-index:999; }
.toast.success { background:#2e7d32; }
.toast.error   { background:#c62828; }

.state-msg { text-align:center; padding:60px; color:var(--text-muted); font-size:15px; }

/* ── Summary card ── */
.summary-card {
  background:var(--bg-card, #fff); border-radius:12px; padding:20px 24px;
  border:1px solid var(--border-divider); box-shadow:var(--shadow-card);
  margin-bottom:20px;
}
.summary-header { display:flex; gap:32px; flex-wrap:wrap; }
.summary-title { font-size:13px; color:var(--text-muted); font-weight:500; }
.summary-number { font-size:20px; font-weight:700; color:var(--accent-orange); font-family:monospace; }
.summary-meta { display:flex; flex-direction:column; gap:6px; min-width:220px; }
.meta-row { display:flex; gap:8px; font-size:13px; }
.meta-label { color:var(--text-muted); min-width:130px; }
.meta-val { color:var(--text-label); font-weight:500; }
.type-chip { background:var(--accent-orange-light); color:var(--accent-orange); font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px; margin-right:4px; }

/* ── Section titles ── */
.section-title { font-size:16px; font-weight:700; color:var(--text-label); margin:0 0 4px; }
.section-hint { font-size:13px; color:var(--text-muted); margin:0 0 16px; }

/* ── Upload section ── */
.upload-section {
  background:var(--bg-card); border-radius:12px; padding:24px;
  border:1px solid var(--border-divider); box-shadow:var(--shadow-card);
  margin-bottom:20px;
}
.empty-params { color:var(--text-muted); font-size:14px; padding:16px 0; }

.param-card {
  display:flex; justify-content:space-between; align-items:center;
  padding:14px 16px; border-radius:8px; margin-bottom:10px;
  border:1px solid var(--border-light);
  background:var(--bg-section); gap:16px; flex-wrap:wrap;
  transition:border-color 0.2s;
}
.param-card:has(.file-info) { border-color:rgba(52,211,153,0.4); background:rgba(52,211,153,0.04); }
.param-card-left { flex:1; min-width:200px; }
.param-name { font-size:14px; font-weight:600; color:var(--text-label); margin-bottom:6px; }

.file-info { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.file-icon { font-size:16px; }
.file-name { font-size:13px; color:var(--text-secondary); max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.btn-preview { font-size:12px; color:var(--accent-green); text-decoration:none; font-weight:600; padding:2px 8px; border-radius:4px; background:var(--accent-green-light); }
.btn-preview:hover { opacity:0.8; }
.btn-remove { font-size:12px; color:#c62828; background:none; border:none; cursor:pointer; font-family:inherit; padding:2px 6px; }
.btn-remove:hover { text-decoration:underline; }

.no-file { font-size:13px; color:var(--text-placeholder); font-style:italic; }

.param-card-right { flex-shrink:0; }
.upload-label {
  display:inline-flex; align-items:center; gap:6px;
  padding:8px 16px; border-radius:6px; cursor:pointer;
  border:1.5px dashed var(--border); background:var(--bg-card);
  transition:border-color 0.2s, background 0.2s;
}
.upload-label:hover { border-color:var(--accent-orange); background:var(--accent-orange-light); }
.upload-label.uploaded { border-style:solid; border-color:rgba(52,211,153,0.5); }
.file-input { display:none; }
.upload-btn-text { font-size:13px; font-weight:600; color:var(--text-label); white-space:nowrap; }

.upload-summary { margin-top:16px; padding-top:16px; border-top:1px solid var(--border-divider); text-align:right; font-size:13px; font-weight:600; }
.summary-ok   { color:#2e7d32; }
.summary-warn { color:#e65100; }

/* ── Result card ── */
.result-card {
  background:var(--bg-card); border-radius:12px; padding:24px;
  border:1px solid var(--border-divider); box-shadow:var(--shadow-card);
}
.result-readonly { display:flex; align-items:center; gap:12px; margin:14px 0; }
.result-text.pending { color:var(--text-muted); background:var(--bg-section); }
.readonly-row { display:flex; gap:8px; align-items:center; margin-bottom:8px; font-size:14px; }
.readonly-label { color:var(--text-muted); font-weight:600; min-width:100px; }
.readonly-val { color:var(--text-label); font-weight:500; }
.readonly-sig { padding-top:12px; border-top:1px solid var(--border-divider); flex-direction:column; align-items:flex-start; gap:4px; }
.result-options { display:flex; gap:32px; margin:14px 0; }
.result-opt { display:flex; align-items:center; gap:8px; cursor:pointer; }
.result-opt input[type="radio"] { width:18px; height:18px; cursor:pointer; }
.result-text { font-size:15px; font-weight:600; padding:4px 16px; border-radius:4px; }
.result-text.pass { color:var(--result-pass-text); background:var(--result-pass-bg); }
.result-text.fail { color:var(--result-fail-text); background:var(--result-fail-bg); }
.fail-remark { display:flex; gap:12px; align-items:flex-start; margin-bottom:12px; }
.fail-remark label { font-size:13px; font-weight:600; padding-top:6px; white-space:nowrap; color:var(--text-label); }
.input-textarea { border:1px solid var(--border-light); border-radius:4px; padding:6px 8px; font-size:14px; font-family:inherit; outline:none; width:100%; resize:vertical; background:var(--bg-card); color:var(--text-primary); }
.input-textarea:focus { border-color:var(--accent-orange); }
.sig-row { display:flex; gap:24px; align-items:center; flex-wrap:wrap; padding-top:12px; border-top:1px solid var(--border-divider); }
.field-group { display:flex; align-items:center; gap:8px; }
.field-group label { font-size:13px; font-weight:600; white-space:nowrap; color:var(--text-label); }
.input-field { border:none; border-bottom:1.5px solid var(--border); background:var(--bg-input); padding:4px 6px; font-size:14px; font-family:inherit; outline:none; color:var(--text-primary); }
.input-field:focus { border-bottom-color:var(--accent-orange); }
</style>
