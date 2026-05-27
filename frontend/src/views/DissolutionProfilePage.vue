<template>
  <div>
    <div class="action-bar no-print">
      <div class="action-left">
        <router-link to="/" class="back-btn">← หน้าหลัก</router-link>
        <span class="form-badge">F-RD-FD-006 REV.00</span>
        <span v-if="editId" class="edit-badge">แก้ไข #{{ editId }}</span>
        <span v-if="editId" :class="['status-badge', `status-${form.status || 'pending'}`]">
          {{ STATUS_MAP[form.status || 'pending'].label }}
        </span>
      </div>
      <div class="action-right">
        <button v-if="canAdvance" class="btn-advance" @click="advanceStatus">
          {{ STATUS_MAP[form.status].nextLabel }}
        </button>
      </div>
    </div>

    <div v-if="toast" class="toast" :class="toast.type">{{ toast.msg }}</div>

    <div class="form-card" :class="{ 'form-locked': !isEditable }">
      <!-- Header -->
      <div class="form-header">
        <div class="company-name">T.MAN PHARMA CO., LTD.</div>
        <div class="form-title-block">
          <div>แบบฟอร์มส่งวิเคราะห์ Dissolution profile</div>
          <div>ของผลิตภัณฑ์ในขั้นตอนการวิจัยและพัฒนา</div>
        </div>
        <div class="form-number-block">F-RD-FD-006 REV.01</div>
      </div>

      <div v-if="editId && !isEditable" class="lock-notice">
        🔒 ฟอร์มนี้อยู่ในสถานะ <strong>{{ STATUS_MAP[form.status]?.label }}</strong> — ไม่สามารถแก้ไขได้
      </div>

      <!-- Analysis number -->
      <div class="top-right-field">
        <label>เลขที่ใบส่งวิเคราะห์:</label>
        <div class="run-number-group">
          <span class="run-static">RD</span>
          <input v-model="runYear" type="text" class="run-part run-auto" maxlength="2" placeholder="YY" readonly />
          <span class="run-static">-</span>
          <input v-model="runSeq" type="text" class="run-part run-auto" maxlength="3" placeholder="XX" readonly />
          <span class="run-static">/</span>
          <input v-model="runMonth" type="text" class="run-part run-auto" maxlength="2" placeholder="MM" readonly />
        </div>
      </div>

      <!-- Our products section -->
      <div class="sub-section-title">ผลิตภัณฑ์ที่ส่งวิเคราะห์</div>
      <div v-for="(p, i) in form.our_products" :key="i" class="product-entry">
        <div class="product-name-row">
          <div class="field-group" style="flex:1">
            <label>ชื่อผลิตภัณฑ์:</label>
            <input v-model="p.product_name" type="text" class="input-field flex-1" />
          </div>
          <button v-if="form.our_products.length > 1" class="btn-remove-row no-print" @click="removeOurProduct(i)"
            title="ลบแถวนี้">✕</button>
        </div>
        <div class="product-detail-row">
          <div class="field-group sm">
            <label>Lot No.:</label>
            <input v-model="p.lot_no" type="text" />
          </div>
          <div class="field-group sm">
            <label>Mfd.:</label>
            <DateInput v-model="p.mfd" />
          </div>
          <div class="field-group sm">
            <label>Exp.:</label>
            <DateInput v-model="p.exp" />
          </div>
          <div class="field-group sm">
            <label>จำนวน:</label>
            <input v-model="p.quantity" type="text" class="input-field" style="width:60px" />
            <span class="unit-text">ตัวอย่าง</span>
          </div>
        </div>
      </div>
      <div class="btn-add-row-wrap no-print">
        <button class="btn-add-row" @click="addOurProduct">+ เพิ่มผลิตภัณฑ์</button>
      </div>

      <hr class="divider" />

      <!-- Original reference products -->
      <div class="sub-section-title">เทียบกับยา Original</div>
      <div v-for="(p, i) in form.original_products" :key="i" class="product-entry">
        <div class="product-name-row">
          <div class="field-group" style="flex:1">
            <label>ชื่อผลิตภัณฑ์:</label>
            <input v-model="p.product_name" type="text" class="input-field flex-1" />
          </div>
          <button v-if="form.original_products.length > 1" class="btn-remove-row no-print"
            @click="removeOriginalProduct(i)" title="ลบแถวนี้">✕</button>
        </div>
        <div class="product-detail-row">
          <div class="field-group sm">
            <label>Lot No.:</label>
            <input v-model="p.lot_no" type="text" />
          </div>
          <div class="field-group sm">
            <label>Mfd.:</label>
            <DateInput v-model="p.mfd" />
          </div>
          <div class="field-group sm">
            <label>Exp.:</label>
            <DateInput v-model="p.exp" />
          </div>
          <div class="field-group sm">
            <label>จำนวน:</label>
            <input v-model="p.quantity" type="text" class="input-field" style="width:60px" />
            <span class="unit-text">ตัวอย่าง</span>
          </div>
        </div>
        <div class="product-detail-row">
          <div class="field-group">
            <label>Distributor:</label>
            <input v-model="p.distributor" type="text" class="input-field flex-1" />
          </div>
          <div class="field-group">
            <label>Manufacturer:</label>
            <input v-model="p.manufacturer" type="text" class="input-field flex-1" />
          </div>
        </div>
      </div>
      <div class="btn-add-row-wrap no-print">
        <button class="btn-add-row" @click="addOriginalProduct">+ เพิ่มยา Original</button>
      </div>

      <!-- Condition Box -->
      <div class="condition-box">
        <div class="field-row">
          <label class="field-label">Condition : Reference</label>
          <input v-model="form.condition_reference" type="text" class="input-field flex-1" />
        </div>
        <div class="field-row">
          <label class="field-label">Medium:</label>
          <input v-model="form.medium" type="text" class="input-field flex-1" />
          <label style="margin-left:16px">ปริมาตร:</label>
          <input v-model="form.medium_volume" type="text" class="input-field" style="width:80px" />
          <span class="unit-text">mL</span>
        </div>

        <div class="field-row checkbox-row" style="flex-wrap:wrap;gap:16px">
          <label class="field-label">Apparatus:</label>
          <label class="checkbox-label">
            <input type="radio" v-model="form.apparatus" value="basket" />
            Apparatus I (Basket)
          </label>
          <label class="checkbox-label">
            <input type="radio" v-model="form.apparatus" value="paddle" />
            Apparatus II (Paddle)
          </label>
          <label class="checkbox-label">
            <input type="radio" v-model="form.apparatus" value="franz" />
            Franz diffusion cell (Model C)
          </label>
        </div>
        <div class="field-row">
          <label class="field-label">RPM:</label>
          <input v-model="form.rpm" type="text" class="input-field" style="width:80px" />
          <label style="margin-left:16px">%Induction:</label>
          <input v-model="form.induction" type="text" class="input-field" style="width:80px" />
          <label style="margin-left:16px">Temperature:</label>
          <input v-model="form.temperature" type="text" class="input-field" style="width:80px" />
          <span class="unit-text">°C</span>
        </div>
        <div class="field-row">
          <label class="field-label">Sampling time:</label>
          <input v-model="form.sampling_time" type="text" class="input-field flex-1" />
        </div>
      </div>
      <hr class="divider" />
      <div class="field-row" style="margin-top:16px">
        <label class="field-label">วิธีการเตรียม:</label>
        <textarea v-model="form.preparation_method" class="input-textarea flex-1" rows="2"></textarea>
      </div>
      <div class="condition-footer">
        <div class="field-group" style="margin-top:16px">
          <label>โดย:</label>
          <input v-model="form.prepared_by" type="text" class="input-field" style="width:180px" />
        </div>
        <div class="field-group">
          <label>วันที่ทำ:</label>
          <DateInput v-model="form.prepared_date" />
        </div>
      </div>


      <!-- Sender info -->
      <div class="sender-row" style="margin-top:16px">
        <div class="field-group" style="margin-top:16px">
          <label>ผู้ส่งวิเคราะห์:</label>
          <input v-model="form.sender" type="text" class="input-field" style="width:180px" />
        </div>
        <div class="field-group">
          <label>วันที่ส่งวิเคราะห์:</label>
          <DateInput v-model="form.send_date" @update:modelValue="onSendDateChange" />
        </div>
      </div>

      <hr class="divider" />

      <!-- Observation -->
      <div class="field-row" style="margin-top:16px">
        <label class="field-label">ผลจากการสังเกต:</label>
        <textarea v-model="form.observation" class="input-textarea flex-1" rows="3"></textarea>
      </div>
      <div class="sender-row" style="margin-top:4px">
        <div class="field-group">
          <label>สังเกตโดย:</label>
          <input v-model="form.observed_by" type="text" class="input-field" style="width:180px" />
        </div>
        <div class="field-group">
          <label>วันที่สังเกต:</label>
          <DateInput v-model="form.observed_date" />
        </div>
      </div>

      <hr class="divider" />

      <!-- Result -->
      <div class="result-row">
        <label class="result-label">ผลที่ได้ F₂ =</label>
        <!-- <input v-model="form.f2_result" type="text" class="input-field flex-1" /> -->
        <textarea v-model="form.f2_result" class="input-textarea flex-1" rows="3"></textarea>
      </div>


      <!-- Analyst signature -->
      <div class="signature-row">
        <div class="field-group">
          <label>ผู้วิเคราะห์:</label>
          <input v-model="form.analyst" type="text" class="input-field" style="width:200px" />
        </div>
        <div class="field-group">
          <label>วันที่วิเคราะห์:</label>
          <DateInput v-model="form.analysis_date" />
        </div>
      </div>
    </div>
  </div>
  <br>
  <div class="action-bar no-print">
    <div class="action-left">
    </div>
    <div class="action-right">

      <button class="btn-secondary" @click="resetForm">เคลียร์ฟอร์ม</button>
      <!-- <button class="btn-secondary" @click="window.print()">🖨 พิมพ์</button> -->
      <div class="urgency-group">
        <label class="urgency-label">ระดับความเร่งด่วน:</label>
        <select v-model="form.urgency_level" class="urgency-select" :class="urgencyClass">
          <option value="">-- เลือก --</option>
          <option value="3">3 สูง (High)</option>
          <option value="2">2 ปานกลาง (Medium)</option>
          <option value="1">1 ต่ำ (Low)</option>
        </select>
      </div>
      <button v-if="isEditable" class="btn-primary" :disabled="saving" @click="saveForm">
        {{ saving ? 'กำลังบันทึก...' : (editId ? '💾 อัปเดต' : '💾 บันทึก') }}
      </button>

    </div>
  </div>
  <br>
  <br>
  <br>

