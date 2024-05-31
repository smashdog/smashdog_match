<template>
  <div class="list" v-if="show">
    <h4>
      <span>key管理</span>
      <button class="btn btn-primary btn-sm" @click="$router.push('/')">返回首页</button>
      <button class="btn btn-primary btn-sm" @click="createKey()">添加key</button>
    </h4>
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>key</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in data" :key="index">
          <td>{{ index }}</td>
          <td>{{ item }}</td>
          <td>
            <button class="btn btn-primary btn-sm" @click="$router.push({path: '/game_list', query: {key: item}})">查看比赛</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { myFetch } from '@/tools/net';
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
const router = useRouter()
const data = ref([])
const show = ref(false)
const createKey = () => {
  const index = layer.confirm('确定要添加key吗？', async () => {
    const res = await myFetch('/api/keyManager.php', {
      token: localStorage.getItem('token'),
      action: 'create'
    })
    if(res.code == 0){
      layer.msg('添加成功')
      await getList()
    }else{
      layer.msg(res.msg)
    }
    layer.close(index)
  })
}
const getList = async () => {
  const res = await myFetch('/api/keyManager.php', {
    token: localStorage.getItem('token'),
    action: 'list'
  })
  if(res.code == 0){
    data.value = res.data.list
    show.value = true
  }else{
    layer.msg(res.msg)
    router.push('/')
  }
}
onMounted(async () => {
  await getList()
})
</script>

<style scoped lang="less">
@import url('../assets/home.less');
</style>