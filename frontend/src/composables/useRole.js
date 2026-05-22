import { ref, readonly } from 'vue'

const role = ref(localStorage.getItem('rd_role') || null)

export function useRole() {
  function setRole(newRole) {
    role.value = newRole
    localStorage.setItem('rd_role', newRole)
  }
  function clearRole() {
    role.value = null
    localStorage.removeItem('rd_role')
  }
  return { role: readonly(role), setRole, clearRole }
}
