<template>
  <div>
    <div class="action-bar no-print">
      <div class="action-left">
        <router-link to="/" class="back-btn">← หน้าหลัก</router-link>
        <span class="form-badge">F-RD-FD-005 REV.00</span>
        <span v-if="editId" class="edit-badge">แก้ไข #{{ editId }}</span>
      </div>
    </div>

    <div v-if="toast" class="toast" :class="toast.type">{{ toast.msg }}</div>

    <div class="form-card">
      <!-- Header -->
      <div class="form-header">
        <div class="company-name">T.MAN PHARMA CO., LTD.</div>
        <div class="form-title-block">
          <div>แบบฟอร์มการตรวจสอบคุณภาพวัตถุดิบและเภสัชภัณฑ์</div>
          <div>ในขั้นตอนการวิจัยและพัฒนา</div>
        </div>
        <div class="form-number-block">F-RD-FD-005 REV.00</div>
      </div>

      <div class="section-title">การตรวจสอบคุณภาพวัตถุดิบและเภสัชภัณฑ์ในขั้นตอนการวิจัยและพัฒนา</div>

      <!-- Type selector -->
      <div class="type-selector">
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.is_raw_material" />
          วัตถุดิบ
        </label>
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.is_pharmaceutical" />
          เภสัชภัณฑ์
        </label>
        <!-- <label class="checkbox-label">
          <input type="checkbox" v-model="form.is_other_type" />
          Other:
        </label> -->
        <input v-if="form.is_other_type" v-model="form.other_type_text" type="text" class="input-field"
          style="width:140px" placeholder="ระบุ..." />
        <div class="date-fields">
          <div class="field-group">
            <label>วันที่ส่งวิเคราะห์:</label>
            <DateInput v-model="form.send_date" style="width:160px" />
          </div>
          <div class="field-group">
            <label>เลขที่ใบวิเคราะห์:</label>
            <div class="run-number-group">
              <span class="run-static">RD</span>
              <input v-model="runYear" type="text" class="run-part" maxlength="2" placeholder="YY" />
              <span class="run-static">-</span>
              <input v-model="runSeq" type="text" class="run-part" maxlength="3" placeholder="XX" />
              <span class="run-static">/</span>
              <input v-model="runMonth" type="text" class="run-part" maxlength="2" placeholder="MM" />
            </div>
          </div>
        </div>
      </div>

      <!-- Product identification -->
      <div class="product-id-section">
        <div class="field-row">
          <label class="field-label">ชื่อผลิตภัณฑ์:</label>
          <input v-model="form.product_name" type="text" class="input-field flex-1" />
          <label style="margin-left:16px">Lot No.:</label>
          <input v-model="form.lot_no" type="text" class="input-field" style="width:140px" />
          <label style="margin-left:16px">Mfd.:</label>
          <DateInput v-model="form.mfd" style="width:160px" />
        </div>
      </div>

      <!-- Details section -->
      <div class="details-section">
        <div class="details-title">รายละเอียด</div>
        <div class="detail-item">
          <div class="detail-bullet">●</div>
          <div class="detail-content">
            <div class="detail-label">ตัวยาสำคัญ (Active pharmaceutical ingredients) และความแรง (Strength)</div>
            <input v-model="form.active_ingredient" type="text" class="input-field" style="width:100%" />
          </div>
        </div>
        <div class="detail-item">
          <div class="detail-bullet">●</div>
          <div class="detail-content">
            <div class="detail-label">รูปแบบ (Dosage form)</div>
            <input v-model="form.dosage_form" type="text" class="input-field" style="width:100%" />
          </div>
        </div>
        <div class="detail-item">
          <div class="detail-bullet">●</div>
          <div class="detail-content">
            <div class="detail-label">ลักษณะ (Appearance)</div>
            <input v-model="form.appearance" type="text" class="input-field" style="width:100%" />
          </div>
        </div>
      </div>

      <hr class="divider" />

      <!-- Quality check section -->
      <div class="qc-section-title">ประเภทการวิเคราะห์</div>
      <div class="qc-type-row">
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.qc_preliminary" />
          Formulate
        </label>
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.qc_stability" />
          Stability
        </label>
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.microbiology_analysis" />
          Microbiology Analysis
        </label>
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.is_other_type" />
          Other:
        </label>
        <input v-if="form.is_other_type" v-model="form.other_type_text" type="text" class="input-field"
          style="width:140px" placeholder="ระบุ..." />
      </div>

      <!-- Quality control parameters -->
      <div class="param-section">
        <div class="param-header-row">
          <span class="param-header-label">หัวข้อในการควบคุมคุณภาพ :</span>
          <div class="param-std-row">
            <label class="checkbox-label"><input type="checkbox" v-model="form.param_std_usp" />USP</label>
            <input v-if="form.param_std_usp" v-model="form.param_std_usp_text" type="text"
              class="input-field std-text-field" placeholder="ระบุ..." />
            <label class="checkbox-label"><input type="checkbox" v-model="form.param_std_bp" />BP</label>
            <input v-if="form.param_std_bp" v-model="form.param_std_bp_text" type="text"
              class="input-field std-text-field" placeholder="ระบุ..." />
            <label class="checkbox-label"><input type="checkbox" v-model="form.param_std_inhouse" />in-house</label>
            <input v-if="form.param_std_inhouse" v-model="form.param_std_inhouse_text" type="text"
              class="input-field std-text-field" placeholder="ระบุ..." />
            <label class="checkbox-label"><input type="checkbox" v-model="form.param_std_other" />other</label>
            <input v-if="form.param_std_other" v-model="form.param_std_other_text" type="text"
              class="input-field std-text-field" placeholder="ระบุ..." />
          </div>
        </div>
        <div class="param-list">
          <div v-for="param in paramList" :key="param.key" class="param-item">
            <label class="checkbox-label param-checkbox">
              <input type="checkbox" v-model="form.params[param.key]" />
              {{ param.label }}
            </label>
          </div>
          <div v-for="(cp, i) in form.custom_params" :key="i" class="param-item">
            <label class="checkbox-label param-checkbox">
              <input type="checkbox" v-model="cp.checked" />
              <input v-model="cp.label" type="text" class="input-field" style="width:200px"
                placeholder="ชื่อหัวข้อ..." />
            </label>
            <button class="btn-remove-param no-print" @click="removeCustomParam(i)">✕</button>
          </div>
          <button class="btn-add-param no-print" @click="addCustomParam">+ เพิ่มหัวข้อ</button>
        </div>
      </div>

      <div class="field-row" style="margin:8px 0">
        <label class="field-label">ผู้ส่งวิเคราะห์:</label>
        <input v-model="form.requester" type="text" class="input-field" style="width:200px" />
      </div>

      <hr class="divider" />

      <!-- PDF reports from tester (read-only for sender) -->
      <div v-if="editId && activeParams.length > 0" class="pdf-section no-print">
        <div class="pdf-section-title">รายงาน PDF จากผู้ทดสอบ</div>
        <div v-for="param in activeParams" :key="param.key" class="pdf-row">
          <span class="pdf-param-name">{{ param.label }}</span>
          <template v-if="uploadMap[param.key]">
            <span class="pdf-filename">{{ uploadMap[param.key].original_name }}</span>
            <a
              :href="api.uploads.fileUrl(uploadMap[param.key].filename)"
              target="_blank"
              class="pdf-btn pdf-open"
            >เปิดใน Browser</a>
            <a
              :href="api.uploads.fileUrl(uploadMap[param.key].filename)"
              :download="uploadMap[param.key].original_name"
              class="pdf-btn pdf-download"
            >ดาวน์โหลด</a>
          </template>
          <span v-else class="pdf-none">ยังไม่มีรายงาน</span>
        </div>
      </div>

      <!-- Result -->
      <div class="result-section">
        <div class="result-title">Result:</div>

        <!-- Read-only for sender -->
        <template v-if="role === 'sender'">
          <div class="result-readonly">
            <span v-if="form.result === 'pass'" class="result-text pass">ผ่าน</span>
            <span v-else-if="form.result === 'fail'" class="result-text fail">ไม่ผ่าน</span>
            <span v-else class="result-text result-pending">ยังไม่มีผล</span>
          </div>
          <div v-if="form.result === 'fail' && form.fail_remark" class="readonly-row">
            <span class="readonly-label">หมายเหตุ:</span>
            <span class="readonly-val">{{ form.fail_remark }}</span>
          </div>
        </template>

        <!-- Editable for tester / no role -->
        <template v-else>
          <div class="result-options">
            <label class="result-label-option">
              <input type="radio" v-model="form.result" value="pass" />
              <span class="result-text pass">ผ่าน</span>
            </label>
            <label class="result-label-option">
              <input type="radio" v-model="form.result" value="fail" />
              <span class="result-text fail">ไม่ผ่าน</span>
            </label>
          </div>
          <div v-if="form.result === 'fail'" class="fail-remark">
            <label>หมายเหตุ:</label>
            <textarea v-model="form.fail_remark" class="input-textarea flex-1" rows="2"></textarea>
          </div>
        </template>
      </div>

      <!-- Signatures -->
      <div class="sig-section">
        <!-- Read-only for sender -->
        <template v-if="role === 'sender'">
          <div class="readonly-row">
            <span class="readonly-label">ผู้วิเคราะห์:</span>
            <span class="readonly-val">{{ form.sig_analyst || '-' }}</span>
          </div>
          <div class="readonly-row">
            <span class="readonly-label">วันที่:</span>
            <span class="readonly-val">{{ form.sig_date || '-' }}</span>
          </div>
        </template>

        <!-- Editable for tester / no role -->
        <template v-else>
          <div class="field-group">
            <label>ผู้วิเคราะห์:</label>
            <input v-model="form.sig_analyst" type="text" class="input-field" style="width:200px" />
          </div>
          <div class="field-group">
            <label>วันที่:</label>
            <DateInput v-model="form.sig_date" style="width:160px" />
          </div>
        </template>
      </div>
    </div>
  </div>
  <div class="action-bar no-print">
    <div class="action-left">
    </div>
    
    <div class="action-right">
      <button class="btn-secondary" @click="resetForm">เคลียร์ฟอร์ม</button>
      <button class="btn-primary" :disabled="saving" @click="saveForm">
        {{ saving ? 'กำลังบันทึก...' : (editId ? '💾 อัปเดต' : '💾 บันทึก') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '../api/index.js'
import { useRole } from '../composables/useRole.js'
import DateInput from '../components/DateInput.vue'

const props = defineProps({ id: String })
const route = useRoute()
const router = useRouter()
const { role } = useRole()

const saving = ref(false)
const toast = ref(null)
const editId = ref(null)
const uploads = ref([])

const uploadMap = computed(() => {
  const map = {}
  uploads.value.forEach(u => { map[u.param_key] = u })
  return map
})

const activeParams = computed(() => {
  const standard = paramList.filter(p => form.params?.[p.key])
  const custom = (form.custom_params || [])
    .filter(cp => cp.checked)
    .map((cp, i) => ({ key: `custom_${i}`, label: cp.label }))
  return [...standard, ...custom]
})

const now = new Date()
const runYear = ref(String(now.getFullYear()).slice(-2))
const runSeq = ref('')
const runMonth = ref(String(now.getMonth() + 1).padStart(2, '0'))

watch([runYear, runSeq, runMonth], () => {
  form.analysis_number = runSeq.value
    ? `RD${runYear.value}-${String(runSeq.value).padStart(2, '0')}/${runMonth.value}`
    : ''
})

const paramList = [
  { key: 'assay', label: 'Assay' },
  { key: 'identification', label: 'Identification' },
  { key: 'appearance', label: 'Appearance' },
  { key: 'dissolution', label: 'Dissolution' },
  { key: 'ph', label: 'pH' },
  { key: 'microbial', label: 'Microbial enumeration test and test for specified microorganism' },
  { key: 'other', label: 'Other' },
]

function blankForm() {
  return {
    is_raw_material: false,
    is_pharmaceutical: false,
    is_other_type: false,
    other_type_text: '',
    send_date: '',
    analysis_number: '',
    product_name: '',
    lot_no: '',
    mfd: '',
    active_ingredient: '',
    dosage_form: '',
    appearance: '',
    qc_preliminary: false,
    qc_stability: false,
    microbiology_analysis: false,
    param_std_usp: false, param_std_usp_text: '',
    param_std_bp: false, param_std_bp_text: '',
    param_std_inhouse: false, param_std_inhouse_text: '',
    param_std_other: false, param_std_other_text: '',
    custom_params: [],
    params: { assay: false, identification: false, appearance: false, dissolution: false, ph: false, microbial: false, other: false },
    param_details: { assay: '', identification: '', appearance: '', dissolution: '', ph: '', microbial: '', other: '' },
    requester: '',
    result: '',
    fail_remark: '',
    sig_sender: '',
    sig_analyst: '',
    sig_date: '',
  }
}

const form = reactive(blankForm())

function resetForm() {
  Object.assign(form, blankForm())
  editId.value = null
  const d = new Date()
  runYear.value = String(d.getFullYear()).slice(-2)
  runSeq.value = ''
  runMonth.value = String(d.getMonth() + 1).padStart(2, '0')
}

function addCustomParam() {
  form.custom_params.push({ checked: false, label: '', detail: '' })
}

function removeCustomParam(i) {
  form.custom_params.splice(i, 1)
}

function showToast(msg, type = 'success') {
  toast.value = { msg, type }
  setTimeout(() => { toast.value = null }, 3000)
}

async function saveForm() {
  if (editId.value) {
    if (!confirm(`ยืนยันการบันทึกทับข้อมูลรายการ #${editId.value}?\nข้อมูลเดิมจะถูกแทนที่และไม่สามารถกู้คืนได้`)) return
  }
  saving.value = true
  try {
    if (editId.value) {
      await api.qualityCheck.update(editId.value, form)
      showToast('อัปเดตสำเร็จ')
    } else {
      await api.qualityCheck.create(form)
      showToast('บันทึกสำเร็จ — กำลังเคลียร์ฟอร์ม...')
      setTimeout(async () => {
        resetForm()
        try {
          const r = await api.nextRunNumber()
          runYear.value = r.year
          runSeq.value = String(r.next_seq)
          runMonth.value = r.month
        } catch { /* leave defaults */ }
      }, 1500)
    }
  } catch {
    showToast('เกิดข้อผิดพลาด กรุณาตรวจสอบการเชื่อมต่อ', 'error')
  }
  saving.value = false
}

onMounted(async () => {
  const id = props.id || route.params.id
  if (id && role.value === 'tester') {
    router.replace(`/quality-check/${id}/test`)
    return
  }
  if (id) {
    try {
      const res = await api.qualityCheck.get(id)
      editId.value = res.id
      Object.keys(res.form_data).forEach(k => { if (k in form) form[k] = res.form_data[k] })
      const m = form.analysis_number?.match(/^RD(\d{2})-(\d+)\/(\d{2})$/)
      if (m) { runYear.value = m[1]; runSeq.value = m[2]; runMonth.value = m[3] }
      uploads.value = await api.uploads.list('quality_check', id)
    } catch {
      showToast('ไม่พบข้อมูล', 'error')
    }
  } else {
    try {
      const r = await api.nextRunNumber()
      runYear.value = r.year
      runSeq.value = String(r.next_seq)
      runMonth.value = r.month
    } catch { /* leave defaults */ }
  }
})
</script>

<style scoped>
/* ── Action bar ── */
.action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  margin-bottom: 16px;
  gap: 12px;
  flex-wrap: wrap;
}

.action-left,
.action-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.back-btn {
  color: var(--c-teal);
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
}

.back-btn:hover {
  opacity: 0.8;
}

.form-badge {
  background: var(--accent-orange-light);
  color: var(--accent-orange);
  font-size: 12px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
}

.edit-badge {
  background: var(--surface2);
  color: var(--text2);
  font-size: 12px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
}

.btn-primary {
  background: var(--c-teal);
  color: var(--c-dark);
  border: none;
  padding: 9px 22px;
  border-radius: var(--r-sm);
  cursor: pointer;
  font-family: inherit;
  font-size: 14px;
  font-weight: 700;
  transition: opacity 0.2s;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.85;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
  padding: 8px 18px;
  border-radius: var(--r-sm);
  cursor: pointer;
  font-family: inherit;
  font-size: 14px;
  transition: background 0.2s;
}

.btn-secondary:hover {
  background: var(--surface2);
}

/* ── Toast ── */
.toast {
  position: fixed;
  top: 80px;
  right: 24px;
  padding: 12px 20px;
  border-radius: var(--r-md);
  color: white;
  font-size: 14px;
  z-index: 999;
  box-shadow: var(--shadow-lg);
}

.toast.success {
  background: #059669;
}

.toast.error {
  background: #dc2626;
}

/* ── Form card ── */
.form-card {
  background: var(--surface);
  border-radius: var(--r-lg);
  padding: 28px 32px;
  box-shadow: var(--shadow-sm);
  max-width: 960px;
  margin: 0 auto;
  border: 1px solid var(--border);
  transition: background 0.25s;
}

/* ── Form header ── */
.form-header {
  display: grid;
  grid-template-columns: 200px 1fr 200px;
  border: 2px solid var(--border);
  margin-bottom: 0;
}

.company-name {
  border-right: 2px solid var(--border);
  padding: 12px 16px;
  font-size: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  color: var(--text-label);
}

.form-title-block {
  padding: 12px 16px;
  text-align: center;
  border-right: 2px solid var(--border);
  font-size: 14px;
  font-weight: 700;
  color: var(--text-label);
}

.form-number-block {
  padding: 12px 16px;
  text-align: center;
  font-size: 12px;
  font-weight: 700;
  color: var(--accent-orange);
  display: flex;
  align-items: center;
  justify-content: center;
}

.section-title {
  font-size: 14px;
  font-weight: 700;
  text-align: center;
  padding: 8px;
  border: 2px solid var(--border);
  border-top: none;
  margin-bottom: 16px;
  color: var(--text-label);
}

/* ── Type selector ── */
.type-selector {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 12px;
  flex-wrap: wrap;
  border: 1px solid var(--border-light);
  padding: 12px 16px;
  border-radius: 6px;
  background: var(--bg-section);
  transition: background 0.25s, border-color 0.25s;
}

.date-fields {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  margin-left: auto;
}

/* ── Fields ── */
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  cursor: pointer;
  color: var(--text-label);
}

