<template>
  <nav class="navbar no-print">
    <div class="nav-brand">
      <span class="brand-logo">⚗</span>
      <span class="brand-name">T.MAN PHARMA</span>
      <span class="brand-sub">RD Document System</span>
    </div>
    <div class="nav-links">
      <router-link to="/" class="nav-link">หน้าหลัก</router-link>
      <router-link to="/quality-check" class="nav-link">Quality Check<br><small>F-RD-FD-005</small></router-link>
      <router-link to="/dissolution" class="nav-link">Dissolution Profile<br><small>F-RD-FD-006</small></router-link>
      <router-link to="/stability" class="nav-link">Stability Program<br><small>F-RD-FD-012</small></router-link>
      <button class="theme-toggle" @click="darkMode.toggle()" :title="darkMode.isDark.value ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
        <span v-if="darkMode.isDark.value">☀️</span>
        <span v-else>🌙</span>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { useDarkMode } from '../composables/useDarkMode.js'
const darkMode = useDarkMode()
</script>

<style scoped>
.navbar {
  background: linear-gradient(135deg, var(--nav-start) 0%, var(--nav-end) 100%);
  color: white;
  padding: 0 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 64px;
  box-shadow: var(--shadow-nav);
  position: sticky;
  top: 0;
  z-index: 100;
  transition: background 0.25s, box-shadow 0.25s;
}
.nav-brand { display: flex; align-items: center; gap: 10px; }
.brand-logo { font-size: 24px; }
.brand-name { font-size: 18px; font-weight: 700; letter-spacing: 1px; color: white; }
.brand-sub { font-size: 12px; opacity: 0.7; border-left: 1px solid rgba(255,255,255,0.3); padding-left: 10px; color: var(--nav-text); }
.nav-links { display: flex; gap: 4px; align-items: center; }
.nav-link {
  color: var(--nav-text);
  text-decoration: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 13px;
  text-align: center;
  line-height: 1.3;
  transition: background 0.2s;
}
.nav-link:hover, .nav-link.router-link-active {
  background: var(--nav-active-bg);
  color: white;
}
.nav-link small { font-size: 11px; opacity: 0.7; }

.theme-toggle {
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  width: 40px;
  height: 36px;
  font-size: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: 8px;
  transition: background 0.2s, transform 0.15s;
  line-height: 1;
}
.theme-toggle:hover {
  background: rgba(255, 255, 255, 0.22);
  transform: scale(1.1);
}
</style>
