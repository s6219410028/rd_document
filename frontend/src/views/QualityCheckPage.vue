<template>
  <div>
    <div class="action-bar no-print">
      <div class="action-left">
        <router-link to="/" class="back-btn">← หน้าหลัก</router-link>
        <span class="form-badge">F-RD-FD-005 REV.00</span>
        <span v-if="editId" class="edit-badge">แก้ไข #{{ editId }}</span>
      </div>
      <div class="action-right">
        <button class="btn-secondary" @click="resetForm">เคลียร์ฟอร์ม</button>
        <button class="btn-secondary" @click="window.print()">🖨 พิมพ์</button>
        <button class="btn-primary" :disabled="saving" @click="saveForm">
          {{ saving ? 'กำลังบันทึก...' : (editId ? '💾 อัปเดต' : '💾 บันทึก') }}
        </button>
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
        <div class="date-fields">
          <div class="field-group">
            <label>วันที่ส่งวิเคราะห์:</label>
            <input v-model="form.send_date" type="date" class="input-field" style="width:160px" />
          </div>
          <div class="field-group">
            <label>เลขที่ใบวิเคราะห์:</label>
            <input v-model="form.analysis_number" type="text" class="input-field" style="width:160px" />
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
          <input v-model="form.mfd" type="date" class="input-field" style="width:160px" />
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
      <div class="qc-section-title">การตรวจสอบคุณภาพ</div>
      <div class="qc-type-row">
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.qc_preliminary" />
          การตรวจสอบคุณภาพเบื้องต้น
        </label>
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.qc_stability" />
          การตรวจสอบคุณภาพเพื่อติดตามความคงสภาพ
        </label>
      </div>

      <!-- Reference standard -->
      <div class="reference-section">
        <div class="ref-col">
          <div class="ref-title">ดำเนินตามเภสัชตำรับ</div>
          <div class="std-checkboxes">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.std_usp" />
              USP หน้า <input v-model="form.std_usp_page" type="text" class="page-input" />
            </label>
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.std_bp" />
              BP หน้า <input v-model="form.std_bp_page" type="text" class="page-input" />
            </label>
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.std_other" />
              Other <input v-model="form.std_other_text" type="text" class="page-input wide" />
            </label>
          </div>
        </div>
        <div class="ref-col">
          <div class="ref-title">Microbiology Analysis</div>
          <label class="checkbox-label">
            <input type="checkbox" v-model="form.microbiology_analysis" />
            Microbiology analysis
          </label>
        </div>
      </div>

      <!-- Quality control parameters -->
      <div class="param-section">
        <div class="param-header-row">
          <span class="param-header-label">หัวข้อในการควบคุมคุณภาพ :</span>
          <div class="param-std-row">
            <label class="checkbox-label"><input type="checkbox" v-model="form.param_std_usp" />USP</label>
            <label class="checkbox-label"><input type="checkbox" v-model="form.param_std_bp" />BP</label>
            <label class="checkbox-label"><input type="checkbox" v-model="form.param_std_inhouse" />in-house</label>
            <label class="checkbox-label"><input type="checkbox" v-model="form.param_std_other" />other</label>
          </div>
        </div>
        <div class="param-list">
          <div v-for="param in paramList" :key="param.key" class="param-item">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.params[param.key]" />
              {{ param.label }}
            </label>
            <div v-if="param.hasDetail && form.params[param.key]" class="param-detail">
              <span>รายรู :</span>
              <input v-model="form.param_details[param.key]" type="text" class="input-field" style="width:200px" />
            </div>
          </div>
          <div class="param-item">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.params.other" />
              Other:
            </label>
            <input v-if="form.params.other" v-model="form.params_other_text" type="text" class="input-field" style="width:300px" />
          </div>
        </div>
      </div>

      <div class="field-row" style="margin:8px 0">
        <label class="field-label">ผู้ส่งวิเคราะห์:</label>
        <input v-model="form.requester" type="text" class="input-field" style="width:200px" />
      </div>

      <hr class="divider" />

      <!-- Result -->
      <div class="result-section">
        <div class="result-title">Result:</div>
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
      </div>

      <!-- Signatures -->
      <div class="sig-section">
        <div class="field-group">
          <label>ผู้ส่งวิเคราะห์:</label>
          <input v-model="form.sig_sender" type="text" class="input-field" style="width:200px" />
        </div>
        <div class="field-group">
          <label>ผู้วิเคราะห์:</label>
          <input v-model="form.sig_analyst" type="text" class="input-field" style="width:200px" />
        </div>
        <div class="field-group">
          <label>วันที่:</label>
          <input v-model="form.sig_date" type="date" class="input-field" style="width:160px" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '../api/index.js'

const props = defineProps({ id: String })
const route = useRoute()
const router = useRouter()

const saving = ref(false)
const toast = ref(null)
const editId = ref(null)