.checkbox-label input[type="checkbox"] {
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.product-id-section {
  margin-bottom: 12px;
}

.field-row {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}

.field-label {
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
  min-width: 140px;
  padding-top: 6px;
  color: var(--text-label);
}

.field-group {
  display: flex;
  align-items: center;
  gap: 6px;
}

.flex-1 {
  flex: 1;
}

label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-label);
}

.input-field {
  border: none;
  border-bottom: 1.5px solid var(--border);
  background: var(--bg-input);
  padding: 4px 6px;
  font-size: 14px;
  font-family: inherit;
  outline: none;
  color: var(--text-primary);
  transition: border-color 0.2s;
}

.input-field:focus {
  border-bottom-color: var(--accent-orange);
}

.input-textarea {
  border: 1px solid var(--border-light);
  border-radius: 4px;
  padding: 6px 8px;
  font-size: 14px;
  font-family: inherit;
  outline: none;
  width: 100%;
  resize: vertical;
  background: var(--bg-card);
  color: var(--text-primary);
  transition: border-color 0.2s, background 0.25s;
}

.input-textarea:focus {
  border-color: var(--accent-orange);
}

/* ── Details ── */
.details-section {
  margin: 12px 0;
}

.details-title {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 8px;
  color: var(--text-label);
}

.detail-item {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
  align-items: flex-start;
}

