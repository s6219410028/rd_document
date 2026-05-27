<template>
  <div>
    <!-- Action bar -->
    <div class="action-bar no-print">
      <div class="action-left">
        <router-link to="/" class="back-btn">← หน้าหลัก</router-link>
        <span class="form-badge">F-RD-FD-012 REV.00</span>
        <span v-if="editId" class="edit-badge">แก้ไข #{{ editId }}</span>
      </div>
      <div class="action-right">
      </div>
    </div>

    <div v-if="toast" class="toast" :class="toast.type">{{ toast.msg }}</div>

    <!-- FORM -->
    <div class="form-card">
      <!-- Header -->
      <div class="form-header">
        <div class="company-name">T.MAN PHARMA CO., LTD.</div>
        <div class="form-title-block">
          <div class="form-title-th">แบบฟอร์มการศึกษาความคงสภาพ</div>
          <div class="form-title-th">ของผลิตภัณฑ์</div>
        </div>
        <div class="form-number-block">
          <div class="form-number">F-RD-FD-012 REV.00</div>
        </div>
      </div>

      <div class="section-title">Stability program</div>

      <!-- Product rows -->
      <div class="field-section">
        <div v-for="(p, i) in form.products" :key="i" class="product-row">
          <div class="field-group">
            <label>Product name:</label>
            <select v-model="p.product_name" class="dropdown-field">
              <option value="">Select a product</option>
              <option v-for="product in products" :key="product.id" :value="product.name">
                {{ product.name }}
              </option>
            </select>
          </div>
          <div class="field-group sm">
            <label>Lot no.:</label>
            <input v-model="p.lot_no" type="text" />
          </div>
          <div class="field-group sm">
            <label>Mfg. date:</label>
            <DateInput v-model="p.mfg_date" />
          </div>
          <div class="field-group sm">
            <label>Exp. date:</label>
            <DateInput v-model="p.exp_date" />
          </div>
        </div>
      </div>

      <!-- Composition -->
      <div class="field-row">
        <label class="field-label">Composition:</label>
        <textarea v-model="form.composition" class="input-textarea" rows="2"></textarea>
      </div>

      <!-- Packaging -->
      <div class="field-row">
        <label class="field-label">Packaging:</label>
        <input v-model="form.packaging" type="text" class="input-field flex-1" />
      </div>

      <!-- Purpose -->
      <div class="field-row">
        <label class="field-label">Purpose:</label>
        <input v-model="form.purpose" type="text" class="input-field flex-1" />
      </div>

      <!-- Type of Test -->
      <div class="field-row checkbox-row">
        <label class="field-label">Type of Test:</label>
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.test_type.accelerated" />
          Accelerated
        </label>
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.test_type.long_term" />
          Long term
        </label>
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.test_type.others" />
          Others
        </label>
      </div>

      <!-- Time Station Grid -->
      <div class="grid-section">
        <div class="station-label-row">
          <div class="row-label">Station (Time)</div>
          <div v-for="s in STATIONS" :key="s" class="station-col">{{ s }}</div>
        </div>

        <!-- Initial dates row -->
        <div class="date-row">
          <div class="row-label">
            Initial
            <input v-model="form.initial_label" type="text" class="label-input" placeholder="label" />
          </div>
          <div v-for="(_, i) in STATIONS" :key="i" class="station-col">
            <DateInput v-model="form.initial_dates[i]" />
          </div>
        </div>

        <!-- Long term dates row -->
        <div class="date-row">
          <div class="row-label">Long term</div>
          <div v-for="(_, i) in STATIONS" :key="i" class="station-col">
            <DateInput v-model="form.longterm_dates[i]" />
          </div>
        </div>

        <!-- Temperature header -->
        <div class="temp-header-row">
          <div class="row-label">Temperature (°C) / %RH</div>
        </div>

        <!-- 40/75 row -->
        <div class="check-row">
          <div class="row-label temp-label">40/75</div>
          <div v-for="(_, i) in STATIONS" :key="i" class="station-col">
            <input type="checkbox" v-model="form.temp_4075[i]" />
          </div>
        </div>

        <!-- 30/75 row -->
        <div class="check-row">
          <div class="row-label temp-label">30/75</div>
          <div v-for="(_, i) in STATIONS" :key="i" class="station-col">
            <input type="checkbox" v-model="form.temp_3075[i]" />
          </div>
        </div>

        <!-- Extra condition rows -->
        <div v-for="(cond, ci) in form.extra_conditions" :key="ci" class="check-row">
          <div class="row-label">
            <input v-model="cond.label" type="text" class="label-input" placeholder="°C / %RH" />
          </div>
          <div v-for="(_, i) in STATIONS" :key="i" class="station-col">
            <input type="checkbox" v-model="cond.values[i]" />
          </div>
        </div>
        <div class="no-print" style="margin-top:8px">
          <button class="btn-tiny" @click="addCondition">+ เพิ่มเงื่อนไขอุณหภูมิ</button>
        </div>
      </div>

      <!-- Sample amount -->
      <div class="field-row">
        <label class="field-label">Sample amount/station:</label>
        <div class="sample-amount-row">
          <span>✓ =</span>
          <input v-model="form.sample_amount_standard" type="text" class="input-field sm-input" placeholder="เช่น 60 tablets (6 แผ่น)" />
          <span style="margin-left:16px">✓* =</span>
          <input v-model="form.sample_amount_special" type="text" class="input-field sm-input" />
        </div>
      </div>

      <!-- Total sample amount -->
      <div class="field-row">
        <label class="field-label">Total sample amount:</label>
        <input v-model="form.total_sample_amount" type="text" class="input-field flex-1" />
      </div>

      <!-- วันที่ส่ง lab row -->
      <div class="grid-section" style="margin-top:12px">
        <div class="date-row">
          <div class="row-label">วันที่ส่ง lab:</div>
          <div v-for="(_, i) in STATIONS" :key="i" class="station-col">
            <DateInput v-model="form.lab_send_dates[i]" />
          </div>
        </div>
        <div class="date-row">
          <div class="row-label">ลงชื่อ:</div>
          <div v-for="(_, i) in STATIONS" :key="i" class="station-col">
            <input v-model="form.signatures[i]" type="text" />
          </div>
        </div>
      </div>

      <!-- Study Parameter -->
      <div class="study-param-section">
        <div class="field-label" style="margin-bottom:6px">Study Parameter:</div>
        <div class="param-row">
          <span>✓ =</span>
          <input v-model="form.study_param_standard" type="text" class="input-field flex-1"
            placeholder="Appearance, Identification, Assay, Impurities, Dissolution, Content uniformity, Tablet properties..." />
        </div>
        <div class="param-row" style="margin-top:6px">
          <span>✓* =</span>
          <input v-model="form.study_param_special" type="text" class="input-field flex-1" />
        </div>
      </div>

      <!-- Remark -->
      <div class="field-row" style="margin-top:16px">
        <label class="field-label">Remark:</label>
        <div class="flex-1">
          <div v-for="(r, i) in form.remarks" :key="i" style="display:flex;gap:8px;margin-bottom:4px">
            <input v-model="form.remarks[i]" type="text" class="input-field flex-1" :placeholder="remarkPlaceholders[i]" />
          </div>
          <button class="btn-tiny no-print" @click="form.remarks.push('')">+ เพิ่มหมายเหตุ</button>
        </div>
      </div>
    </div>
  </div>
  <br>
  <div class="action-bar no-print">
    <div class="action-left">
    </div>
    <div class="action-right">
      <!-- read-only mode: save/clear disabled temporarily -->
      <!-- <button class="btn-secondary" @click="resetForm">เคลียร์ฟอร์ม</button> -->
      <button class="btn-secondary" @click="window.print()">🖨 พิมพ์</button>
      <!-- <button class="btn-primary" :disabled="saving" @click="saveForm">
        {{ saving ? 'กำลังบันทึก...' : (editId ? '💾 อัปเดต' : '💾 บันทึก') }}
      </button> -->
    </div>
  </div>
  <br>
  <br>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/index.js'
