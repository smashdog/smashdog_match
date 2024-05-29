<template>
  <form class="div_form">
    <h4>找回密码</h4>
    <div class="mb-3">
      <label for="email" class="form-label">邮箱</label>
      <input type="email" class="form-control" id="email" name="email" placeholder="请输入你要找回密码的邮箱" required
        v-model="form.email" autocomplete="off">
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
import { useRouter } from 'vue-router'
import Verify from '../components/verifition/Verify.vue'
import { myFetch } from '../tools/net.js'
const router = useRouter()
const form = ref({
  email: ''
})
const verify = ref(null)
const captchaType = ref('')
const handleSuccess = (res)=>{
  if(res){
    localStorage.setItem('captchaVerification', res.captchaVerification ?? '')
    submit()
  }
}
const submit = async ()=>{
  if(!/^[a-zA-Z0-9\-_]+@[a-zA-Z0-9\-\.]+\.[a-zA-Z0-9]+$/.test(form.value.email)){
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
    action: 'find_pwd',
  })
  localStorage.removeItem('captchaVerification')
  layer.msg(res.msg)
  if(res.code == 0){
    router.push({path: '/changepwd', query: {email: form.value.email}})
  }
}
</script>

<style lang="less" scoped>
@import url('../assets/form.less');
</style>