.detail-bullet {
  font-size: 18px;
  color: var(--accent-orange);
  line-height: 1;
  margin-top: 2px;
}

.detail-content {
  flex: 1;
}

.detail-label {
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 4px;
}

.divider {
  border: none;
  border-top: 1.5px solid var(--border-divider);
  margin: 16px 0;
}

.qc-section-title {
  font-size: 14px;
  font-weight: 700;
  padding: 7px 12px;
  background: var(--surface2);
  border-left: 3px solid var(--accent-orange);
  border-radius: 0 var(--r-sm) var(--r-sm) 0;
  margin-bottom: 10px;
  color: var(--accent-orange);
  transition: background 0.25s;
}

.qc-type-row {
  display: flex;
  gap: 32px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}

.run-number-group {
  display: flex;
  align-items: center;
  gap: 2px;
}

.run-static {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-label);
  padding: 0 1px;
}

.run-part {
  border: none;
  border-bottom: 1.5px solid var(--border);
  background: var(--bg-input);
  padding: 4px 4px;
  font-size: 14px;
  font-family: inherit;
  outline: none;
  width: 30px;
  text-align: center;
  color: var(--text-primary);
  transition: border-color 0.2s;
}

.run-part:focus {
  border-bottom-color: var(--accent-orange);
}

