<template>
  <h3>{{ game.title }}</h3>
  <div class="top">
    <div class="btn-group" role="group">
      <button type="button" class="btn btn-sm btn-success" @click="showAdd = true" v-if="(game.game_status == 0 || (game.game_status == 4 && game.game_format == 3)) && this.list.count < (game.group_nums * game.group_count)">添加选手</button>
      <button type="button" class="btn btn-sm btn-primary" @click="randPlayers()" v-if="game.game_status == 0 && game.game_format != 3">打乱选手</button>
      <button type="button" class="btn btn-sm btn-secondary" @click="$router.push('/games')">返回比赛列表</button>
    </div>
  </div>
  <div class="add_player window" v-show="showAdd">
    <form class="div_form">
      <div class="mb-3">
        <label for="player_title" class="form-label">选手名称</label>
        <input type="text" class="form-control" id="player_title" name="player_title" placeholder="请输入选手名称" v-model="form.title" autocomplete="off">
      </div>
      <div class="mb-3">
        <label for="fast_copy" class="form-label">快速复制内容（例如街霸6用户码）</label>
        <input type="text" class="form-control" id="fast_copy" name="fast_copy" placeholder="请输入快速复制内容" v-model="form.fast_copy" autocomplete="off">
      </div>
      <div class="mb-3">
        <label for="sort_num" class="form-label">排序</label>
        <input type="number" step="1" min="0" class="form-control" id="sort_num" name="sort_num" placeholder="数字越大排序越靠后" v-model="form.sort_num" autocomplete="off">
      </div>
      <div class="mb-3">
        <button type="button" class="btn btn-sm btn-success" @click="submit()">提交</button>
        <button type="button" class="btn btn-sm btn-secondary" @click="showAdd = false">取消</button>
      </div>
    </form>
  </div>
  <div class="players">
    <div>
      <table class="table table-hover">
        <thead>
          <th>选手名称</th>
          <th>快速复制内容</th>
          <th>排序</th>
          <th>操作</th>
        </thead>
        <tbody>
          <tr v-for="(player, index) in list.data">
            <td>{{ player.title }}</td>
            <td @click="fastCopy(player.fast_copy)">{{ player.fast_copy }}</td>
            <td>{{ player.sort_num }}</td>
            <td>
              <button type="button" class="btn btn-sm btn-success" @click="editPlayer(player.id)">编辑</button>
              <button type="button" class="btn btn-sm btn-danger" @click="deletePlayer(player.id)" v-if="game.game_status == 0 || (game.game_status == 4 && game.game_format == 3)">删除</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <mypage :page="list.page" :maxPage="list.maxPage" :count="list.count" @getList="getList"></MyPage>
</template>

<script>
import mypage from "../components/page.vue";
import { writeText } from '@tauri-apps/api/clipboard'
export default {
  components: {mypage},
  data() {
    return {
      list:{
        data: [],
        page: 1,
        maxPage: 1,
        count: 0,
        pageSize: 20,
      },
      game_id: 0,
      showAdd: false,
      game: {
        title: '',
        game_status: 0,
        game_format: 1,
        group_nums: 0,
        group_count: 0,
      },
      players: [],
      form: {
        id: 0,
        title: '',
        fast_copy: '',
        sort_num: 0,
      },
    }
  },
  async mounted() {
    this.game_id = this.$route.params.id
    const game = await this.$getGame(this.game_id)
    if(typeof game == 'string'){
      layer.msg(game, () => {
        this.$router.push('/games')
      })
    }
    this.game = game
    this.getList()
  },
  methods: {
    async fastCopy(text) {
      if (text.length == 0) {
        return
      }
      await writeText(text)
      layer.msg('复制成功')
    },
    async randPlayers(){
      layer.load()
      let players = await this.$db.select("select * from players where game_id = ? and is_display = 1", [this.game.id])
      players.sort(() => {
        return (0.5 - Math.random())
      })
      for (let i = 0; i < players.length; i++) {
        await this.$db.execute("update players set sort_num = ? where id = ?", [i, players[i].id])
      }
      this.getList()
      layer.closeAll()
    },
    changePageSize(pageSize){
      this.list.pageSize = pageSize
      this.getList()
    },
    async submit() {
      this.showAdd = false
      if (this.form.title == '') {
        layer.msg('请输入选手名称')
        return
      }
      if(this.game.game_status == 1){
        layer.msg('比赛已经开始不能添加选手')
        return
      }
      if(this.game.game_format != 2 && this.game_status == 2){
        layer.msg('比赛类型为双败或单败时，比赛结束后不能添加选手')
        return
      }
      const nums = await this.$db.select("select count(*) as mycount from players where game_id = ?", [this.game_id])
      const maxUserNum = this.game.group_nums * 26
      if(nums[0].mycount >= maxUserNum){
        layer.msg(`参赛选手人数不能大于${maxUserNum}人（小组人数*26个队伍）`)
        return
      }
      let sqlreturn
      if (this.form.id === 0) {
        sqlreturn = await this.$db.select("select id from players where title = ? and game_id = ?", [this.form.title, this.game_id])
        if (sqlreturn.length > 0) {
          layer.msg('该选手已存在')
          return
        }
        await this.$db.execute("insert into players (title, game_id,fast_copy,sort_num) values (?, ?, ?, ?)", [this.form.title, this.game_id, this.form.fast_copy, this.form.sort_num])
      } else {
        sqlreturn = await this.$db.select("select id from players where game_id = ? and title = ? and id <> ?", [this.game_id, this.form.title, this.form.id])
        if (sqlreturn.length > 0) {
          layer.msg('该选手已存在')
          return
        }
        await this.$db.execute("update players set title = ?, fast_copy = ?, sort_num = ? where id = ?", [this.form.title, this.form.fast_copy, this.form.sort_num, this.form.id])
      }
      this.form = {id: 0, title: '', fast_copy: '', sort_num: 0}
      await this.getList()
    },
    async getList(page) {
      if(!page){
        page = 1
      }else if(page == this.list.page){
        return
      }
      this.list.page = page
      this.list = await this.$page('players', this.list.page, localStorage.getItem('pageSize'), ' game_id = ' + this.game_id + ' and is_display = 1', '*', 'sort_num asc, id asc')
    },
    async editPlayer(id) {
      const sqlreturn = await this.$db.select("select * from players where id = ?", [id])
      if (sqlreturn.length <= 0) {
        layer.msg('未找到该选手')
        return
      }
      this.showAdd = true
      this.form = sqlreturn[0]
    },
    async deletePlayer(id) {
      layer.confirm('确定删除该选手吗？', async () => {
        const sqlreturn = await this.$db.select("select game_status from games where id = ?", [this.game_id])
        if (sqlreturn.length > 0) {
          if (sqlreturn[0].game_status > 0) {
            layer.msg('比赛已经开始不能删除')
            return
          }
          await this.$db.execute("delete from players where id = ?", [id])
          this.getList()
          layer.closeAll()
        } else {
          layer.msg('未找到该比赛')
          return
        }
      })
    }
  }
}
</script>