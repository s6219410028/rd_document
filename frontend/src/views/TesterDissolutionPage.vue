<template>
  <div>
    <div class="action-bar no-print">
      <div class="action-left">
        <router-link to="/records" class="back-btn">← รายการทั้งหมด</router-link>
        <span class="form-badge">F-RD-FD-006 REV.00</span>
        <span class="tester-badge">🔬 ผู้ทดสอบ — #{{ formId }}</span>
      </div>
      <!-- <div class="action-right">
        <button v-if="role !== 'sender'" class="btn-primary" :disabled="saving" @click="saveResult">
          {{ saving ? 'กำลังบันทึก...' : '💾 บันทึกผล' }}
        </button>
      </div> -->
    </div>

    <div v-if="toast" class="toast" :class="toast.type">{{ toast.msg }}</div>

    <div v-if="loading" class="state-msg">กำลังโหลด...</div>
    <div v-else-if="!form" class="state-msg">ไม่พบข้อมูล</div>

    <template v-else>
      <!-- Form summary (read-only) -->
      <div class="summary-card">
        <div class="summary-top">
          <div>
            <div class="summary-title">แบบฟอร์มส่งวิเคราะห์ Dissolution profile</div>
            <div class="summary-number">{{ form.analysis_number || '-' }}</div>
          </div>
          <div class="summary-meta-right">
            <div class="meta-row"><span class="meta-label">ผู้ส่งวิเคราะห์</span><span class="meta-val">{{ form.sender
              || '-' }}</span></div>
            <div class="meta-row"><span class="meta-label">วันที่ส่ง</span><span class="meta-val">{{ form.send_date ||
              '-' }}</span></div>
            <div v-if="form.urgency_level" class="meta-row">
              <span class="meta-label">ความเร่งด่วน</span>
              <span :class="['urgency-chip', `urg-${form.urgency_level}`]">{{ URGENCY_LABELS[form.urgency_level] ||
                form.urgency_level }}</span>
            </div>
          </div>
        </div>

        <!-- Our products -->
        <div class="summary-section">
          <div class="summary-section-title">ผลิตภัณฑ์ที่ส่งวิเคราะห์</div>
          <div v-for="(p, i) in (form.our_products || [])" :key="i" class="product-row">
            <span class="product-name">{{ p.product_name || '-' }}</span>
            <span class="product-chip">Lot: {{ p.lot_no || '-' }}</span>
            <span v-if="p.mfd" class="product-chip">Mfd: {{ p.mfd }}</span>
            <span v-if="p.exp" class="product-chip">Exp: {{ p.exp }}</span>
            <span v-if="p.quantity" class="product-chip">{{ p.quantity }} ตัวอย่าง</span>
          </div>
        </div>

        <!-- Original products -->
        <div class="summary-section">
          <div class="summary-section-title">เทียบกับยา Original</div>
          <div v-for="(p, i) in (form.original_products || [])" :key="i" class="product-row">
            <span class="product-name">{{ p.product_name || '-' }}</span>
            <span class="product-chip">Lot: {{ p.lot_no || '-' }}</span>
            <span v-if="p.mfd" class="product-chip">Mfd: {{ p.mfd }}</span>
            <span v-if="p.distributor" class="product-chip">{{ p.distributor }}</span>
          </div>
        </div>

      </div>

      <!-- Test conditions (editable) -->
      <div class="conditions-card">
        <h2 class="section-title">เงื่อนไขการทดสอบ</h2>

        <div class="field-row">
          <label class="cond-label">Condition reference:</label>
          <input v-model="form.condition_reference" type="text" class="input-field" style="width:200px" />
        </div>

        <div class="field-row">
          <label class="cond-label">Medium:</label>
          <input v-model="form.medium" type="text" class="input-field" style="width:160px" />
          <label style="margin-left:8px;font-size:13px;color:var(--text3)">ปริมาตร:</label>
          <input v-model="form.medium_volume" type="text" class="input-field" style="width:80px" />
          <span class="unit-text">mL</span>
        </div>

        <div class="field-row" style="flex-wrap:wrap;gap:16px">
          <label class="cond-label">Apparatus:</label>
          <label class="radio-label">
            <input type="radio" v-model="form.apparatus" value="basket" />
            Apparatus I (Basket)
          </label>
          <label class="radio-label">
            <input type="radio" v-model="form.apparatus" value="paddle" />
            Apparatus II (Paddle)
          </label>
          <label class="radio-label">
            <input type="radio" v-model="form.apparatus" value="franz" />
            Franz diffusion cell (Model C)
          </label>
        </div>

        <div class="field-row">
          <label class="cond-label">RPM:</label>
          <input v-model="form.rpm" type="text" class="input-field" style="width:80px" />
          <label style="margin-left:16px;font-size:13px;color:var(--text3)">%Induction:</label>
          <input v-model="form.induction" type="text" class="input-field" style="width:80px" />
        </div>

        <div class="field-row">
          <label class="cond-label">Temperature:</label>
          <input v-model="form.temperature" type="text" class="input-field" style="width:80px" />
          <span class="unit-text">°C</span>
        </div>

        <div class="field-row">
          <label class="cond-label">Sampling time:</label>
          <input v-model="form.sampling_time" type="text" class="input-field" style="width:200px" />
        </div>

      </div>

      <div class="conditions-card">
        <div class="field-row" style="align-items:flex-start">
          <label class="cond-label" style="padding-top:6px">วิธีการเตรียม:</label>
          <textarea v-model="form.preparation_method" class="input-textarea" rows="3"
            style="flex:1;min-width:200px"></textarea>
        </div>

        <div class="field-row">
          <label class="cond-label">โดย:</label>
          <input v-model="form.prepared_by" type="text" class="input-field" style="width:160px" />
          <label style="margin-left:16px;font-size:13px;color:var(--text3)">วันที่:</label>
          <input v-model="form.prepared_date" type="date" class="input-field" style="width:160px" />
        </div>
      </div>

      <!-- File uploads -->
      <div class="upload-section">
        <div class="upload-header">
          <h2 class="section-title">ไฟล์รายงานผลการทดสอบ</h2>
          <label v-if="role !== 'sender'" class="btn-add-file" :class="{ uploading: !!uploading }">
            <input type="file" accept=".pdf,.jpg,.jpeg" class="file-input-hidden" @change="handleFileAdd"
              :disabled="!!uploading" />
            {{ uploading ? 'กำลังอัปโหลด...' : '+ เพิ่มไฟล์' }}
          </label>
        </div>

        <p class="upload-hint">รองรับไฟล์ PDF และ JPEG — กดปุ่มเพื่อเพิ่มได้ไม่จำกัด</p>

        <div v-if="uploads.length === 0" class="no-files">ยังไม่มีไฟล์รายงาน</div>

        <div v-else class="file-list">
          <div v-for="(u, i) in uploads" :key="u.id" class="file-item">
            <span class="file-seq">{{ i + 1 }}</span>
            <span class="file-icon-em">{{ u.original_name?.match(/\.(jpg|jpeg)$/i) ? '🖼️' : '📄' }}</span>
            <span class="file-name-text">{{ u.original_name }}</span>
            <div class="file-actions">
              <a :href="fileUrl(u.filename)" target="_blank" class="btn-file btn-open">เปิด</a>
              <a :href="fileUrl(u.filename)" :download="u.original_name" class="btn-file btn-dl">ดาวน์โหลด</a>
              <button v-if="role !== 'sender'" class="btn-file btn-remove-file" @click="removeFile(u)">✕ ลบ</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tester result card -->
      <div class="result-card">
        <h2 class="section-title">ผลการทดสอบและลายเซ็น</h2>

        <!-- Observation -->
        <div class="field-block">
          <label class="field-label">ผลจากการสังเกต:</label>
          <div v-if="role === 'sender'" class="readonly-block">{{ tester.observation || '-' }}</div>
          <textarea v-else v-model="tester.observation" class="input-textarea" rows="3"
            placeholder="บันทึกผลที่สังเกตได้..."></textarea>
        </div>
        <div class="sig-row">
          <div class="sig-group">
            <label>สังเกตโดย:</label>
            <span v-if="role === 'sender'" class="readonly-val">{{ tester.observed_by || '-' }}</span>
            <input v-else v-model="tester.observed_by" type="text" class="input-field" style="width:200px" />
          </div>
          <div class="sig-group">
            <label>วันที่สังเกต:</label>
            <span v-if="role === 'sender'" class="readonly-val">{{ tester.observed_date || '-' }}</span>
            <input v-else v-model="tester.observed_date" type="date" class="input-field" style="width:160px" />
          </div>
        </div>

        <hr class="divider" />

        <!-- F2 result -->
        <div class="field-block">
          <label class="field-label f2-label">ผลที่ได้ F₂ =</label>
          <div v-if="role === 'sender'" class="readonly-block f2-readonly">{{ tester.f2_result || '-' }}</div>
          <textarea v-else v-model="tester.f2_result" class="input-textarea" rows="3"
            placeholder="ระบุค่า F₂ และหมายเหตุ..."></textarea>
        </div>

        <hr class="divider" />

        <!-- Analyst signature -->
        <div class="sig-row">
          <div class="sig-group">
            <label>ผู้วิเคราะห์:</label>
            <span v-if="role === 'sender'" class="readonly-val">{{ tester.analyst || '-' }}</span>
            <input v-else v-model="tester.analyst" type="text" class="input-field" style="width:200px" />
          </div>
          <div class="sig-group">
            <label>วันที่วิเคราะห์:</label>
            <span v-if="role === 'sender'" class="readonly-val">{{ tester.analysis_date || '-' }}</span>
            <input v-else v-model="tester.analysis_date" type="date" class="input-field" style="width:160px" />
          </div>
        </div>
      </div>
    </template>
    <div class="action-bar no-print">
      <div class="action-left">
        <!-- <router-link to="/records" class="back-btn">← รายการทั้งหมด</router-link>
        <span class="form-badge">F-RD-FD-006 REV.00</span>
        <span class="tester-badge">🔬 ผู้ทดสอบ — #{{ formId }}</span> -->
      </div>
      <div class="action-right">
        <button v-if="role !== 'sender'" class="btn-primary" :disabled="saving" @click="saveResult">
          {{ saving ? 'กำลังบันทึก...' : '💾 บันทึกผล' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/index.js'
import { useRole } from '../composables/useRole.js'

const URGENCY_LABELS = { '3': 'สูง (High)', '2': 'ปานกลาง (Medium)', '1': 'ต่ำ (Low)' }

const props = defineProps({ id: String })
const route = useRoute()
const { role } = useRole()

const formId = computed(() => props.id || route.params.id)
const loading = ref(true)
const saving = ref(false)
const toast = ref(null)
const form = ref(null)
const uploads = ref([])
const uploading = ref(false)

const tester = reactive({
  observation: '',
  observed_by: '',
  observed_date: '',
  f2_result: '',
  analyst: '',
  analysis_date: '',
})

function fileUrl(filename) {
  return api.uploads.fileUrl(filename)
}

function showToast(msg, type = 'success') {
  toast.value = { msg, type }
  setTimeout(() => { toast.value = null }, 3000)
}

async function handleFileAdd(event) {
  const file = event.target.files[0]
  if (!file) return
  uploading.value = true
  try {
    const paramKey = `f${Date.now()}`
    await api.uploads.upload('dissolution', formId.value, paramKey, 'รายงานผลการทดสอบ', file)
    uploads.value = await api.uploads.list('dissolution', formId.value)
    showToast('อัปโหลดสำเร็จ')
  } catch {
    showToast('อัปโหลดไม่สำเร็จ กรุณาตรวจสอบไฟล์', 'error')
  }
  uploading.value = false
  event.target.value = ''
}

async function removeFile(u) {
  if (!confirm('ยืนยันการลบไฟล์นี้?')) return
  try {
    await api.uploads.delete(u.id)
    uploads.value = await api.uploads.list('dissolution', formId.value)
    showToast('ลบไฟล์สำเร็จ')
  } catch {
    showToast('ลบไม่สำเร็จ', 'error')
  }
}

async function saveResult() {
  saving.value = true
  try {
    const updated = {
      ...form.value,
      observation: tester.observation,
      observed_by: tester.observed_by,
      observed_date: tester.observed_date,
      f2_result: tester.f2_result,
      analyst: tester.analyst,
      analysis_date: tester.analysis_date,
    }
    await api.dissolution.update(formId.value, updated)
    form.value = updated
    showToast('บันทึกผลสำเร็จ')
  } catch {
    showToast('เกิดข้อผิดพลาด กรุณาลองใหม่', 'error')
  }
  saving.value = false
}

onMounted(async () => {
  try {
    const res = await api.dissolution.get(formId.value)
    form.value = res.form_data
    // Migrate apparatus from old object format {basket,paddle,franz} to string
    const ap = form.value.apparatus
    if (ap && typeof ap === 'object') {
      form.value.apparatus = ap.basket ? 'basket' : ap.paddle ? 'paddle' : ap.franz ? 'franz' : ''
    }
    tester.observation = res.form_data.observation || ''
    tester.observed_by = res.form_data.observed_by || ''
    tester.observed_date = res.form_data.observed_date || ''
    tester.f2_result = res.form_data.f2_result || ''
    tester.analyst = res.form_data.analyst || ''
    tester.analysis_date = res.form_data.analysis_date || ''
    uploads.value = await api.uploads.list('dissolution', formId.value)
  } catch {
    form.value = null
  }
  loading.value = false
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

.tester-badge {
  background: rgba(52, 211, 153, 0.15);
  color: #34d399;
  font-size: 12px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
  border: 1px solid rgba(52, 211, 153, 0.3);
}

.btn-primary {
  background: var(--accent-green);
  color: white;
  border: none;
  padding: 9px 20px;
  border-radius: 6px;
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

.toast {
  position: fixed;
  top: 80px;
  right: 24px;
  padding: 12px 20px;
  border-radius: 8px;
  color: white;
  font-size: 14px;
  z-index: 999;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.toast.success {
  background: #059669;
}

.toast.error {
  background: #dc2626;
}

.state-msg {
  text-align: center;
  padding: 60px;
  color: var(--text3);
  font-size: 15px;
}

/* ── Summary card ── */
.summary-card {
  background: var(--surface);
  border-radius: 12px;
  padding: 24px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  margin-bottom: 20px;
}

.summary-top {
  display: flex;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.summary-title {
  font-size: 13px;
  color: var(--text3);
  font-weight: 500;
  margin-bottom: 4px;
}

.summary-number {
  font-size: 22px;
  font-weight: 700;
  color: var(--accent-green);
  font-family: monospace;
}

.summary-meta-right {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.summary-section {
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--border);
}

.summary-section:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.summary-section-title {
  font-size: 12px;
  font-weight: 700;
  color: var(--accent-green);
  border-left: 3px solid var(--accent-green);
  padding: 3px 8px;
  background: var(--accent-green-light);
  border-radius: 0 4px 4px 0;
  margin-bottom: 10px;
  display: inline-block;
}

.product-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: baseline;
  margin-bottom: 6px;
}

.product-row:last-child {
  margin-bottom: 0;
}

.product-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--text);
}

.product-chip {
  font-size: 12px;
  color: var(--text3);
  background: var(--surface2);
  padding: 2px 8px;
  border-radius: 10px;
  white-space: nowrap;
}

.condition-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 6px 16px;
}

.meta-row {
  display: flex;
  gap: 8px;
  font-size: 13px;
  align-items: baseline;
}

.meta-label {
  color: var(--text3);
  min-width: 120px;
  flex-shrink: 0;
  font-weight: 500;
}

.meta-val {
  color: var(--text);
  font-weight: 500;
}

.prep-row {
  margin-top: 8px;
  align-items: flex-start;
}

.prep-text {
  white-space: pre-wrap;
  line-height: 1.5;
}

.urgency-chip {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 10px;
  border-radius: 10px;
}

.urg-3 {
  background: #fef2f2;
  color: #dc2626;
}

.urg-2 {
  background: #fffbeb;
  color: #d97706;
}

.urg-1 {
  background: #f0fdf4;
  color: #16a34a;
}

/* ── Upload section ── */
.upload-section {
  background: var(--surface);
  border-radius: 12px;
  padding: 24px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  margin-bottom: 20px;
}

.upload-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
  flex-wrap: wrap;
  gap: 10px;
}

.section-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text);
  margin: 0;
}

