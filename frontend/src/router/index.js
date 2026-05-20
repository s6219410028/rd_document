import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../views/HomePage.vue'
import StabilityProgramPage from '../views/StabilityProgramPage.vue'
import DissolutionProfilePage from '../views/DissolutionProfilePage.vue'
import QualityCheckPage from '../views/QualityCheckPage.vue'

const routes = [
  { path: '/', component: HomePage },
  { path: '/stability', component: StabilityProgramPage },
  { path: '/stability/:id', component: StabilityProgramPage, props: true },
  { path: '/dissolution', component: DissolutionProfilePage },
  { path: '/dissolution/:id', component: DissolutionProfilePage, props: true },
  { path: '/quality-check', component: QualityCheckPage },
  { path: '/quality-check/:id', component: QualityCheckPage, props: true },
]

export default createRouter({ history: createWebHistory(), routes })
