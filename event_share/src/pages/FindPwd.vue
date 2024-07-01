<template>
  <form class="div_form">
    <h4>找回密码</h4>
    <div class="mb-3">
      <label for="email" class="form-label">邮箱</label>
      <input type="email" class="form-control" id="email" name="email" placeholder="请输入你要找回密码的邮箱" required
        v-model="form.email" autocomplete="off">
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
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { myFetch } from '../tools/net.js'
const router = useRouter()
const form = ref({
  email: '',
  captcha: '',
})
const captcha = ref(`${import.meta.env.VITE_HOST}/api/captcha.php?action=get`)
const submit = async ()=>{
  if(!/^[a-zA-Z0-9\-_]+@[a-zA-Z0-9\-\.]+\.[a-zA-Z0-9]+$/.test(form.value.email)){
    layer.msg('邮箱格式不正确')
    return
  }
  const res = await myFetch('/api/user.php', {
    captcha: form.value.captcha,
    email: form.value.email,
    action: 'find_pwd',
  })
  layer.msg(res.msg)
  if(res.code == 0){
    router.push({path: '/changepwd', query: {email: form.value.email}})
    return
  }
  changeCaptcha()
}
const changeCaptcha = () => {
  captcha.value = `${import.meta.env.VITE_HOST}/api/captcha.php?action=get&r=${Math.random()}`
}
</script>

<style lang="less" scoped>
@import url('../assets/form.less');
</style>