</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/index.js'
import { useRole } from '../composables/useRole.js'
import DateInput from '../components/DateInput.vue'

const STATUS_MAP = {
  pending: { label: 'ส่งวิเคราะห์', next: 'in_progress', nextLabel: 'รับงาน →' },
  in_progress: { label: 'รอผลการวิเคราะห์', next: 'pending_rd', nextLabel: 'ส่งผล →' },
  pending_rd: { label: 'รอรับผล', next: 'complete', nextLabel: 'รับผลแล้ว ✓' },
  complete: { label: 'รับผลเรียบร้อย', next: null, nextLabel: null },
}

const props = defineProps({ id: String })
const route = useRoute()

const saving = ref(false)
const toast = ref(null)
const editId = ref(null)

const urgencyClass = computed(() => {
  if (form.urgency_level === '3') return 'urgency-high'
  if (form.urgency_level === '2') return 'urgency-medium'
  if (form.urgency_level === '1') return 'urgency-low'
  return ''
})

const now = new Date()
const runYear = ref(String(now.getFullYear()).slice(-2))
const runSeq = ref('')
const runMonth = ref(String(now.getMonth() + 1).padStart(2, '0'))

watch([runYear, runSeq, runMonth], () => {
  form.analysis_number = runSeq.value
    ? `RD${runYear.value}-${String(runSeq.value).padStart(3, '0')}/${runMonth.value}`
    : ''
})