const paramList = [
  { key: 'assay',          label: 'Assay',          hasDetail: true  },
  { key: 'identification', label: 'Identification',  hasDetail: false },
  { key: 'appearance',     label: 'Appearance',      hasDetail: false },
  { key: 'dissolution',    label: 'Dissolution',     hasDetail: true  },
  { key: 'ph',             label: 'pH',              hasDetail: true  },
  { key: 'microbial',      label: 'Microbial enumeration test and test for specified microorganism', hasDetail: false },
]

function blankForm() {
  return {
    is_raw_material: false,
    is_pharmaceutical: false,
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
    std_usp: false, std_usp_page: '',
    std_bp: false,  std_bp_page: '',
    std_other: false, std_other_text: '',
    microbiology_analysis: false,
    param_std_usp: false, param_std_bp: false, param_std_inhouse: false, param_std_other: false,
    params: { assay: false, identification: false, appearance: false, dissolution: false, ph: false, microbial: false, other: false },
    param_details: { assay: '', dissolution: '', ph: '' },
    params_other_text: '',
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
}

function showToast(msg, type = 'success') {
  toast.value = { msg, type }
  setTimeout(() => { toast.value = null }, 3000)
}

async function saveForm() {
  saving.value = true
  try {
    if (editId.value) {
      await api.qualityCheck.update(editId.value, form)
      showToast('อัปเดตสำเร็จ')
    } else {
      const res = await api.qualityCheck.create(form)
      editId.value = res.id
      router.replace(`/quality-check/${res.id}`)
      showToast('บันทึกสำเร็จ')
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
      const res = await api.qualityCheck.get(id)
      editId.value = res.id
      Object.keys(res.form_data).forEach(k => { if (k in form) form[k] = res.form_data[k] })
    } catch {
      showToast('ไม่พบข้อมูล', 'error')
    }
  }
})
</script>

