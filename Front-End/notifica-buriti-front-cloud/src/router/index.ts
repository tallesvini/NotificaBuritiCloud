import { createRouter, createWebHistory } from 'vue-router'
import NotificationTab from '../components/NotificationTab.vue'

const routes = [
  {
    path: '/notificacao',
    name: 'NotificationTab',
    component: NotificationTab
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
