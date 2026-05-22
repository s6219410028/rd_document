<template>
  <div class="app-layout">
    <NavBar />
    <main class="app-main">
      <div class="app-content">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import NavBar from './components/NavBar.vue'
</script>

<style>
.app-layout {
  display: flex;
  height: 100vh;
  overflow: hidden;
}

.app-main {
  flex: 1;
  overflow-y: auto;
  background: var(--bg);
}

.app-content {
  padding: 28px 32px;
  max-width: 1200px;
  margin: 0 auto;
}
</style>
