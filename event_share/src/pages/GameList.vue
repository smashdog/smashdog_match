<template>
  <div class="list" v-if="show">
    <h4>key管理<button class="btn btn-primary btn-sm" @click="$router.push('/key_manager')">返回key列表</button></h4>
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>比赛名称</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in list" :key="index">
          <td>{{ item.game.id }}</td>
          <td>{{ item.game.title }}</td>
          <td>
            <button class="btn btn-primary btn-sm" @click="copy(`/#/sign?key=${$route.query.key}&id=${item.game.id}`)" v-if="item.game.game_status == 0">复制报名链接</button>
            <button class="btn btn-primary btn-sm" @click="copy(`/#/matchs/${$route.query.key}/${item.game.id}`)" v-else>复制对阵表链接</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { myFetch } from '@/tools/net'
import useClipboard from 'vue-clipboard3'
const { toClipboard } = useClipboard()
const route = useRoute()
const router = useRouter()
const key = ref('')
const list = ref([])
const show = ref(false)
const copy = async (text) => {
  await toClipboard(`${location.origin}${text}`)
  layer.msg('复制成功')
}
onMounted(async () => {
  if(!route.query.key){
    layer.msg('缺少参数')
    router.go(-1)
  }
  const res = await myFetch('/api/keyManager.php', {
    key: route.query.key,
    action: 'game_list',
    token: localStorage.getItem('token')
  })
  if(res.code == 0){
    list.value = res.data.list
    for(let i = 0; i < list.value.length; i++){
      if(typeof list.value[i].game == 'string'){
        list.value[i].game = JSON.parse(list.value[i].game)
      }
    }
    show.value = true
  }else{
    layer.msg(res.msg)
    router.go(-1)
  }
})
</script>

<style scoped lang="less">
@import url('../assets/home.less');
</style>