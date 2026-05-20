import { ref, watch } from 'vue'

const isDark = ref(localStorage.getItem('theme') === 'dark')

function applyTheme(dark) {
  document.documentElement.classList.toggle('dark', dark)
}

applyTheme(isDark.value)

watch(isDark, (val) => {
  applyTheme(val)
  localStorage.setItem('theme', val ? 'dark' : 'light')
})

export function useDarkMode() {
  function toggle() { isDark.value = !isDark.value }
  return { isDark, toggle }
}
