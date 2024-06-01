<template>
  <div class="home">
    <p>
      <img src="../assets/header.png" alt="">
    </p>
    <h3>心情过客的比赛管理器<a href="https://pan.baidu.com/s/1qWAfHyFZAd1zQ_bEUvZ1yw?pwd=j8je" class="btn btn-primary" target="_blank">下载</a></h3>
    <p v-if="!is_login">
      <button class="btn btn-primary" @click="$router.push('/register')">注册</button>
      <button class="btn btn-success" @click="$router.push('/login')">登录</button>
    </p>
    <p>
      <button class="btn btn-success" @click="$router.push('/sign')" v-if="save.id && save.key">立即报名</button>
      <button class="btn btn-primary" @click="logout()" v-if="is_login">退出登录</button>
    </p>
    <p v-if="admin">
      <button class="btn btn-primary" @click="$router.push('/key_manager')">管理</button>
    </p>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
const is_login = ref(false)
const save = ref({})
const admin = ref(false)
const logout = () => {
  localStorage.removeItem('token')
  is_login.value = false
  localStorage.removeItem('admin')
  admin.value = false
}
onMounted(async () => {
  is_login.value = localStorage.getItem('token') ? true : false
  save.value = localStorage.getItem('save') ? JSON.parse(localStorage.getItem('save')) : {}
  if(localStorage.getItem('admin')){
    admin.value = true
  }
})
</script>

<style scoped lang="less">
@import url('../assets/home.less');
</style>