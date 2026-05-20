<template>
  <div>
    <NavBar />
    <main style="padding: 24px; max-width: 1200px; margin: 0 auto;">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import NavBar from './components/NavBar.vue'
</script>
