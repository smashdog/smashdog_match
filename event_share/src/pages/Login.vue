<template>
  <form class="div_form">
    <h4>登录</h4>
    <div class="mb-3">
      <label for="email" class="form-label">邮箱</label>
      <input type="email" class="form-control" id="email" name="email" placeholder="请输入你的邮箱"
        v-model="form.email" autocomplete="off">
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">密码</label>
      <input type="password" class="form-control" id="password" name="password" placeholder="请输入密码"
        v-model="form.password" autocomplete="off">
    </div>
    <div class="mb-3">
      <button type="button" class="btn btn-success btn-sm" @click="submit()">提交</button>
      <button type="button" class="btn btn-secondary btn-sm" @click="$router.push(`/`)">取消</button>
    </div>
  </form>
  <Verify
      mode="pop"
      :captchaType="captchaType"
      :imgSize="{width:'400px',height:'200px'}"
      ref="verify"
      @success="handleSuccess"
    ></Verify>
</template>

<script setup>
import { ref } from 'vue'
import Verify from '../components/verifition/Verify.vue'
import { useRouter } from 'vue-router'
import { myFetch } from '../tools/net.js'
const form = {
  email: '',
  password: '',
}
const router = useRouter()
const verify = ref(null)
const captchaType = ref('')
const handleSuccess = (res)=>{
  if(res){
    localStorage.setItem('captchaVerification', res.captchaVerification ?? '')
    submit()
  }
}
const submit = async () => {
  if(!form.email || !form.password){
    layer.msg('请填写完整的表单')
    return
  }
  if(!/^[a-zA-Z0-9\-_]+@[a-zA-Z0-9\-\.]+\.[a-zA-Z0-9]+$/.test(form.email)){
    layer.msg('邮箱格式不正确')
    return
  }
  if(!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{8,}$/.test(form.password)){
    layer.msg('密码必须包含大小写字母、数字和特殊字符，长度至少为8位')
    return
  }
  if(!localStorage.getItem('captchaVerification')){
    captchaType.value = 'blockPuzzle'
    verify.value.show()
    return
  }
  let res = await myFetch('/api/user.php', {
    captchaVerification: localStorage.getItem('captchaVerification'),
    email: form.email,
    password: form.password,
    action: 'login',
  })
  localStorage.removeItem('captchaVerification')
  if(res.code == 0){
    localStorage.setItem('token', res.data.token)
    await router.push('/sign')
    return
  }
  layer.msg(res.msg)
}
</script>

<style scoped lang="less">
@import url('../assets/form.less');
</style>