<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">จัดการผู้ใช้</h1>
        <p class="page-sub">เพิ่ม แก้ไข หรือลบบัญชีผู้ใช้งานในระบบ</p>
      </div>
      <button class="btn-primary" @click="openCreate">+ เพิ่มผู้ใช้</button>
    </div>

    <!-- Table -->
    <div class="card">
      <table class="users-table">
        <thead>
          <tr>
            <th>#</th>
            <th>ชื่อผู้ใช้</th>
            <th>ชื่อแสดง</th>
            <th>บทบาท</th>
            <th>สถานะ</th>
            <th>วันที่สร้าง</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="empty">กำลังโหลด…</td></tr>
          <tr v-else-if="!users.length"><td colspan="7" class="empty">ยังไม่มีผู้ใช้</td></tr>
          <tr v-for="u in users" :key="u.id">
            <td class="td-id">{{ u.id }}</td>
            <td class="td-username">{{ u.username }}</td>
            <td>{{ u.display_name }}</td>
            <td><span :class="['role-badge', `role-${u.role}`]">{{ ROLE_LABELS[u.role] }}</span></td>
            <td>
              <span :class="['status-dot', u.active ? 'active' : 'inactive']">
                {{ u.active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
              </span>
            </td>
            <td class="td-date">{{ formatDate(u.created_at) }}</td>
            <td class="td-actions">
              <button class="btn-icon" title="แก้ไข" @click="openEdit(u)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="btn-icon btn-danger" title="ลบ" @click="confirmDelete(u)" :disabled="u.id === currentUser?.id">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="modal.open" class="modal-backdrop" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ modal.editId ? 'แก้ไขผู้ใช้' : 'เพิ่มผู้ใช้ใหม่' }}</h3>
          <button class="modal-close" @click="closeModal">✕</button>
        </div>
        <form @submit.prevent="saveUser" class="modal-form">
          <div class="field">
            <label>ชื่อผู้ใช้ <span class="req">*</span></label>
            <input v-model="form.username" type="text" required placeholder="username" />
          </div>
          <div class="field">
            <label>ชื่อแสดง <span class="req">*</span></label>
            <input v-model="form.display_name" type="text" required placeholder="ชื่อ-นามสกุล" />
          </div>
          <div class="field">
            <label>รหัสผ่าน {{ modal.editId ? '(เว้นว่างเพื่อคงเดิม)' : '*' }}</label>
            <input v-model="form.password" type="password" :required="!modal.editId" placeholder="รหัสผ่าน" />
          </div>
          <div class="field">
            <label>บทบาท <span class="req">*</span></label>
            <select v-model="form.role" required>
              <option value="admin">Admin — สลับบทบาทได้ + จัดการผู้ใช้</option>
              <option value="sender">Sender — ผู้ส่งตัวอย่าง (ตายตัว)</option>
              <option value="tester">Tester — ผู้ทดสอบ (ตายตัว)</option>
            </select>
          </div>
          <div v-if="modal.editId" class="field">
            <label>สถานะ</label>
            <select v-model="form.active">
              <option :value="1">เปิดใช้งาน</option>
              <option :value="0">ปิดใช้งาน</option>
            </select>
          </div>
          <div v-if="modalError" class="form-error">{{ modalError }}</div>
          <div class="modal-footer">
            <button type="button" class="btn-secondary" @click="closeModal">ยกเลิก</button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'กำลังบันทึก…' : (modal.editId ? 'บันทึก' : 'เพิ่มผู้ใช้') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete confirm -->
    <div v-if="deleteTarget" class="modal-backdrop" @click.self="deleteTarget = null">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h3>ยืนยันการลบ</h3>
          <button class="modal-close" @click="deleteTarget = null">✕</button>
        </div>
        <p class="delete-msg">ต้องการลบผู้ใช้ <strong>{{ deleteTarget.display_name }}</strong> ออกจากระบบ?<br/>การกระทำนี้ไม่สามารถยกเลิกได้</p>
        <div class="modal-footer">
          <button class="btn-secondary" @click="deleteTarget = null">ยกเลิก</button>
          <button class="btn-danger-solid" @click="doDelete" :disabled="saving">
            {{ saving ? 'กำลังลบ…' : 'ลบผู้ใช้' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { api } from '../api/index.js'
import { useAuth } from '../composables/useAuth.js'

const { user: currentUser } = useAuth()

const ROLE_LABELS = { admin: 'Admin', sender: 'Sender', tester: 'Tester' }

const users       = ref([])
const loading     = ref(false)
const saving      = ref(false)
const modalError  = ref('')
const deleteTarget = ref(null)

const modal = reactive({ open: false, editId: null })
const form  = reactive({ username: '', display_name: '', password: '', role: 'sender', active: 1 })

function resetForm() {
  form.username     = ''
  form.display_name = ''
  form.password     = ''
  form.role         = 'sender'
  form.active       = 1
  modalError.value  = ''
}

function openCreate() {
  resetForm()
  modal.editId = null
  modal.open   = true
}

function openEdit(u) {
  resetForm()
  modal.editId      = u.id
  form.username     = u.username
  form.display_name = u.display_name
  form.role         = u.role
  form.active       = u.active ? 1 : 0
  modal.open        = true
}

function closeModal() {
  modal.open = false
}

function confirmDelete(u) {
  deleteTarget.value = u
}

async function loadUsers() {
  loading.value = true
  try { users.value = await api.users.list() }
  finally { loading.value = false }
}

async function saveUser() {
  modalError.value = ''
  saving.value     = true
  try {
    const payload = { ...form }
    if (modal.editId) {
      await api.users.update(modal.editId, payload)
    } else {
      await api.users.create(payload)
    }
    closeModal()
    await loadUsers()
  } catch (e) {
    modalError.value = e.message || 'บันทึกไม่สำเร็จ'
  } finally {
    saving.value = false
  }
}

async function doDelete() {
  if (!deleteTarget.value) return
  saving.value = true
  try {
    await api.users.delete(deleteTarget.value.id)
    deleteTarget.value = null
    await loadUsers()
  } catch (e) {
    alert(e.message || 'ลบไม่สำเร็จ')
  } finally {
    saving.value = false
  }
}

function formatDate(str) {
  if (!str) return '-'
  return new Date(str).toLocaleDateString('th-TH', { day: '2-digit', month: 'short', year: 'numeric' })
}

onMounted(loadUsers)
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 24px;
}

.page-title { font-size: 20px; font-weight: 700; color: var(--text); margin: 0 0 4px; }
.page-sub   { font-size: 13px; color: var(--text-muted, #888); margin: 0; }

/* ── Card ── */
.card {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
}

/* ── Table ── */
.users-table { width: 100%; border-collapse: collapse; font-size: 13px; }

.users-table th {
  background: var(--table-head-bg, rgba(0,0,0,0.04));
  padding: 10px 14px;
  text-align: left;
  font-weight: 600;
  color: var(--text-muted, #666);
  font-size: 12px;
  white-space: nowrap;
}

.users-table td {
  padding: 11px 14px;
  border-top: 1px solid var(--border);
  color: var(--text);
  vertical-align: middle;
}

.users-table tr:hover td { background: var(--hover-bg, rgba(0,0,0,0.02)); }

.td-id       { width: 40px; color: var(--text-muted, #888); }
.td-username { font-weight: 600; }
.td-date     { white-space: nowrap; color: var(--text-muted, #888); }
.td-actions  { width: 80px; white-space: nowrap; }

.empty { text-align: center; color: var(--text-muted, #888); padding: 32px; }

/* ── Badges ── */
.role-badge {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}
.role-admin  { background: rgba(99,102,241,0.12); color: #6366f1; }
.role-sender { background: rgba(251,146,60,0.12);  color: #f97316; }
.role-tester { background: rgba(16,185,129,0.12);  color: #10b981; }

.status-dot {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 500;
}
.status-dot::before { content: ''; display: inline-block; width: 7px; height: 7px; border-radius: 50%; }
.status-dot.active   { color: #10b981; }
.status-dot.active::before   { background: #10b981; }
.status-dot.inactive { color: #ef4444; }
.status-dot.inactive::before { background: #ef4444; }

/* ── Buttons ── */
.btn-primary {
  padding: 9px 18px;
  background: #6366f1;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s;
}
.btn-primary:hover { background: #4f46e5; }

.btn-secondary {
  padding: 9px 18px;
  background: transparent;
  color: var(--text);
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  font-family: inherit;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-secondary:hover { background: var(--hover-bg, rgba(0,0,0,0.04)); }

.btn-icon {
  width: 30px;
  height: 30px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--border);
  border-radius: 6px;
  background: transparent;
  color: var(--text-muted, #888);
  cursor: pointer;
  margin-left: 4px;
  transition: background 0.15s, color 0.15s;
}
.btn-icon svg { width: 14px; height: 14px; }
.btn-icon:hover { background: var(--hover-bg, rgba(0,0,0,0.05)); color: var(--text); }
.btn-icon.btn-danger:hover { background: rgba(239,68,68,0.1); color: #ef4444; border-color: rgba(239,68,68,0.3); }
.btn-icon:disabled { opacity: 0.4; cursor: not-allowed; }

.btn-danger-solid {
  padding: 9px 18px;
  background: #ef4444;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-family: inherit;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-danger-solid:hover { background: #dc2626; }
.btn-danger-solid:disabled { opacity: 0.65; cursor: not-allowed; }

/* ── Modal ── */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 14px;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 8px 40px rgba(0,0,0,0.15);
}
.modal-sm { max-width: 360px; }

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px 0;
}
.modal-header h3 { font-size: 16px; font-weight: 700; color: var(--text); margin: 0; }

.modal-close {
  background: none;
  border: none;
  font-size: 16px;
  cursor: pointer;
  color: var(--text-muted, #888);
  line-height: 1;
  padding: 2px 6px;
}

.modal-form { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; }

.field { display: flex; flex-direction: column; gap: 5px; }
.field label { font-size: 12px; font-weight: 500; color: var(--text-muted, #666); }
.req { color: #ef4444; }

.field input,
.field select {
  padding: 9px 12px;
  border: 1px solid var(--border);
  border-radius: 7px;
  background: var(--input-bg, var(--bg));
  color: var(--text);
  font-size: 13px;
  font-family: inherit;
  outline: none;
  transition: border-color 0.15s;
}
.field input:focus,
.field select:focus { border-color: #6366f1; }

.form-error {
  font-size: 12px;
  color: #ef4444;
  background: rgba(239,68,68,0.08);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 6px;
  padding: 8px 12px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding: 0 24px 20px;
}

.delete-msg { padding: 12px 24px; font-size: 14px; color: var(--text); line-height: 1.6; }
</style>