<style scoped>
/* ── Action bar ── */
.action-bar { display:flex; justify-content:space-between; align-items:center; padding:12px 0; margin-bottom:16px; gap:12px; flex-wrap:wrap; }
.action-left, .action-right { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.back-btn { color:var(--accent-orange); text-decoration:none; font-size:14px; font-weight:500; }
.back-btn:hover { text-decoration:underline; }
.form-badge  { background:var(--accent-orange-light); color:var(--accent-orange); font-size:12px; font-weight:700; padding:3px 10px; border-radius:20px; }
.edit-badge  { background:var(--accent-blue-light);   color:var(--accent-blue);   font-size:12px; font-weight:600; padding:3px 10px; border-radius:20px; }

.btn-primary {
  background:var(--accent-orange); color:white; border:none;
  padding:9px 20px; border-radius:6px; cursor:pointer; font-family:inherit; font-size:14px;
  transition:background 0.2s;
}
.btn-primary:hover:not(:disabled) { background:var(--accent-orange-hover); }
.btn-primary:disabled { opacity:0.6; cursor:not-allowed; }
.btn-secondary {
  background:var(--bg-card); color:var(--text-label);
  border:1px solid var(--border-light); padding:8px 18px;
  border-radius:6px; cursor:pointer; font-family:inherit; font-size:14px;
  transition:background 0.2s;
}
.btn-secondary:hover { background:var(--bg-section); }

/* ── Toast ── */
.toast { position:fixed; top:80px; right:24px; padding:12px 20px; border-radius:8px; color:white; font-size:14px; z-index:999; box-shadow:var(--shadow-toast); }
.toast.success { background:#2e7d32; }
.toast.error   { background:#c62828; }

/* ── Form card ── */
.form-card {
  background:var(--bg-card); border-radius:12px; padding:28px 32px;
  box-shadow:var(--shadow-card); max-width:960px; margin:0 auto;
  border:1px solid var(--border-divider);
  transition:background 0.25s;
}

/* ── Form header ── */
.form-header { display:grid; grid-template-columns:200px 1fr 200px; border:2px solid var(--border); margin-bottom:0; }
.company-name { border-right:2px solid var(--border); padding:12px 16px; font-size:14px; font-weight:700; display:flex; align-items:center; color:var(--text-label); }
.form-title-block { padding:12px 16px; text-align:center; border-right:2px solid var(--border); font-size:14px; font-weight:700; color:var(--text-label); }
.form-number-block { padding:12px 16px; text-align:center; font-size:12px; font-weight:700; color:var(--accent-orange); display:flex; align-items:center; justify-content:center; }
.section-title { font-size:14px; font-weight:700; text-align:center; padding:8px; border:2px solid var(--border); border-top:none; margin-bottom:16px; color:var(--text-label); }

/* ── Type selector ── */
.type-selector {
  display:flex; align-items:center; gap:24px; margin-bottom:12px; flex-wrap:wrap;
  border:1px solid var(--border-light); padding:12px 16px; border-radius:6px;
  background:var(--bg-section); transition:background 0.25s, border-color 0.25s;
}
.date-fields { display:flex; gap:16px; flex-wrap:wrap; margin-left:auto; }

/* ── Fields ── */
.checkbox-label { display:flex; align-items:center; gap:6px; font-size:14px; cursor:pointer; color:var(--text-label); }
.checkbox-label input[type="checkbox"] { width:16px; height:16px; cursor:pointer; }
.product-id-section { margin-bottom:12px; }
.field-row { display:flex; align-items:flex-start; gap:12px; margin-bottom:10px; flex-wrap:wrap; }
.field-label { font-weight:600; font-size:14px; white-space:nowrap; min-width:140px; padding-top:6px; color:var(--text-label); }
.field-group { display:flex; align-items:center; gap:6px; }
.flex-1 { flex:1; }
label { font-size:13px; font-weight:500; color:var(--text-label); }

.input-field {
  border:none; border-bottom:1.5px solid var(--border);
  background:var(--bg-input); padding:4px 6px;
  font-size:14px; font-family:inherit; outline:none;
  color:var(--text-primary);
  transition:border-color 0.2s;
}
.input-field:focus { border-bottom-color:var(--accent-orange); }
.input-textarea {
  border:1px solid var(--border-light); border-radius:4px; padding:6px 8px;
  font-size:14px; font-family:inherit; outline:none; width:100%; resize:vertical;
  background:var(--bg-card); color:var(--text-primary);
  transition:border-color 0.2s, background 0.25s;
}
.input-textarea:focus { border-color:var(--accent-orange); }

/* ── Details ── */
.details-section { margin:12px 0; }
.details-title { font-size:14px; font-weight:700; margin-bottom:8px; color:var(--text-label); }
.detail-item { display:flex; gap:10px; margin-bottom:10px; align-items:flex-start; }
.detail-bullet { font-size:18px; color:var(--accent-orange); line-height:1; margin-top:2px; }
.detail-content { flex:1; }
.detail-label { font-size:13px; color:var(--text-muted); margin-bottom:4px; }

.divider { border:none; border-top:1.5px solid var(--border-divider); margin:16px 0; }
.qc-section-title { font-size:14px; font-weight:700; text-align:center; padding:6px; background:var(--accent-orange-light); border-radius:4px; margin-bottom:10px; color:var(--accent-orange); transition:background 0.25s; }
.qc-type-row { display:flex; gap:32px; margin-bottom:12px; flex-wrap:wrap; }

/* ── Reference section ── */
.reference-section { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px; }
.ref-col { border:1px solid var(--border-light); border-radius:6px; padding:12px; background:var(--bg-section); transition:background 0.25s, border-color 0.25s; }
.ref-title { font-size:13px; font-weight:700; margin-bottom:8px; color:var(--text-label); }
.std-checkboxes { display:flex; flex-direction:column; gap:8px; }
.page-input {
  border:none; border-bottom:1px solid var(--border);
  background:transparent; width:60px; font-size:13px; padding:1px 4px;
  font-family:inherit; outline:none; margin-left:4px; color:var(--text-primary);
}
.wide { width:120px; }

/* ── Params ── */
.param-section { border:1px solid var(--border-light); border-radius:6px; padding:16px; margin-bottom:12px; background:var(--bg-section); transition:background 0.25s, border-color 0.25s; }
.param-header-row { display:flex; align-items:center; gap:16px; margin-bottom:12px; flex-wrap:wrap; }
.param-header-label { font-size:14px; font-weight:700; color:var(--text-label); }
.param-std-row { display:flex; gap:16px; }
.param-list { display:flex; flex-direction:column; gap:8px; }
.param-item { display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
.param-detail { display:flex; align-items:center; gap:6px; font-size:13px; color:var(--text-secondary); }

/* ── Result ── */
.result-section { margin-bottom:16px; }
.result-title { font-size:15px; font-weight:700; margin-bottom:10px; text-decoration:underline; color:var(--text-label); }
.result-options { display:flex; gap:40px; margin-bottom:10px; }
.result-label-option { display:flex; align-items:center; gap:8px; cursor:pointer; }
.result-label-option input[type="radio"] { width:18px; height:18px; cursor:pointer; }
.result-text { font-size:15px; font-weight:600; padding:4px 16px; border-radius:4px; }
.result-text.pass { color:var(--result-pass-text); background:var(--result-pass-bg); }
.result-text.fail { color:var(--result-fail-text); background:var(--result-fail-bg); }
.fail-remark { display:flex; gap:12px; align-items:flex-start; margin-top:8px; }

/* ── Signatures ── */
.sig-section { display:flex; gap:24px; align-items:center; flex-wrap:wrap; padding-top:12px; border-top:1px solid var(--border-divider); }

@media print {
  .form-card { padding:12px; box-shadow:none; background:white; }
  * { color:black !important; background:white !important; border-color:#333 !important; }
}
</style>