.page-input {
  border: none;
  border-bottom: 1px solid var(--border);
  background: transparent;
  width: 60px;
  font-size: 13px;
  padding: 1px 4px;
  font-family: inherit;
  outline: none;
  margin-left: 4px;
  color: var(--text-primary);
}

.wide {
  width: 120px;
}

/* ── Params ── */
.param-section {
  border: 1px solid var(--border-light);
  border-radius: 6px;
  padding: 16px;
  margin-bottom: 12px;
  background: var(--bg-section);
  transition: background 0.25s, border-color 0.25s;
}

.param-header-row {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}

.param-header-label {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-label);
}

.param-std-row {
  display: flex;
  gap: 16px;
}

.param-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.param-item {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.param-checkbox {
  min-width: 260px;
}

.param-detail-field {
  flex: 1;
  min-width: 160px;
}

.std-text-field {
  width: 100px;
}

.btn-add-param {
  margin-top: 8px;
  background: none;
  border: 1.5px dashed var(--c-teal);
  color: var(--c-teal);
  padding: 5px 14px;
  border-radius: var(--r-sm);
  cursor: pointer;
  font-size: 13px;
  font-family: inherit;
  font-weight: 600;
  transition: background 0.2s;
}

.btn-add-param:hover {
  background: rgba(0, 229, 160, 0.08);
}

.btn-remove-param {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  font-size: 13px;
  padding: 2px 6px;
  border-radius: 4px;
  transition: color 0.2s;
}

.btn-remove-param:hover {
  color: #c62828;
}

/* ── Result ── */
.result-section {
  margin-bottom: 16px;
}

.result-title {
  font-size: 15px;
  font-weight: 700;
  margin-bottom: 10px;
  text-decoration: underline;
  color: var(--text-label);
}

.result-options {
  display: flex;
  gap: 40px;
  margin-bottom: 10px;
}

.result-label-option {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.result-label-option input[type="radio"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.result-text {
  font-size: 15px;
  font-weight: 600;
  padding: 4px 16px;
  border-radius: 4px;
}

.result-text.pass {
  color: var(--result-pass-text);
  background: var(--result-pass-bg);
}

.result-text.fail {
  color: var(--result-fail-text);
  background: var(--result-fail-bg);
}

.fail-remark {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  margin-top: 8px;
}

/* ── Signatures ── */
.sig-section {
  display: flex;
  gap: 24px;
  align-items: center;
  flex-wrap: wrap;
  padding-top: 12px;
  border-top: 1px solid var(--border-divider);
}

/* ── Read-only result / sig ── */
.result-readonly { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.result-text.result-pending { color: var(--text3); background: var(--surface2); }
.readonly-row { display: flex; gap: 8px; align-items: center; margin-bottom: 6px; font-size: 14px; }
.readonly-label { color: var(--text2); font-weight: 600; min-width: 110px; }
.readonly-val { color: var(--text); font-weight: 500; }

/* ── PDF reports section ── */
.pdf-section {
  margin-bottom: 16px;
  border: 1px solid var(--border-light);
  border-radius: 8px;
  padding: 14px 16px;
  background: var(--bg-section);
}
.pdf-section-title {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-label);
  margin-bottom: 10px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--border-divider);
}
.pdf-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 7px 0;
  border-bottom: 1px solid var(--border-light);
  flex-wrap: wrap;
}
.pdf-row:last-child { border-bottom: none; }
.pdf-param-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-label);
  min-width: 200px;
  flex-shrink: 0;
}
.pdf-filename {
  font-size: 12px;
  color: var(--text2);
  flex: 1;
  min-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.pdf-btn {
  font-size: 12px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 20px;
  text-decoration: none;
  white-space: nowrap;
  flex-shrink: 0;
  transition: opacity 0.15s;
}
.pdf-btn:hover { opacity: 0.8; }
.pdf-open     { background: var(--accent-blue-light);  color: var(--c-blue); }
.pdf-download { background: var(--accent-green-light); color: var(--accent-green); }
.pdf-none {
  font-size: 12px;
  color: var(--text3);
  font-style: italic;
}

@media print {
  .form-card {
    padding: 12px;
    box-shadow: none;
    background: white;
  }

  * {
    color: black !important;
    background: white !important;
    border-color: #333 !important;
  }
}
</style>
