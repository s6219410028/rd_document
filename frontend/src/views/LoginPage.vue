<template>
  <div class="login-page">
    <div class="login-card">
      <!-- Brand -->
      <div class="login-brand">
        <div class="brand-logo">RD</div>
        <div class="brand-text">
          <div class="brand-name">RD Document</div>
          <div class="brand-sub">T.MAN PHARMA</div>
        </div>
      </div>

      <h2 class="login-title">เข้าสู่ระบบ</h2>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="field">
          <label>ชื่อผู้ใช้</label>
          <input
            v-model="username"
            type="text"
            placeholder="ชื่อผู้ใช้"
            autocomplete="username"
            required
            :disabled="loading"
          />
        </div>
        <div class="field">
          <label>รหัสผ่าน</label>
          <input
            v-model="password"
            type="password"
            placeholder="รหัสผ่าน"
            autocomplete="current-password"
            required
            :disabled="loading"
          />
        </div>
        <div v-if="error" class="login-error">{{ error }}</div>
        <button type="submit" class="login-btn" :disabled="loading">
          <span v-if="loading" class="spinner" />
          {{ loading ? 'กำลังเข้าสู่ระบบ…' : 'เข้าสู่ระบบ' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuth } from '../composables/useAuth.js'
import { useRouter } from 'vue-router'

const { login } = useAuth()
const router    = useRouter()

const username = ref('')
const password = ref('')
const error    = ref('')
const loading  = ref(false)

async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    await login(username.value, password.value)
    router.push('/')
  } catch (e) {
    error.value = e.message || 'เข้าสู่ระบบไม่สำเร็จ'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
  padding: 24px;
}

.login-card {
  width: 100%;
  max-width: 380px;
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 36px 32px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.07);
}

/* ── Brand ── */
.login-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 28px;
}

.brand-logo {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: var(--sb-brand-bg, #6366f1);
  color: #fff;
  font-size: 14px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.brand-text { display: flex; flex-direction: column; gap: 2px; }
.brand-name { font-size: 14px; font-weight: 700; color: var(--text); }
.brand-sub  { font-size: 11px; color: var(--text-muted, #888); }

/* ── Form ── */
.login-title {
  font-size: 20px;
  font-weight: 700;
  color: var(--text);
  margin: 0 0 24px;
}

.login-form { display: flex; flex-direction: column; gap: 16px; }

.field { display: flex; flex-direction: column; gap: 6px; }

.field label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted, #666);
}

.field input {
  padding: 10px 14px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--input-bg, var(--bg));
  color: var(--text);
  font-size: 14px;
  font-family: inherit;
  outline: none;
  transition: border-color 0.15s;
}

.field input:focus  { border-color: #6366f1; }
.field input:disabled { opacity: 0.6; cursor: not-allowed; }

.login-error {
  font-size: 13px;
  color: #ef4444;
  background: rgba(239,68,68,0.08);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 8px;
  padding: 10px 14px;
}

.login-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 11px;
  background: #6366f1;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: background 0.15s;
  margin-top: 4px;
}

.login-btn:hover:not(:disabled) { background: #4f46e5; }
.login-btn:disabled { opacity: 0.65; cursor: not-allowed; }

.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  flex-shrink: 0;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
