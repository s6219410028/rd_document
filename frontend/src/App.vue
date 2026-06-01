<template>
  <div v-if="authChecking" class="app-loading">
    <div class="loading-spinner" />
  </div>
  <div v-else class="app-layout">
    <NavBar v-if="isLoggedIn" />
    <main :class="isLoggedIn ? 'app-main' : 'app-main-full'">
      <div :class="isLoggedIn ? 'app-content' : ''">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import NavBar    from './components/NavBar.vue'
import { useAuth } from './composables/useAuth.js'

const { isLoggedIn, verifyToken } = useAuth()
const router       = useRouter()
const authChecking = ref(true)

onMounted(async () => {
  const valid = await verifyToken()
  authChecking.value = false
  if (!valid && router.currentRoute.value.meta?.public !== true) {
    router.replace('/login')
  }
})
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

.app-main-full {
  flex: 1;
  overflow-y: auto;
  background: var(--bg);
}

.app-content {
  padding: 28px 32px;
  max-width: 1200px;
  margin: 0 auto;
}

.app-loading {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
}

.loading-spinner {
  width: 36px;
  height: 36px;
  border: 3px solid var(--border, #e5e7eb);
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
