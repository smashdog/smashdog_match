<template>
  <div class="home">
    <div class="sub">
      <p>{{ msg }}</p>
      <p><button :class="{btn: true, 'btn-primary': !status, 'btn-success': status}" @click="toUrl()">{{ btn }}</button></p>
    </div>
  </div>
</template>

<script setup>
import { myFetch } from '../tools/net.js'
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
const route = useRoute()
const router = useRouter()
const vali_key = ref('')
const msg = ref('')
const status = ref(null)
const btn = ref('返回首页')
const toUrl = () => {
  switch(btn){
    case '立即报名':
      router.push('/sign')
      break
    default:
      router.push('/')
      break
  }
}
onMounted(async () => {
  status.value = false
  if(route.query.vali_key){
    vali_key.value = route.query.vali_key
  }else{
    msg.value = '参数错误'
    return
  }
  let res = await myFetch('/api/user.php', {
    action: 'verify_email',
    vali_key: vali_key.value
  })
  msg.value = res.msg
  if(res.code == 0){
    localStorage.setItem('token', res.data.token)
    status.value = true
    if(localStorage.getItem('save')){
      btn.value = '立即报名'
      setTimeout(() => {
        router.push('/sign')
      }, 3000)
    }
  }
})
</script>

<style scoped lang="less">
@import url('../assets/home.less');
.sub{
  width: 100%;
  display: flex;
  flex: 1;
  flex-wrap: wrap;
  >*{
    width: 100%;
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
  }
}
</style>