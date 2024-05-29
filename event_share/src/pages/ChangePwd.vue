<template>
  <form class="div_form">
    <h4>修改密码</h4>
    <div class="mb-3">
      <label for="email" class="form-label">邮箱</label>
      <input type="email" class="form-control" id="email" name="email" placeholder="请尽量使用国内邮箱，国外邮箱有可能收不到邮件" required
        v-model="form.email" autocomplete="off">
    </div>
    <div class="mb-3">
      <label for="code" class="form-label">邮箱验证码</label>
      <input type="text" class="form-control" id="code" name="code" placeholder="请输入邮箱验证码" required
        v-model="form.code" autocomplete="off" maxlength="6">
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
      <button type="button" class="btn btn-success btn-sm" @click="submit()">提交</button>
      <button type="button" class="btn btn-secondary btn-sm" @click="$router.push(`/`)">取消</button>
    </div>
  </form>
  <Verify mode="pop" :captchaType="captchaType" :imgSize="{ width: '400px', height: '200px' }" ref="verify"
    @success="handleSuccess"></Verify>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import Verify from '../components/verifition/Verify.vue'
import { myFetch } from '../tools/net.js'
const router = useRouter()
const route = useRoute()
const form = ref({
  email: '',
  password: '',
  repassword: '',
  code: '',
})
const verify = ref(null)
const captchaType = ref('')
const handleSuccess = (res) => {
  if (res) {
    localStorage.setItem('captchaVerification', res.captchaVerification ?? '')
    submit()
  }
}
const submit = async () => {
  if (!/^[a-zA-Z0-9\-_]+@[a-zA-Z0-9\-\.]+\.[a-zA-Z0-9]+$/.test(form.value.email)) {
    layer.msg('邮箱格式不正确')
    return
  }
  if(!localStorage.getItem('captchaVerification')){
    captchaType.value = 'blockPuzzle'
    verify.value.show()
    return
  }
  const res = await myFetch('/api/user.php', {
    captchaVerification: localStorage.getItem('captchaVerification'),
    email: form.value.email,
    password: form.value.password,
    repassword: form.value.repassword,
    code: form.value.code,
    action: 'change_pwd',
  })
  localStorage.removeItem('captchaVerification')
  layer.msg(res.msg)
  if (res.code == 0) {
    router.push('/login')
  }
}
onMounted(() => {
  if(route.query.email){
    form.value.email = route.query.email
  }
})
</script>

<style lang="less" scoped>
@import url('../assets/form.less');
</style>