import DateInput from '../components/DateInput.vue'

const props = defineProps({ id: String })
const route = useRoute()


const STATIONS = ['1 Mo', '3 Mo', '6 Mo', '9 Mo', '12 Mo', '18 Mo', '24 Mo', '30 Mo', '36 Mo', '42 Mo', '48 Mo', '54 Mo', '60 Mo']
const N = STATIONS.length

const saving = ref(false)
const toast = ref(null)
const editId = ref(null)
const remarkPlaceholders = ["40/75: สำรอง = 6*10 'S", "30/75: สำรอง = 10*10 'S", "Retain = 227*10 'S"]

function blankForm() {
  return {
    products: [
      { product_name: '', lot_no: '', mfg_date: '', exp_date: '' },
      { product_name: '', lot_no: '', mfg_date: '', exp_date: '' },
      { product_name: '', lot_no: '', mfg_date: '', exp_date: '' },
    ],
    composition: '',
    packaging: '',
    purpose: '',
    test_type: { accelerated: false, long_term: false, others: false },
    initial_label: '',
    initial_dates: Array(N).fill(''),
    longterm_dates: Array(N).fill(''),
    temp_4075: Array(N).fill(false),
    temp_3075: Array(N).fill(false),
    extra_conditions: [],
    sample_amount_standard: '',
    sample_amount_special: '',
    total_sample_amount: '',
    lab_send_dates: Array(N).fill(''),
    signatures: Array(N).fill(''),
    study_param_standard: 'Appearance, Identification, Assay, Impurities, Dissolution, Content uniformity, Tablet properties (Weight variation, Thickness, Diameter, Hardness and Disintegration time)',
    study_param_special: '',
    remarks: ['', '', ''],
  }
}

