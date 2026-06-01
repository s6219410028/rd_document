import { createRouter, createWebHistory } from 'vue-router'
import HomePage               from '../views/HomePage.vue'
import StabilityProgramPage   from '../views/StabilityProgramPage.vue'
import DissolutionProfilePage from '../views/DissolutionProfilePage.vue'
import QualityCheckPage       from '../views/QualityCheckPage.vue'
import TesterQualityCheckPage from '../views/TesterQualityCheckPage.vue'
import TesterDissolutionPage  from '../views/TesterDissolutionPage.vue'
import RecordsPage            from '../views/RecordsPage.vue'
import LoginPage              from '../views/LoginPage.vue'
import UsersPage              from '../views/UsersPage.vue'

const TOKEN_KEY = 'rd_auth_token'
const USER_KEY  = 'rd_auth_user'

const routes = [
  { path: '/login', component: LoginPage, meta: { public: true } },

  { path: '/',                           component: HomePage },
  { path: '/records',                    component: RecordsPage },
  { path: '/stability',                  component: StabilityProgramPage },
  { path: '/stability/:id',              component: StabilityProgramPage, props: true },
  { path: '/dissolution',                component: DissolutionProfilePage },
  { path: '/dissolution/:id',            component: DissolutionProfilePage, props: true },
  { path: '/dissolution/:id/test',       component: TesterDissolutionPage, props: true },
  { path: '/quality-check',              component: QualityCheckPage },
  { path: '/quality-check/:id',          component: QualityCheckPage, props: true },
  { path: '/quality-check/:id/test',     component: TesterQualityCheckPage, props: true },
  { path: '/users',                       component: UsersPage, meta: { adminOnly: true } },
]

const router = createRouter({ history: createWebHistory(), routes })

router.beforeEach((to) => {
  const token = localStorage.getItem(TOKEN_KEY)
  const user  = (() => { try { return JSON.parse(localStorage.getItem(USER_KEY)) } catch { return null } })()

  if (!to.meta.public && !token) return '/login'
  if (to.path === '/login' && token) return '/'
  if (to.meta.adminOnly && user?.role !== 'admin') return '/'
})

export default router
