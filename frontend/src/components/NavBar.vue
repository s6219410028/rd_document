<template>
  <aside class="sidebar no-print">
    <!-- Brand -->
    <div class="sidebar-brand">
      <div class="brand-logo">RD</div>
      <div class="brand-text">
        <div class="brand-name">RD Document</div>
        <div class="brand-sub">T.MAN PHARMA</div>
      </div>
    </div>

    <!-- Nav links -->
    <nav class="sidebar-nav">
      <router-link to="/" class="nav-item" exact-active-class="nav-active">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </span>
        <span class="nav-label">หน้าหลัก</span>
      </router-link>

      <router-link to="/quality-check" class="nav-item" active-class="nav-active">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/></svg>
        </span>
        <span class="nav-label">
          Quality Check
          <small>F-RD-FD-005</small>
        </span>
      </router-link>

      <router-link to="/dissolution" class="nav-item" active-class="nav-active">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3l-6 18h18L15 3"/><path d="M6 12h12"/></svg>
        </span>
        <span class="nav-label">
          Dissolution
          <small>F-RD-FD-006</small>
        </span>
      </router-link>

<router-link to="/records" class="nav-item" active-class="nav-active">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        </span>
        <span class="nav-label">ข้อมูลทั้งหมด</span>
      </router-link>

      <!-- Admin only -->
      <router-link v-if="isAdmin" to="/users" class="nav-item" active-class="nav-active">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </span>
        <span class="nav-label">จัดการผู้ใช้</span>
      </router-link>
    </nav>

    <!-- Bottom area -->
    <div class="sidebar-bottom">
      <!-- Theme toggle -->
      <button class="theme-toggle" @click="darkMode.toggle()" :title="darkMode.isDark.value ? 'Light Mode' : 'Dark Mode'">
        <span class="nav-icon">
          <svg v-if="darkMode.isDark.value" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </span>
        <span class="nav-label">{{ darkMode.isDark.value ? 'Light Mode' : 'Dark Mode' }}</span>
      </button>

      <!-- Admin role switcher -->
      <div v-if="isAdmin" class="role-switcher">
        <div class="role-switcher-label">มุมมอง</div>
        <div class="role-switcher-btns">
          <button
            :class="['rs-btn', activeRole === 'sender' ? 'rs-active-sender' : '']"
            @click="setActiveRole('sender')"
          >📤 ผู้ส่ง</button>
          <button
            :class="['rs-btn', activeRole === 'tester' ? 'rs-active-tester' : '']"
            @click="setActiveRole('tester')"
          >🔬 ผู้ทดสอบ</button>
        </div>
      </div>

      <!-- User info + logout -->
      <div v-if="user" class="user-section">
        <div class="user-row">
          <div class="user-avatar" :class="`avatar-${user.role}`">
            {{ user.display_name.charAt(0).toUpperCase() }}
          </div>
          <div class="user-info">
            <span class="user-name">{{ user.display_name }}</span>
            <span class="user-role">{{ ROLE_LABELS[user.role] }}</span>
          </div>
        </div>
        <button class="logout-btn" @click="handleLogout">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          ออกจากระบบ
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useDarkMode } from '../composables/useDarkMode.js'
import { useAuth }     from '../composables/useAuth.js'

const darkMode = useDarkMode()
const { user, isAdmin, activeRole, setActiveRole, logout } = useAuth()
const router = useRouter()

const ROLE_LABELS = { admin: 'Admin', sender: 'ผู้ส่ง', tester: 'ผู้ทดสอบ' }

async function handleLogout() {
  await logout()
  router.replace('/login')
}
</script>

<style scoped>
.sidebar {
  width: 220px;
  min-width: 220px;
  height: 100vh;
  background: var(--sb-bg);
  display: flex;
  flex-direction: column;
  border-right: 1px solid var(--sb-border);
  position: sticky;
  top: 0;
  overflow-y: auto;
  overflow-x: hidden;
  transition: background 0.25s, border-color 0.25s;
}