const form = reactive(blankForm())

function resetForm() {
  Object.assign(form, blankForm())
  editId.value = null
}

function addCondition() {
  form.extra_conditions.push({ label: '', values: Array(N).fill(false) })
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
      await api.stability.update(editId.value, form)
      showToast('อัปเดตสำเร็จ')
    } else {
      await api.stability.create(form)
      showToast('บันทึกสำเร็จ — กำลังเคลียร์ฟอร์ม...')
      setTimeout(() => { resetForm() }, 1500)
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
      const res = await api.stability.get(id)
      editId.value = res.id
      const d = res.form_data
      Object.keys(d).forEach(k => { if (k in form) form[k] = d[k] })
    } catch {
      showToast('ไม่พบข้อมูล', 'error')
    }
  }
})
</script>

<style scoped>
/* ── Action bar ── */
.action-bar {
  display: flex; justify-content: space-between; align-items: center;
  padding: 12px 0; margin-bottom: 16px; gap: 12px; flex-wrap: wrap;
}
.action-left, .action-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.back-btn { color: var(--c-teal); text-decoration: none; font-size: 14px; font-weight: 600; }
.back-btn:hover { opacity: 0.8; }
.form-badge  { background: var(--accent-blue-light); color: var(--accent-blue); font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.edit-badge  { background: var(--surface2); color: var(--text2); font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }

.btn-primary {
  background: var(--c-teal); color: var(--c-dark); border: none;
  padding: 9px 22px; border-radius: var(--r-sm); cursor: pointer;
  font-family: inherit; font-size: 14px; font-weight: 700;
  transition: opacity 0.2s;
}
.btn-primary:hover:not(:disabled) { opacity: 0.85; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-secondary {
  background: var(--surface); color: var(--text);
  border: 1px solid var(--border); padding: 8px 18px;
  border-radius: var(--r-sm); cursor: pointer; font-family: inherit; font-size: 14px;
  transition: background 0.2s;
}
.btn-secondary:hover { background: var(--surface2); }
.btn-tiny {
  background: rgba(0,229,160,0.08); color: var(--c-teal);
  border: 1.5px dashed var(--c-teal); padding: 4px 12px;
  border-radius: var(--r-sm); cursor: pointer; font-size: 12px; font-family: inherit; font-weight: 600;
  transition: background 0.15s;
}
.btn-tiny:hover { background: rgba(0,229,160,0.15); }

/* ── Toast ── */
.toast {
  position: fixed; top: 80px; right: 24px; padding: 12px 20px;
  border-radius: var(--r-md); color: white; font-size: 14px; z-index: 999;
  box-shadow: var(--shadow-lg);
}
.toast.success { background: #059669; }
.toast.error   { background: #dc2626; }

/* ── Form card ── */
.form-card {
  background: var(--surface); border-radius: var(--r-lg); padding: 28px 32px;
  box-shadow: var(--shadow-sm); max-width: 1100px; margin: 0 auto;
  border: 1px solid var(--border);
  transition: background 0.25s;
}

/* ── Form header ── */
.form-header {
  display: grid; grid-template-columns: 200px 1fr 200px;
  border: 2px solid var(--border); margin-bottom: 0;
}
.company-name {
  border-right: 2px solid var(--border); padding: 12px 16px;
  font-size: 15px; font-weight: 700; display: flex; align-items: center;
  color: var(--text-label);
}
.form-title-block {
  padding: 12px 16px; text-align: center; border-right: 2px solid var(--border);
  font-size: 16px; font-weight: 700; color: var(--text-label);
}
.form-number-block { padding: 12px 16px; text-align: center; }
.form-number { font-size: 13px; font-weight: 700; color: var(--accent-blue); }
.section-title {
  font-size: 16px; font-weight: 700; text-align: center;
  padding: 10px; border: 2px solid var(--border); border-top: none; margin-bottom: 20px;
  color: var(--text-label);
}

/* ── Fields ── */
.field-section { margin-bottom: 12px; }
.product-row { display: flex; gap: 12px; margin-bottom: 8px; align-items: center; flex-wrap: wrap; }
.field-group { display: flex; align-items: center; gap: 6px; flex: 1; }
.field-group.sm { flex: 0 0 auto; min-width: 160px; }
.field-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px; }
.field-label { font-weight: 600; font-size: 14px; white-space: nowrap; min-width: 160px; padding-top: 6px; color: var(--text-label); }
label { font-size: 14px; font-weight: 500; white-space: nowrap; color: var(--text-label); }
.flex-1 { flex: 1; }

.input-field {
  border: none; border-bottom: 1.5px solid var(--border);
  background: var(--bg-input); padding: 4px 6px;
  font-size: 14px; font-family: inherit; outline: none; width: 100%;
  color: var(--text-primary);
  transition: border-color 0.2s;
}
.input-field:focus { border-bottom-color: var(--accent-blue); }
.sm-input { width: 220px; }
.input-textarea {
  border: 1px solid var(--border-light); border-radius: 4px;
  padding: 6px 8px; font-size: 14px; font-family: inherit; outline: none;
  width: 100%; resize: vertical;
  background: var(--bg-card); color: var(--text-primary);
  transition: border-color 0.2s, background 0.25s;
}
.input-textarea:focus { border-color: var(--accent-blue); }

.checkbox-row { gap: 24px; align-items: center; }
.checkbox-label { display: flex; align-items: center; gap: 6px; font-size: 14px; cursor: pointer; color: var(--text-label); }
.checkbox-label input { width: 16px; height: 16px; cursor: pointer; }

/* ── Station grid ── */
.grid-section { overflow-x: auto; margin-bottom: 12px; }
.station-label-row, .date-row, .check-row, .temp-header-row {
  display: grid;
  grid-template-columns: 150px repeat(13, 1fr);
  border-bottom: 1px solid var(--border-table);
  min-width: 900px;
}
.row-label {
  padding: 6px 8px; font-size: 13px; font-weight: 600;
  border-right: 1px solid var(--border-table);
  background: var(--bg-section); color: var(--text-label);
  display: flex; align-items: center; gap: 4px; flex-wrap: wrap;
}
.temp-label { color: var(--c-teal); }
.station-col {
  padding: 4px; font-size: 12px; text-align: center;
  border-right: 1px solid var(--border-table);
  display: flex; align-items: center; justify-content: center;
}
.station-label-row .station-col {
  background: var(--bg-station-header); font-weight: 700; font-size: 11px; padding: 6px 2px;
  color: var(--accent-blue);
}
.date-input {
  width: 100%; border: none; border-bottom: 1px dashed var(--border);
  background: transparent; font-size: 11px; padding: 2px; text-align: center;
  font-family: inherit; outline: none; color: var(--text-primary);
}
.date-input:focus { border-bottom-color: var(--c-teal); }
.label-input {
  border: none; border-bottom: 1px dashed var(--border);
  background: transparent; width: 60px; font-size: 11px; padding: 1px;
  font-family: inherit; outline: none; color: var(--text-primary);
}
.temp-header-row { background: var(--bg-hover); }
.temp-header-row .row-label { font-size: 12px; font-style: italic; background: var(--bg-hover); }
.check-row input[type="checkbox"] { width: 15px; height: 15px; cursor: pointer; }

/* ── Sample amount ── */
.sample-amount-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; color: var(--text-label); }

/* ── Study param ── */
.study-param-section { margin-bottom: 12px; }
.param-row { display: flex; align-items: center; gap: 8px; color: var(--text-label); }

@media print {
  .form-card { padding: 12px; box-shadow: none; background: white; }
  .grid-section { overflow-x: visible; }
  * { color: black !important; background: white !important; border-color: #333 !important; }
}
</style>