.upload-hint {
  font-size: 12px;
  color: var(--text3);
  margin: 0 0 16px;
}

.btn-add-file {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 18px;
  border-radius: 6px;
  cursor: pointer;
  background: var(--accent-green);
  color: white;
  font-size: 13px;
  font-weight: 700;
  font-family: inherit;
  transition: opacity 0.2s;
  white-space: nowrap;
  user-select: none;
}

.btn-add-file:hover:not(.uploading) {
  opacity: 0.85;
}

.btn-add-file.uploading {
  opacity: 0.55;
  cursor: not-allowed;
}

.file-input-hidden {
  display: none;
}

.no-files {
  color: var(--text3);
  font-size: 14px;
  font-style: italic;
  padding: 12px 0;
}

.file-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.file-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--surface2);
  flex-wrap: wrap;
  transition: border-color 0.15s;
}

.file-item:hover {
  border-color: var(--accent-green);
}

.file-seq {
  font-size: 11px;
  font-weight: 700;
  color: var(--text3);
  min-width: 20px;
}

.file-icon-em {
  font-size: 16px;
  flex-shrink: 0;
}

.file-name-text {
  font-size: 13px;
  color: var(--text);
  flex: 1;
  min-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-actions {
  display: flex;
  gap: 6px;
  flex-shrink: 0;
  flex-wrap: wrap;
}

.btn-file {
  padding: 4px 12px;
  border-radius: 20px;
  border: 1px solid var(--border);
  font-size: 12px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  text-decoration: none;
  white-space: nowrap;
  transition: background 0.15s;
  background: var(--surface);
}

.btn-open {
  color: var(--accent-green);
  border-color: var(--accent-green);
}

.btn-open:hover {
  background: var(--accent-green-light);
}

.btn-dl {
  color: var(--c-blue, #2563eb);
  border-color: var(--c-blue, #2563eb);
}

.btn-dl:hover {
  background: #eff6ff;
}

.btn-remove-file {
  color: #dc2626;
  border-color: #dc2626;
}

.btn-remove-file:hover {
  background: #fef2f2;
}

/* ── Result card ── */
.result-card {
  background: var(--surface);
  border-radius: 12px;
  padding: 24px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}

.field-block {
  margin-bottom: 12px;
}

.field-label {
  font-size: 13px;
  font-weight: 700;
  color: var(--text);
  display: block;
  margin-bottom: 6px;
}

.f2-label {
  font-size: 15px;
}

.input-textarea {
  border: 1px solid var(--border);
  border-radius: 6px;
  padding: 8px 10px;
  font-size: 14px;
  font-family: inherit;
  outline: none;
  width: 100%;
  resize: vertical;
  background: var(--surface2);
  color: var(--text);
  transition: border-color 0.2s;
}

.input-textarea:focus {
  border-color: var(--accent-green);
}

.input-field {
  border: none;
  border-bottom: 1.5px solid var(--border);
  background: transparent;
  padding: 4px 6px;
  font-size: 14px;
  font-family: inherit;
  outline: none;
  color: var(--text);
  transition: border-color 0.2s;
}

.input-field:focus {
  border-bottom-color: var(--accent-green);
}

.readonly-block {
  padding: 10px 12px;
  border-radius: 6px;
  border: 1px solid var(--border);
  background: var(--surface2);
  font-size: 14px;
  color: var(--text);
  white-space: pre-wrap;
  min-height: 44px;
  line-height: 1.6;
}

.f2-readonly {
  font-size: 15px;
  font-weight: 600;
  color: var(--accent-green);
}

.readonly-val {
  font-size: 14px;
  font-weight: 500;
  color: var(--text);
}

.sig-row {
  display: flex;
  gap: 24px;
  align-items: center;
  flex-wrap: wrap;
  margin: 12px 0;
}

.sig-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.sig-group label {
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
  color: var(--text3);
}

.divider {
  border: none;
  border-top: 1px solid var(--border);
  margin: 16px 0;
}

/* ── Conditions card ── */
.conditions-card {
  background: var(--surface);
  border-radius: 12px;
  padding: 24px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  margin-bottom: 20px;
}

.field-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.cond-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text3);
  min-width: 160px;
  flex-shrink: 0;
}

.radio-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  cursor: pointer;
}

.unit-text {
  font-size: 13px;
  color: var(--text3);
}
</style>