function blankForm() {
  return {
    analysis_number: '',
    our_products: [
      { product_name: '', lot_no: '', mfd: '', exp: '', quantity: '' },
    ],
    original_products: [
      { product_name: '', lot_no: '', mfd: '', exp: '', quantity: '', distributor: '', manufacturer: '' },
    ],
    sender: '',
    send_date: '',
    condition_reference: '',
    medium: '',
    medium_volume: '',
    preparation_method: '',
    apparatus: '',
    rpm: '',
    induction: '',
    temperature: '',
    sampling_time: '',
    prepared_by: '',
    prepared_date: '',
    observation: '',
    observed_by: '',
    observed_date: '',
    f2_result: '',
    analyst: '',
    analysis_date: '',
    urgency_level: '',
    status: 'pending',
  }
}

const form = reactive(blankForm())
const { role } = useRole()

const isEditable = computed(() => !editId.value || form.status === 'pending')
const canAdvance = computed(() =>
  !!editId.value && role.value !== 'tester' && !!STATUS_MAP[form.status || 'pending']?.next
)

async function advanceStatus() {
  const next = STATUS_MAP[form.status]?.next
  if (!next || !editId.value) return
  try {
    await api.dissolution.patch(editId.value, { status: next })
    form.status = next
    showToast('อัปเดตสถานะสำเร็จ')
  } catch {
    showToast('เกิดข้อผิดพลาด', 'error')
  }
}

