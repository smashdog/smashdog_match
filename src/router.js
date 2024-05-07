// history模式
import {
  createRouter,
  createWebHashHistory,
} from 'vue-router'
const routes = [
  // 路由的默认路径
  {
    path: '/',
    redirect: "/games"
  },
  {
    path: '/games',
    component: () => import(/* webpackChunkName: "Games-trunk" */ './pages/Games.vue')
  },
  {
    path: '/players/:id(\\d+)',
    component: () => import(/* webpackChunkName: "Players-trunk" */ './pages/Players.vue')
  },
  {
    path: '/matchs/:id(\\d+)/:end(\\d)?',
    component: () => import(/* webpackChunkName: "Matchs-trunk" */ './pages/Matchs.vue')
  },
  {
    path: '/matchs_friend/:id(\\d+)',
    component: () => import(/* webpackChunkName: "MatchsFriend-trunk" */ './pages/MatchsFriend.vue')
  },
]

// 创建路由对象
const router = createRouter({
  history: createWebHashHistory(),
  routes
})
export default router
