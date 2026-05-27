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

      <!-- <router-link to="/stability" class="nav-item" active-class="nav-active">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </span>
        <span class="nav-label">
          Stability
          <small>F-RD-FD-012</small>
        </span>
      </router-link> -->

      <router-link to="/records" class="nav-item" active-class="nav-active">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        </span>
        <span class="nav-label">ข้อมูลทั้งหมด</span>
      </router-link>
    </nav>

    <!-- Bottom area -->
    <div class="sidebar-bottom">
      <button class="theme-toggle" @click="darkMode.toggle()" :title="darkMode.isDark.value ? 'Light Mode' : 'Dark Mode'">
        <span class="nav-icon">
          <svg v-if="darkMode.isDark.value" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </span>
        <span class="nav-label">{{ darkMode.isDark.value ? 'Light Mode' : 'Dark Mode' }}</span>
      </button>

      <div class="role-section">
        <template v-if="!role">
          <div class="role-prompt">เลือกบทบาท</div>
          <div class="role-btns">
            <button class="role-btn role-sender" @click="switchRole('sender')">📤 ผู้ส่ง</button>
            <button class="role-btn role-tester" @click="switchRole('tester')">🔬 ผู้ทดสอบ</button>
          </div>
        </template>
        <template v-else>
          <div class="role-user">
            <div class="role-avatar" :class="role === 'sender' ? 'avatar-sender' : 'avatar-tester'">
              {{ role === 'sender' ? 'S' : 'T' }}
            </div>
            <div class="role-info">
              <span class="role-name">{{ role === 'sender' ? 'ผู้ส่ง' : 'ผู้ทดสอบ' }}</span>
              <button class="role-switch" @click="clearRole">เปลี่ยนบทบาท →</button>
            </div>
          </div>
        </template>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { useDarkMode } from '../composables/useDarkMode.js'
import { useRole } from '../composables/useRole.js'
const darkMode = useDarkMode()
const { role, setRole, clearRole } = useRole()

function switchRole(newRole) {
  setRole(newRole)
  window.location.reload()
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

.brand-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
  min-width: 0;
}

.brand-name {
  font-size: 13px;
  font-weight: 700;
  color: var(--sb-brand-name);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color 0.25s;
}

.brand-sub {
  font-size: 10px;
  color: var(--sb-brand-sub);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color 0.25s;
}

/* ── Nav ── */
.sidebar-nav {
  flex: 1;
  padding: 10px 10px;
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

.nav-item:hover {
  background: var(--sb-hover-bg);
  color: var(--sb-text-hover);
}

.nav-item.nav-active {
  background: var(--sb-active-bg);
  color: var(--sb-active-text);
  border-left-color: var(--sb-active-border);
}

.nav-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-icon svg {
  width: 16px;
  height: 16px;
  stroke: currentColor;
}

.nav-label {
  display: flex;
  flex-direction: column;
  gap: 1px;
  line-height: 1.2;
  min-width: 0;
}

.nav-label small {
  font-size: 10px;
  opacity: 0.55;
  font-weight: 400;
}

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

.theme-toggle:hover {
  background: var(--sb-hover-bg);
  color: var(--sb-text-hover);
}

.theme-toggle .nav-icon { color: currentColor; }

/* ── Role section ── */
.role-section {
  padding: 8px 6px 2px;
}

.role-prompt {
  font-size: 11px;
  color: var(--sb-bottom-text);
  margin-bottom: 6px;
}

.role-btns {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.role-btn {
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid var(--sb-role-border);
  background: var(--sb-role-bg);
  color: var(--sb-text);
  font-size: 12px;
  font-family: inherit;
  cursor: pointer;
  font-weight: 500;
  text-align: left;
  transition: background 0.15s, border-color 0.15s, color 0.15s;
}

.role-sender:hover {
  background: rgba(251,146,60,0.15);
  border-color: rgba(251,146,60,0.5);
  color: #f97316;
}

.role-tester:hover {
  background: rgba(99,102,241,0.15);
  border-color: rgba(99,102,241,0.5);
  color: #6366f1;
}

.role-user {
  display: flex;
  align-items: center;
  gap: 10px;
}

.role-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}

.avatar-sender {
  background: rgba(251,146,60,0.15);
  color: #f97316;
  border: 1px solid rgba(251,146,60,0.35);
}

.avatar-tester {
  background: rgba(99,102,241,0.15);
  color: #6366f1;
  border: 1px solid rgba(99,102,241,0.35);
}

.role-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
}

.role-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--sb-text-hover);
  transition: color 0.25s;
}

.role-switch {
  font-size: 11px;
  color: var(--sb-bottom-text);
  background: none;
  border: none;
  cursor: pointer;
  font-family: inherit;
  padding: 0;
  text-align: left;
  transition: color 0.15s;
}

.role-switch:hover {
  color: var(--sb-text-hover);
}
</style>