function onSendDateChange(val) {
  if (editId.value) return
  if (!val) return
  const m = String(val).match(/^(\d{4})-(\d{2})/)
  if (!m) return
  const yy = m[1].slice(-2)
  const mm = m[2]
  runYear.value = yy
  runMonth.value = mm
  api.nextRunNumber(yy, mm).then(r => {
    runSeq.value = String(r.next_seq).padStart(3, '0')
  }).catch(() => { })
}

function resetForm() {
  Object.assign(form, blankForm())
  editId.value = null
  const d = new Date()
  runYear.value = String(d.getFullYear()).slice(-2)
  runSeq.value = ''
  runMonth.value = String(d.getMonth() + 1).padStart(2, '0')
}

function addOurProduct() {
  form.our_products.push({ product_name: '', lot_no: '', mfd: '', exp: '', quantity: '' })
}

function removeOurProduct(i) {
  form.our_products.splice(i, 1)
}

function addOriginalProduct() {
  form.original_products.push({ product_name: '', lot_no: '', mfd: '', exp: '', quantity: '', distributor: '', manufacturer: '' })
}

function removeOriginalProduct(i) {
  form.original_products.splice(i, 1)
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
      await api.dissolution.update(editId.value, form)
      showToast('อัปเดตสำเร็จ')
    } else {
      await api.dissolution.create(form)
      showToast('บันทึกสำเร็จ — กำลังเคลียร์ฟอร์ม...')
      setTimeout(async () => {
        resetForm()
        try {
          const r = await api.nextRunNumber()
          runYear.value = r.year
          runSeq.value = String(r.next_seq).padStart(3, '0')
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
  if (id) {
    try {
      const res = await api.dissolution.get(id)
      editId.value = res.id
      Object.keys(res.form_data).forEach(k => { if (k in form) form[k] = res.form_data[k] })
      const m = form.analysis_number?.match(/^RD(\d{2})-(\d+)\/(\d{2})$/)
      if (m) { runYear.value = m[1]; runSeq.value = String(m[2]).padStart(3, '0'); runMonth.value = m[3] }
    } catch {
      showToast('ไม่พบข้อมูล', 'error')
    }
  } else {
    try {
      const r = await api.nextRunNumber()
      runYear.value = r.year
      runSeq.value = String(r.next_seq).padStart(3, '0')
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
  background: var(--accent-green-light);
  color: var(--accent-green);
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

/* ── Status ── */
.status-badge {
  font-size: 12px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 20px;
  white-space: nowrap;
}

.status-pending {
  background: #fef3c7;
  color: #d97706;
}

.status-in_progress {
  background: #dbeafe;
  color: #2563eb;
}

.status-pending_rd {
  background: #f3e8ff;
  color: #7c3aed;
}

.status-complete {
  background: #d1fae5;
  color: #059669;
}

.btn-advance {
  padding: 6px 16px;
  border-radius: 20px;
  border: 1.5px solid var(--c-teal);
  background: rgba(0, 229, 160, 0.1);
  color: var(--c-teal);
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  font-family: inherit;
  transition: background 0.15s;
}

.btn-advance:hover {
  background: rgba(0, 229, 160, 0.22);
}

.lock-notice {
  background: #fffbeb;
  border: 1px solid #fcd34d;
  border-radius: var(--r-sm);
  padding: 10px 16px;
  font-size: 13px;
  color: #92400e;
  margin-bottom: 16px;
}

.form-card.form-locked input:not([type="file"]),
.form-card.form-locked textarea,
.form-card.form-locked select,
.form-card.form-locked .checkbox-label {
  pointer-events: none;
  opacity: 0.6;
}

.form-card.form-locked .btn-tiny,
.form-card.form-locked .remove-btn,
.form-card.form-locked .add-btn {
  display: none;
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
  margin-bottom: 16px;
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
  font-size: 15px;
  font-weight: 700;
  color: var(--text-label);
}

.form-number-block {
  padding: 12px 16px;
  text-align: center;
  font-size: 13px;
  font-weight: 700;
  color: var(--accent-green);
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── Sub sections ── */
.top-right-field {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  font-size: 14px;
  color: var(--text-label);
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
  border-bottom-color: var(--accent-green);
}

.run-auto {
  color: var(--text3);
  cursor: default;
  border-bottom-style: dashed;
}

.sub-section-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--accent-green);
  border-left: 3px solid var(--accent-green);
  padding: 6px 10px;
  margin: 12px 0 8px;
  background: var(--accent-green-light);
  border-radius: 0 var(--r-sm) var(--r-sm) 0;
}

.divider {
  border: none;
  border-top: 1px solid var(--border-divider);
  margin: 16px 0;
}

.unit-text {
  font-size: 13px;
  color: var(--text-secondary);
  white-space: nowrap;
}

/* ── Fields ── */
.product-entry {
  margin-bottom: 6px;
}

.product-entry+.product-entry {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px dashed var(--border);
}

.product-name-row {
  display: flex;
  gap: 10px;
  align-items: center;
  margin-bottom: 6px;
}

.product-detail-row {
  display: flex;
  gap: 16px;
  align-items: center;
  flex-wrap: wrap;
  margin-top: 4px;
  padding-left: 2px;
}

.btn-add-row-wrap {
  display: flex;
  justify-content: center;
  margin: 4px 0 10px;
}

.btn-add-row {
  background: none;
  border: 1.5px dashed var(--c-teal);
  color: var(--c-teal);
  padding: 5px 20px;
  border-radius: var(--r-sm);
  cursor: pointer;
  font-size: 13px;
  font-family: inherit;
  font-weight: 600;
  transition: background 0.2s;
}

.btn-add-row:hover {
  background: rgba(0, 229, 160, 0.08);
}

.btn-remove-row {
  background: none;
  border: none;
  color: var(--text3);
  cursor: pointer;
  font-size: 13px;
  padding: 2px 6px;
  border-radius: 4px;
  transition: color 0.15s;
  flex-shrink: 0;
}

.btn-remove-row:hover {
  color: #c62828;
}

.field-group {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
}

.field-group.sm {
  flex: 0 0 auto;
  min-width: 140px;
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

.flex-1 {
  flex: 1;
}

label {
  font-size: 13px;
  font-weight: 500;
  white-space: nowrap;
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
  width: 100%;
  color: var(--text-primary);
  transition: border-color 0.2s;
}

.input-field:focus {
  border-bottom-color: var(--accent-green);
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
  border-color: var(--accent-green);
}

.checkbox-row {
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  cursor: pointer;
  color: var(--text-label);
}

.checkbox-label input {
  width: 16px;
  height: 16px;
  cursor: pointer;
}

/* ── Condition box ── */
.sender-row {
  display: flex;
  gap: 24px;
  align-items: center;
  margin: 12px 0;
  flex-wrap: wrap;
}

.condition-box {
  border: 2px solid var(--border);
  border-radius: 4px;
  padding: 16px;
  margin: 16px 0;
  background: var(--bg-section);
  transition: background 0.25s, border-color 0.25s;
}

.condition-footer {
  display: flex;
  gap: 24px;
  justify-content: flex-end;
  margin-top: 8px;
  flex-wrap: wrap;
}

/* ── Result ── */
.result-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.result-label {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-label);
  white-space: nowrap;
  min-width: 140px;
}

.signature-row {
  display: flex;
  gap: 32px;
  flex-wrap: wrap;
}

/* ── Urgency ── */
.urgency-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.urgency-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-label);
  white-space: nowrap;
}

.urgency-select {
  border: 2px solid var(--border);
  border-radius: var(--r-sm);
  padding: 5px 10px;
  font-size: 13px;
  font-family: inherit;
  font-weight: 700;
  cursor: pointer;
  outline: none;
  background: var(--surface);
  color: var(--text-primary);
  transition: border-color 0.2s, background 0.2s, color 0.2s;
  min-width: 160px;
}

.urgency-select.urgency-high {
  border-color: #dc2626;
  background: #fef2f2;
  color: #dc2626;
}

.urgency-select.urgency-medium {
  border-color: #d97706;
  background: #fffbeb;
  color: #d97706;
}

.urgency-select.urgency-low {
  border-color: #16a34a;
  background: #f0fdf4;
  color: #16a34a;
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
