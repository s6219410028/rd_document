import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../views/HomePage.vue'
import StabilityProgramPage from '../views/StabilityProgramPage.vue'
import DissolutionProfilePage from '../views/DissolutionProfilePage.vue'
import QualityCheckPage from '../views/QualityCheckPage.vue'
import TesterQualityCheckPage from '../views/TesterQualityCheckPage.vue'
import RecordsPage from '../views/RecordsPage.vue'

const routes = [
  { path: '/', component: HomePage },
  { path: '/records', component: RecordsPage },
  { path: '/stability', component: StabilityProgramPage },
  { path: '/stability/:id', component: StabilityProgramPage, props: true },
  { path: '/dissolution', component: DissolutionProfilePage },
  { path: '/dissolution/:id', component: DissolutionProfilePage, props: true },
  { path: '/quality-check', component: QualityCheckPage },
  { path: '/quality-check/:id', component: QualityCheckPage, props: true },
  { path: '/quality-check/:id/test', component: TesterQualityCheckPage, props: true },
]

export default createRouter({ history: createWebHistory(), routes })
