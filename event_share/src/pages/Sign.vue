<template>
  <form class="div_form" v-if="!is_sign">
    <h4>{{ game.title }}报名</h4>
    <div class="mb-3">
      <label for="nickname" class="form-label">昵称</label>
      <input type="text" class="form-control" id="nickname" name="nickname" placeholder="请输入你的比赛中显示的昵称（主播请不要带上平台名称）"
        v-model="form.nickname" autocomplete="off">
    </div>
    <div class="mb-3">
      <label for="weixin" class="form-label">微信号</label>
      <input type="text" class="form-control" id="weixin" name="weixin" placeholder="请输入微信号（重要，请认真填写）"
        v-model="form.weixin" autocomplete="off">
    </div>
    <div class="mb-3">
      <label for="fastcopy" class="form-label">用户码</label>
      <input type="text" class="form-control" id="fastcopy" name="fastcopy" placeholder="请输入游戏的用户码（重要，请认真填写）"
        v-model="form.fastcopy" autocomplete="off">
    </div>
    <div class="mb-3">
      <button type="button" class="btn btn-success btn-sm" @click="submit()">提交</button>
      <button type="button" class="btn btn-secondary btn-sm" @click="form.action == 'sign' ? $router.push(`/`) : (form = {
        nickname: '',
        weixin: '',
        fastcopy: '',
        action: 'sign',
        k: null
      })">取消</button>
    </div>
  </form>
  <div v-if="is_sign" class="home">
    <p v-if="list.list.length == 0">
      {{ msg }}
    </p>
    <p>
      <button type="button" class="btn btn-success btn-sm" @click="$router.push('/')">返回首页</button>
    </p>
  </div>
  <div class="list" v-if="list.list.length > 0">
    <h4>《{{ list.game.title }}》已报名用户</h4>
    <div>
      <table class="table">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col" v-for="(v,k) in list.title">{{ v }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(v,k) in list.list">
            <td>{{ k + 1 }}</td>
            <td v-for="(vv,kk) in v">
              <span v-if="kk != 'action'">{{ vv }}</span>
              <span v-else-if="kk == 'action'">
                <button type="button" class="btn btn-success btn-sm" @click="edit(v)">编辑</button>
                <button type="button" class="btn btn-danger btn-sm" @click="del(v.uid)" v-if="game.game_status == 0">删除</button>
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <Verify
      mode="pop"
      :captchaType="captchaType"
      :imgSize="{width:'400px',height:'200px'}"
      ref="verify"
      @success="handleSuccess"
    ></Verify>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import Verify from '../components/verifition/Verify.vue'
import { myFetch } from '../tools/net.js'
const verify = ref(null)
const captchaType = ref('')
const router = useRouter()
const route = useRoute()
const game = ref({})
const is_sign = ref(true)
const msg = ref('')
const form = ref({
  nickname: '',
  weixin: '',
  fastcopy: '',
  k: null,
  action: 'sign'
})
const list = ref({
  title: {},
  game: {},
  list: []
});
const handleSuccess = (res)=>{
  if(res){
    localStorage.setItem('captchaVerification', res.captchaVerification ?? '')
    submit()
  }
}
const submit = async () => {
  const msg = {
    nickname: '昵称',
    weixin: '微信号',
    fastcopy: '用户码',
  }
  for(let k in form.value){
    if(!form.value[k] && k != 'k' && k != 'action'){
      layer.msg(`请填写${msg[k]}`)
      return
    }
  }
  if(!localStorage.getItem('captchaVerification')){
    captchaType.value = 'blockPuzzle'
    verify.value.show()
    return
  }
  const values = {
    nickname: form.value.nickname,
    weixin: form.value.weixin,
    fastcopy: form.value.fastcopy,
    id: route.query.key && route.query.id ? route.query.id : localStorage.getItem('save') ? JSON.parse(localStorage.getItem('save')).id : '',
    key: route.query.key && route.query.id ? route.query.key : localStorage.getItem('save') ? JSON.parse(localStorage.getItem('save')).key : '',
    action: form.value.action,
    captchaVerification: localStorage.getItem('captchaVerification'),
    token: localStorage.getItem('token'),
    k: form.value.k
  }
  let res = await myFetch('/api/sign.php', values)
  localStorage.removeItem('captchaVerification')
  layer.msg(res.msg)
  if(res.code == 0){
    if(form.value.action == 'sign'){
      is_sign.value = true
    }
    await getList()
    return
  }
}
const getList = async () => {
  let save = JSON.parse(localStorage.getItem('save'))
  let res = await myFetch('/api/sign.php', {key: save.key, id: save.id, action: 'list', token: localStorage.getItem('token')})
  if(res.code == 0){
    list.value = res.data
  }
}
const del = async (uid) => {
  const index = layer.confirm('确定删除？', async () => {
    let save = JSON.parse(localStorage.getItem('save'))
    let res = await myFetch('/api/sign.php', {key: save.key, id: save.id, k: uid, action: 'del', token: localStorage.getItem('token')})
    layer.close(index)
    if(res.code == 0){
      await getList()
      return
    }
    layer.msg(res.msg)
  })
}
const edit = (item) => {
  form.value = {
    nickname: item.nickname,
    weixin: item.weixin,
    fastcopy: item.fastcopy,
    k: item.uid,
    action: 'edit'
  }
  is_sign.value = false
}
onMounted(async () => {
  let key = '', id = ''
  if(route.query.key && route.query.id){
    key = route.query.key
    id = route.query.id
  }else if(localStorage.getItem('save')){
    let save = JSON.parse(localStorage.getItem('save'))
    key = save.key
    id = save.id
  }else{
    layer.msg('报名参数错误')
    return
  }
  if(!localStorage.getItem('token')){
    router.push('/login')
    return
  }
  let res = await myFetch('/api/sign.php', {key, id, token: localStorage.getItem('token'), action: 'is_sign'})
  if(res.code != 0){
    if(res.code == -1){
      layer.msg(res.msg)
      await getList()
    }else{
      is_sign.value = true
      msg.value = res.msg
      return
    }
  }else{
    is_sign.value = false
    game.value = res.data.game
    try {
      if(typeof res.data.list[0].action != 'undefined'){
        list.value = res.data
      }
    } catch (error) {
      
    }
  }
})
</script>

<style scoped lang="less">
@import url('../assets/form.less');
@import url('../assets/home.less');
</style>