/* ── Brand ── */
.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 16px 18px;
  border-bottom: 1px solid var(--sb-divider);
}

.brand-logo {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: var(--sb-brand-bg);
  color: #fff;
  font-size: 13px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  letter-spacing: 0.5px;
  flex-shrink: 0;
}

.brand-text { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.brand-name { font-size: 13px; font-weight: 700; color: var(--sb-brand-name); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color 0.25s; }
.brand-sub  { font-size: 10px; color: var(--sb-brand-sub); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color 0.25s; }

/* ── Nav ── */
.sidebar-nav {
  flex: 1;
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 10px;
  border-radius: 8px;
  color: var(--sb-text);
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
  transition: background 0.15s, color 0.15s;
  cursor: pointer;
  border-left: 3px solid transparent;
}
.nav-item:hover        { background: var(--sb-hover-bg); color: var(--sb-text-hover); }
.nav-item.nav-active   { background: var(--sb-active-bg); color: var(--sb-active-text); border-left-color: var(--sb-active-border); }

.nav-icon { width: 18px; height: 18px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.nav-icon svg { width: 16px; height: 16px; stroke: currentColor; }

.nav-label { display: flex; flex-direction: column; gap: 1px; line-height: 1.2; min-width: 0; }
.nav-label small { font-size: 10px; opacity: 0.55; font-weight: 400; }

/* ── Bottom ── */
.sidebar-bottom {
  padding: 10px 10px 16px;
  border-top: 1px solid var(--sb-divider);
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.theme-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 8px 10px;
  border-radius: 8px;
  background: transparent;
  border: none;
  color: var(--sb-bottom-text);
  font-size: 12px;
  font-family: inherit;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  text-align: left;
}
.theme-toggle:hover { background: var(--sb-hover-bg); color: var(--sb-text-hover); }
.theme-toggle .nav-icon { color: currentColor; }

/* ── Admin role switcher ── */
.role-switcher { padding: 6px 6px 0; }
.role-switcher-label { font-size: 10px; color: var(--sb-bottom-text); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }

.role-switcher-btns { display: flex; gap: 4px; }

.rs-btn {
  flex: 1;
  padding: 5px 6px;
  border-radius: 6px;
  border: 1px solid var(--sb-role-border);
  background: var(--sb-role-bg);
  color: var(--sb-text);
  font-size: 11px;
  font-family: inherit;
  cursor: pointer;
  font-weight: 500;
  text-align: center;
  transition: background 0.15s, border-color 0.15s, color 0.15s;
}

.rs-active-sender { background: rgba(251,146,60,0.15); border-color: rgba(251,146,60,0.5); color: #f97316; }
.rs-active-tester { background: rgba(99,102,241,0.15); border-color: rgba(99,102,241,0.5); color: #6366f1; }

/* ── User info ── */
.user-section {
  padding: 6px 6px 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.user-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}
.avatar-admin  { background: rgba(99,102,241,0.15); color: #6366f1; border: 1px solid rgba(99,102,241,0.35); }
.avatar-sender { background: rgba(251,146,60,0.15);  color: #f97316; border: 1px solid rgba(251,146,60,0.35); }
.avatar-tester { background: rgba(16,185,129,0.15);  color: #10b981; border: 1px solid rgba(16,185,129,0.35); }

.user-info { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.user-name { font-size: 12px; font-weight: 600; color: var(--sb-text-hover); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color 0.25s; }
.user-role { font-size: 10px; color: var(--sb-bottom-text); }

.logout-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  width: 100%;
  padding: 7px 10px;
  border-radius: 7px;
  background: transparent;
  border: 1px solid var(--sb-role-border);
  color: var(--sb-bottom-text);
  font-size: 12px;
  font-family: inherit;
  cursor: pointer;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
  text-align: left;
}
.logout-btn:hover { background: rgba(239,68,68,0.08); color: #ef4444; border-color: rgba(239,68,68,0.3); }
</style>
