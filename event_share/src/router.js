// history模式
import {
  createRouter,
  createWebHashHistory,
} from 'vue-router'
import { myFetch } from './tools/net.js'
const routes = [
  {
    path: '/',
    component: () => import(/* webpackChunkName: "Home-trunk" */ './pages/Home.vue')
  },
  {
    path: '/matchs/:key(\\w+)/:id(\\d+)/:end(\\d)?',
    component: () => import(/* webpackChunkName: "Matchs-trunk" */ './pages/Matchs.vue')
  },
  {
    path: '/register',
    component: () => import(/* webpackChunkName: "Register-trunk" */ './pages/Register.vue')
  },
  {
    path: '/verify_email',
    component: () => import(/* webpackChunkName: "VerifyEmail-trunk" */ './pages/VerifyEmail.vue')
  },
  {
    path: '/register_success',
    component: () => import(/* webpackChunkName: "RegisterSuccess-trunk" */ './pages/RegisterSuccess.vue')
  },
  {
    path: '/login',
    component: () => import(/* webpackChunkName: "Login-trunk" */ './pages/Login.vue')
  },
  {
    path: '/sign',
    component: () => import(/* webpackChunkName: "Sign-trunk" */ './pages/Sign.vue')
  },
  {
    path: '/findpwd',
    component: () => import(/* webpackChunkName: "FindPwd-trunk" */ './pages/FindPwd.vue')
  },
  {
    path: '/changepwd',
    component: () => import(/* webpackChunkName: "User-trunk" */ './pages/ChangePwd.vue')
  },
]

// 创建路由对象
const router = createRouter({
  history: createWebHashHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  if(to.query.key && to.query.id){
    let save = {
      'key': to.query.key,
      'id': to.query.id
    }
    localStorage.setItem('save', JSON.stringify(save))
  }
  if(localStorage.getItem('token')){
    const res = await myFetch('/api/user.php', {
      action: 'check_token',
      token: localStorage.getItem('token')
    })
    if(res.code != 0){
      localStorage.removeItem('token')
    }
  }
  next()
})

export default router
