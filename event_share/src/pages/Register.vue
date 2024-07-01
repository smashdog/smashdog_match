<template>
  <form class="div_form">
    <h4>注册</h4>
    <div class="mb-3">
      <label for="email" class="form-label">邮箱</label>
      <input type="email" class="form-control" id="email" name="email" placeholder="请尽量使用国内邮箱，国外邮箱有可能收不到邮件" required
        v-model="form.email" autocomplete="off">
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">密码</label>
      <input type="password" class="form-control" id="password" name="password" placeholder="请输入密码" required
        v-model="form.password" autocomplete="off">
    </div>
    <div class="mb-3">
      <label for="repassword" class="form-label">再次输入密码</label>
      <input type="password" class="form-control" id="repassword" name="repassword" placeholder="再次输入密码" required
        v-model="form.repassword" autocomplete="off">
    </div>
    <div class="mb-3">
      <label for="captcha" class="form-label">验证码</label>
      <input type="text" class="form-control" id="captcha" name="captcha" placeholder="请输入验证码" required
        v-model="form.captcha" autocomplete="off">
      <img :src="captcha" alt="" @click="changeCaptcha()">
    </div>
    <div class="mb-3">
      <button type="button" class="btn btn-success btn-sm" @click="submit()">提交</button>
      <button type="button" class="btn btn-secondary btn-sm" @click="$router.push(`/`)">取消</button>
    </div>
    <div class="mb-3">
      <button type="button" class="btn btn-primary btn-sm" @click="$router.push('/login')">立即登录</button>
      <button type="button" class="btn btn-primary btn-sm" @click="$router.push('/findpwd')">找回密码</button>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { myFetch } from '../tools/net.js'
const form = {
  email: '',
  password: '',
  repassword: '',
  captcha: '',
}
const router = useRouter()
const captcha = ref(`${import.meta.env.VITE_HOST}/api/captcha.php?action=get`)
const submit = async () => {
  if(!form.email || !form.password || !form.repassword){
    layer.msg('请填写完整的表单')
    return
  }
  if(!/^[a-zA-Z0-9\-_]+@[a-zA-Z0-9\-\.]+\.[a-zA-Z0-9]+$/.test(form.email)){
    layer.msg('邮箱格式不正确')
    return
  }
  if(form.password != form.repassword){
    layer.msg('两次密码不一致')
    return
  }
  let res = await myFetch('/api/user.php', {
    captcha: form.captcha,
    email: form.email,
    password: form.password,
    action: 'register',
  })
  if(res.code == 0){
    await router.push('/register_success')
    return
  }
  layer.msg(res.msg)
  changeCaptcha()
}
const changeCaptcha = () => {
  captcha.value = `${import.meta.env.VITE_HOST}/api/captcha.php?action=get&r=${Math.random()}`
}
</script>

<style scoped lang="less">
@import url('../assets/form.less');
</style>