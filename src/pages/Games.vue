<template>
  <div class="top">
    <div class="btn-group" role="group">
      <button type="button" class="btn btn-success btn-sm" @click="showAddFuc()">添加比赛</button>
      <button type="button" class="btn btn-primary btn-sm" @click="openDir()">打开配置文件的目录</button>
      <button type="button" class="btn btn-primary btn-sm" @click="openApiConfig()">配置对阵分享api</button>
      <!-- 调式 -->
      <button type="button" class="btn btn-danger btn-sm" @click="clearData()" v-if="$debug">清除数据</button>
    </div>
  </div>
  <div class="add_game window" v-show="showAdd">
    <form class="div_form">
      <div class="mb-3">
        <label for="game_title" class="form-label">比赛名称</label>
        <input type="text" class="form-control" id="game_title" name="game_title" placeholder="请输入比赛名称"
          v-model="form.game_title" autocomplete="off">
      </div>
      <div class="mb-3" v-show="form.game_format != 3">
        <label for="group_nums" class="form-label">每小组人数</label>
        <input type="number" class="form-control" id="group_nums" name="group_nums" placeholder="请输入小组人数"
          v-model="form.group_nums" value="2" step="1" min="2" autocomplete="off" max="256">
      </div>
      <div class="mb-3" v-show="form.game_format != 3">
        <label for="group_count" class="form-label">多少个小组</label>
        <input type="number" class="form-control" id="group_count" name="group_count" placeholder="多少个小组"
          v-model="form.group_count" value="1" step="1" min="1" autocomplete="off" max="128">
      </div>
      <div class="mb-3">
        <label for="game_format1" class="form-label">赛制</label>
        <div class="input_inline">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="game_format" id="game_format1" value="1" checked
              v-model="form.game_format">
            <label class="form-check-label" for="game_format1">
              双败
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="game_format" id="game_format2" value="2"
              v-model="form.game_format">
            <label class="form-check-label" for="game_format2">
              单败
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="game_format" id="game_format3" value="3"
              v-model="form.game_format">
            <label class="form-check-label" for="game_format3">
              赞助赛
            </label>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <label for="game_sort" class="form-label">排序（数字越大越靠前）</label>
        <input type="number" class="form-control" id="game_sort" name="game_sort" placeholder="请输入排序"
          v-model="form.game_sort" value="0" step="1" min="0" autocomplete="off">
      </div>
      <div class="mb-3">
        <button type="button" class="btn btn-success btn-sm" @click="submit()">提交</button>
        <button type="button" class="btn btn-secondary btn-sm" @click="showAddFuc()">取消</button>
      </div>
    </form>
  </div>
  <div class="window" v-show="showStartForm">
    <form class="div_form">
      <div class="mb-3">
        <label for="is_rand1" class="form-label">怎样开始？</label>
        <div class="input_inline">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="is_rand" id="is_rand1" value="0" checked v-model="is_rand">
            <label class="form-check-label" for="is_rand1">
              按选手排序开始
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="is_rand" id="is_rand2" value="1" v-model="is_rand">
            <label class="form-check-label" for="is_rand2">
              打乱选手顺序开始
            </label>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <button type="button" class="btn btn-success btn-sm" @click="startGames(start_id)">开始</button>
        <button type="button" class="btn btn-secondary btn-sm" @click="showStartForm = false">取消</button>
      </div>
    </form>
  </div>
  <div class="window" v-show="configApiShow">
    <form class="div_form">
      <div class="mb-3">
        <label for="apiUrl" class="form-label">api地址</label>
        <input type="text" class="form-control" id="apiUrl" name="apiUrl" placeholder="请输入api地址" v-model="config.shareApi.url" autocomplete="off">
      </div>
      <div class="mb-3">
        <label for="apiKey" class="form-label">key</label>
        <input type="text" class="form-control" id="apiKey" name="apiKey" placeholder="请输入key" v-model="config.shareApi.key" autocomplete="off">
      </div>
      <div class="mb-3">
        <div class="form-label">不会配置的小伙伴请加微信“mydogup”获取帮助</div>
      </div>
      <div class="mb-3">
        <button type="button" class="btn btn-success btn-sm" @click="saveApiConfig()">提交</button>
        <button type="button" class="btn btn-secondary btn-sm" @click="closeApiConfig()">取消</button>
      </div>
    </form>
  </div>
  <div class="list_games">
    <table class="table table-hover">
      <thead>
        <th>比赛名称</th>
        <th>赛制</th>
        <th>每小组人数</th>
        <th>共多少个小组</th>
        <th>已添加选手数</th>
        <th>比赛状态</th>
        <th>排序</th>
        <th>操作</th>
      </thead>
      <tbody>
        <tr v-for="(item, index) in list.data" :key="index">
          <td>{{ item.title }}</td>
          <td>{{ game_format[item.game_format] }}</td>
          <td>{{ item.game_format != 3 ? item.group_nums : '' }}</td>
          <td>{{ item.game_format != 3 ? item.group_count : '' }}</td>
          <td>{{ item.count }}</td>
          <td>{{ item.game_format == 3 ? (item.game_status == 1 ? '进行中' : game_status[item.game_status]) : game_status[item.game_status] }}</td>
          <td>{{ item.game_sort }}</td>
          <td>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-primary btn-sm" @click="viewplayers(item.id)">查看参赛选手</button>
              <button type="button" class="btn btn-primary btn-sm" @click="startSign(item, index)" v-if="item.game_format != 3 && !item.sign && config.shareApi.url.length > 0 && config.shareApi.key.length > 0 && item.game_status == 0">开始报名</button>
              <button type="button" class="btn btn-primary btn-sm" @click="copySignUrl(item.id)" v-if="item.game_format != 3 && item.sign">复制报名链接</button>
              <button type="button" class="btn btn-primary btn-sm" v-if="item.game_status > 0"
                @click="viewmatchs(item)">查看对局</button>
              <button type="button" class="btn btn-success btn-sm" v-if="item.game_status == 0 || (item.game_format == 3 && (item.game_status == 0 || item.game_status == 3))"
                @click="startGames1(item.id)">开始</button>
              <button type="button" class="btn btn-danger btn-sm" v-if="item.game_status == 1"
                @click="stopGames(item.id)">结束</button>
              <button type="button" class="btn btn-success btn-sm" v-if="item.game_status == 0"
                @click="editGames(item.id)">编辑</button>
              <button type="button" class="btn btn-danger btn-sm" v-if="item.game_status == 0"
                @click="deleteGames(item.id)">删除</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <mypage :page="list.page" :maxPage="list.maxPage" :count="list.count" @getList="getList"></mypage>
</template>
<script>
import { writeText } from '@tauri-apps/api/clipboard'
import mypage from "../components/page.vue"
import { open } from "@tauri-apps/api/shell"
import { appDataDir } from "@tauri-apps/api/path"
import { BaseDirectory, writeTextFile } from '@tauri-apps/api/fs'
export default {
  components: { mypage },
  async mounted() {
    let config = {}
    if(!localStorage.getItem('config')){
      config = {
        shareApi: {
          url: '',
          key: ''
        }
      }
    }else{
      config = JSON.parse(localStorage.getItem('config'))
    }
    this.config = config
    await this.getList()
  },
  data() {
    return {
      config: {
        shareApi: {
          url: '',
          key: ''
        }
      },
      configApiShow: false,
      is_rand: 0,
      showStartForm: false,
      list: {
        data: [],
        page: 1,
        maxPage: 1,
        count: 0,
        pageSize: 20,
      },
      games: [],
      showAdd: false,
      form: {
        id: 0,
        game_title: '',
        group_nums: 16,
        game_format: 1,
        game_sort: 0,
        group_wins: 2,
        group_count: 1,
      },
      game_format: {
        1: '双败',
        2: '单败',
        3: '赞助赛',
      },
      game_status: {
        0: '未开始',
        1: '小组赛进行中',
        2: '决赛进行中',
        3: '已结束',
      },
      start_id: 0,
    }
  },
  methods: {
    stopGames(id){
      layer.confirm('结束比赛将不能再更改比赛信息，确定结束比赛吗？', async index => {
        try {
          localStorage.removeItem(`signMatch${id}`)
        } catch (error) {
        }
        await writeTextFile('obs/game_name.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/match.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p1_title.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p2_title.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p1_score.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p2_score.txt', '', { dir: BaseDirectory.App })
        await this.$db.execute("update games set game_status = 3 where id = ?", [id])
        layer.close(index)
        await this.getList()
      })
    },
    saveApiConfig (){
      localStorage.setItem('config', JSON.stringify(this.config))
      this.configApiShow = false
    },
    openApiConfig(){
      localStorage.setItem('config', JSON.stringify(this.config))
      this.configApiShow = true
    },
    closeApiConfig(){
      this.config = JSON.parse(localStorage.getItem('config'))
      this.configApiShow = false
    },
    async openDir(){
      const appDataDirPath = await appDataDir()
      await open(appDataDirPath+'obs')
    },
    async getList(page) {
      if (!page) {
        page = 1
      } else if (page == this.list.page) {
        return
      }
      this.list.data = []
      this.list.page = page
      const temp = await this.$page('games', this.list.page, localStorage.getItem('pageSize'), '', '*', 'game_sort DESC, id DESC')
      this.list.page = temp.page
      this.list.maxPage = temp.maxPage
      this.list.count = temp.count
      this.list.pageSize = temp.pageSize
      for (let i = 0; i < temp.data.length; i++) {
        const temp1 = await this.$db.select('select count(*) as mycount from players where game_id = ? and is_display = 1', [temp.data[i].id])
        let temp2 = temp.data[i]
        temp2.count = temp1[0].mycount
        if(localStorage.getItem(`signMatch${temp.data[i].id}`)){
          temp2['sign'] = true
        }else{
          temp2['sign'] = false
        }
        this.list.data.push(temp2)
      }
    },
    showAddFuc() {
      this.showAdd = this.showAdd ? false : true
      if (this.showAdd === false) {
        this.form = {
          id: 0,
          game_title: '',
          group_nums: 2,
          game_format: 1,
          group_wins: 2,
        }
      }
    },
    async submit() {
      if (this.form.game_title == '') {
        layer.msg('请输入比赛名称')
        return
      }
      if (this.form.group_nums == '') {
        layer.msg('请输入小组人数')
        return
      }
      if (this.form.group_nums < 3 && this.form.game_format == 1) {
        layer.msg('双败小组人数不能小于3')
        return
      }
      if (this.form.group_nums > 256) {
        layer.msg('小组人数不能大于256')
        return
      }
      if (this.form.group_nums < 2) {
        layer.msg('小组人数不能小于2')
        return
      }
      if (this.form.id > 0) {
        const sqlreturn = await this.$db.select("select id from games where id = ?", [this.form.id])
        if (sqlreturn.length <= 0) {
          layer.msg('未找到该比赛')
          return
        }
        await this.$db.execute("update games set title = ?, group_nums = ?, game_format = ?, game_sort = ?, group_wins = ?, group_count = ? where id = ?", [this.form.game_title, this.form.group_nums, this.form.game_format, this.form.game_sort, this.form.group_wins, this.form.group_count, this.form.id])
      } else {
        await this.$db.execute("insert into games (title, group_nums, game_format, game_sort, group_wins, group_count) values (?, ?, ?, ?, ?, ?)", [this.form.game_title, this.form.group_nums, this.form.game_format, this.form.game_sort, this.form.group_wins, this.form.group_count])
      }
      this.getList()
      this.form.game_title = ''
      this.form.group_nums = 2
      this.form.game_format = 1
      this.form.group_count = 1
      this.showAdd = false
    },
    viewplayers(id) {
      this.$router.push('/players/' + id)
    },
    async startSign(game, k){
      const config = JSON.parse(localStorage.getItem('config'))
      let res = await this.$tfetch(config.shareApi.url.replace('index.php', 'sign.php'), {
        game: JSON.stringify(game),
        group_count: `${game.group_count}`,
        winner_matchs: '[]',
        loser_matchs: '[]',
        id: game.id,
        winner_idx: 0,
        looser_idx: 0,
        key: config.shareApi.key,
        action: 'create_sign'
      })
      if(res.code == 0){
        localStorage.setItem(`signMatch${game.id}`, 1)
        this.list.data[k].sign = true
      }
      layer.msg(res.msg)
    },
    async copySignUrl(id){
      let url = this.config.shareApi.url.match(/(https?:\/\/[^/]+)\//i)
      await writeText(`${url[0]}#/sign?key=${this.config.shareApi.key}&id=${id}`)
      layer.msg('已把报名链接复制到剪贴板，请粘贴到想要分享的地方吧！')
    },
    async editGames(id) {
      const sqlreturn = await this.$db.select("select * from games where id = ?", [id])
      if (sqlreturn.length > 0) {
        this.form.id = id
        this.form.game_title = sqlreturn[0].title
        this.form.group_nums = sqlreturn[0].group_nums
        this.form.game_format = sqlreturn[0].game_format
        this.form.group_count = sqlreturn[0].group_count
        this.showAdd = true
      } else {
        layer.msg('未找到该比赛')
      }
    },
    async deleteGames(id) {
      layer.confirm('确定删除该比赛吗？', async () => {
        const sqlreturn = await this.$db.select("select id,game_status from games where id = ?", [id])
        layer.closeAll()
        if (sqlreturn.length > 0) {
          if (sqlreturn[0].game_status == 1 || sqlreturn[0].game_status == 2) {
            layer.msg('比赛已经开始不能删除')
            return
          }
          await this.$db.execute("delete from games where id = ?", [id])
          await this.$db.execute("delete from players where game_id = ?", [id])
          await this.$db.execute("delete from matchs where game_id = ?", [id])
          this.getList()
        } else {
          layer.msg('未找到该比赛')
          return
        }
      })
    },
    async startGames1(id){
      const startGame = await this.$db.select("select id from games where game_status in (1, 2)")
      if(startGame.length > 0){
        layer.msg('已有比赛正在进行中，请先结束比赛')
        return
      }
      const game = await this.$db.select("select * from games where id = ?", [id])
      if (game.length > 0) {
        if (game[0].game_status == 1 || game[0].game_status == 2) {
          layer.msg('比赛已经开始了')
          return
        }
        let players = await this.$db.select("select count(*) as mycount from players where game_id = ? order by sort_num asc, id asc", [id])
        if (players[0].mycount <= 0) {
          layer.msg('没有参赛选手，请先添加参赛选手')
          return
        }
        if (players[0].mycount < 2) {
          layer.msg('参赛选手人数不能小于2人')
          return
        }
        if(game[0].game_format != 3){
          if (game[0].game_format == 1 && players[0].mycount < 3) {
            layer.msg('双败参赛人数不能小于3人')
            return
          }
          if((players[0].mycount / 4) < Math.ceil(game[0].group_nums / 2) && game[0].group_count > 1){
            layer.msg(`参赛选手人数太少，需要保证每个小组最少${Math.ceil(game[0].group_nums / 2)}人`)
            return
          }
          if(players[0].mycount > game[0].group_nums*game[0].group_count){
            layer.msg('参赛人数太多了')
            return
          }
          this.showStartForm = true
          this.start_id = id
        }else{
          await this.$db.execute("update games set game_status = 1 where id = ?", [id])
          await writeTextFile('obs/game_name.txt', game[0].title, { dir: BaseDirectory.App })
          this.$router.push('/matchs_friend/' + id)
        }
      }else{
        layer.msg('未找到该比赛')
        return        
      }
    },
    async startGames(id) {
      const startGame = await this.$db.select("select id from games where game_status in (1, 2)")
      if(startGame.length > 0){
        layer.msg('已有比赛正在进行中，请先结束比赛')
        return
      }
      const game = await this.$db.select("select * from games where id = ?", [id])
      if (game.length > 0) {
        let loading = layer.load()
        let players = await this.$db.select("select * from players where game_id = ? order by sort_num asc, id asc", [id])
        if(this.is_rand){
          players.sort(() => {
            return (0.5 - Math.random())
          })
          for (let i = 0; i < players.length; i++) {
            await this.$db.execute("update players set sort_num = ? where id = ?", [i, players[i].id])
          }
        }else{
          for (let i = 0; i < players.length; i++) {
            await this.$db.execute("update players set sort_num = ? where id = ?", [i, players[i].id])
          }
        }
        players = await this.$db.select("select * from players where game_id = ? order by sort_num asc, id asc", [id])
        let players_dist = []
        const real_players = Math.round(players.length / game[0].group_count)
        // 补齐并分组
        if(players.length < game[0].group_count * game[0].group_nums){
          let sort_num = players.length
          let bq = 'insert into players (title, game_id,fast_copy,sort_num, is_display) values'
          const need_insert_num = game[0].group_nums * game[0].group_count - players.length
          for(let j = 0; j < need_insert_num; j++){
            bq += (j == 0 ? '' : ',')+`('轮空', ${game[0].id}, '', ${sort_num++}, 0)`
          }
          await this.$db.execute(bq)
          let lk = await this.$db.select("select * from players where game_id = ? and is_display = 0", [game[0].id])
          for(let i = 0; i < game[0].group_count; i++){
            players_dist[i] = []
            let temp = [], lk_temp = []
            if(i == (game[0].group_count - 1)){
              temp = players
            }else{
              temp = players.slice(0, real_players)
              players.splice(0, real_players)
            }
            if(temp.length < game[0].group_nums){
              lk_temp = i == (game[0].group_count - 1) ? lk : lk.slice(0, game[0].group_nums - real_players)
              if(i < (game[0].group_count - 1)){
                lk.splice(0, game[0].group_nums - real_players)
              }
            }
            if(lk_temp.length > 0){
              let max_length = Math.max(temp.length, lk_temp.length)
              for(let j = 0; j < max_length; j++){
                if(j < temp.length){
                  players_dist[i].push(temp[j])
                }
                if(j < lk_temp.length){
                  players_dist[i].push(lk_temp[j])
                }
              }
            }else{
              players_dist[i] = temp
            }
          }
        }else{
          for(let i = 0; i < game[0].group_count; i++){
            players_dist[i] = []
            players_dist[i] = players.slice(0, game[0].group_nums)
            players.splice(0, game[0].group_nums)
          }
        }

        // 计算小组数
        const group_max = game[0].group_count

        // 写所有的比赛
        let group_sort = 0
        let sql = ""
        for (let i = 0; i < group_max; i++) {
          let winner_level = 0
          let loser_level = 0
          while (true) {
            if (winner_level == 0) {
              // 第0轮胜者
              let sql_winner = "insert into matchs (game_id, player_one_id, player_two_id, match_level, match_group, match_type, group_sort) values "
              for (let l = 0; l < game[0].group_nums; l +=2) {
                let player_two_id = 0
                if (typeof players_dist[i][l + 1] != 'undefined' && (l + 1 < game[0].group_nums)) {
                  player_two_id = players_dist[i][l + 1].id
                }
                sql_winner = sql_winner + (l == 0 ? "" : ",") +`(${game[0].id}, ${players_dist[i][l].id}, ${player_two_id}, 0, ${i}, 1, ${group_sort++})`
                if (player_two_id == 0) {
                  break
                }
              }
              await this.$db.execute(sql_winner)
              // 第0轮败者
              if (game[0].game_format == 1) {
                let loser_sql = "insert into matchs (game_id, match_level, match_group, match_type, parent_id_1, parent_id_2, group_sort) values "
                const temp = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_type = 1 and match_level = 0", [game[0].id, i])
                for (let k = 0; k < temp.length; k += 2) {
                  if (!temp[k + 1] && temp[k].player_two_id == 0) {
                    break
                  }
                  loser_sql = loser_sql + (k == 0 ? "" : ",") +`(${game[0].id}, 0, ${i}, 2, ${temp[k].id}, ${temp[k+1]?(temp[k + 1].player_two_id == 0 ? 0 : temp[k + 1].id):0}, ${group_sort++})`
                }
                await this.$db.execute(loser_sql)
              }
            }
            const winner_match = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_level = ? and match_type = 1 order by id asc", [game[0].id, i, winner_level])
            if (winner_match.length > 1) {
              // 胜者
              sql = "insert into matchs (game_id, match_level, match_group, match_type, parent_id_1, parent_id_2, group_sort) values "
              for (let k = 0; k < winner_match.length; k += 2) {
                let parent_id_2 = 0
                if (typeof winner_match[k + 1] != 'undefined') {
                  parent_id_2 = winner_match[k + 1].id
                }
                sql = sql + (k == 0 ? "" : ",") +`(${game[0].id}, ${winner_level + 1}, ${i}, 1, ${winner_match[k].id}, ${parent_id_2}, ${group_sort++})`
              }
              await this.$db.execute(sql)
              // 败者
              if (game[0].game_format == 1) {
                const temp = await this.$db.select("select * from matchs where game_id = ? and match_level = ? and match_group = ? and match_type = 1 order by id " + (winner_level % 2 == 0 ? 'desc' : 'asc'), [game[0].id, winner_level + 1, i])
                sql = "insert into matchs (game_id, match_level, match_group, match_type, parent_id_1, parent_id_2, group_sort) values "
                let z = 0
                for (let k = 0; k < temp.length; k++) {
                  if (temp[k].parent_id_2 == 0) {
                    continue
                  }
                  let temp1 = await this.$db.select(`select * from matchs where game_id = ? and match_type = 2 and match_level = ? and match_group = ? limit ${z}, 1`, [game[0].id, loser_level, i])
                  sql = sql + (z == 0 ? "" : ",") +`(${game[0].id}, ${loser_level + 1}, ${i}, 2, ${temp[k].id}, ${temp1[0] ? temp1[0].id : 0}, ${group_sort++})`
                  z ++
                }
                await this.$db.execute(sql)
                // 如果还有一场败者没创建一下场
                let last_ = await this.$db.select("select id from matchs where game_id = ? and match_group = ? and match_level = ? and match_type = 2 order by id desc limit 1", [game[0].id, i, loser_level])
                let last__ = await this.$db.select("select id from matchs where parent_id_1 = ? or parent_id_2 = ?", [last_[0].id, last_[0].id])
                if(last__.length == 0){
                  await this.$db.execute("insert into matchs (game_id, match_level, match_group, match_type, parent_id_1, parent_id_2, group_sort) values (?, ?, ?, ?, ?, ?, ?)", [game[0].id, loser_level + 1, i, 2, 0, last_[0].id, group_sort++])
                }
                loser_level++
                const loser_match = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_level = ? and match_type = 2 order by id asc", [id, i, loser_level])
                if (loser_match.length > 1) {
                  const temp = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_level = ? and match_type = 2 order by id asc", [id, i, loser_level])
                  sql = "insert into matchs (game_id, match_level, match_group, match_type, parent_id_1, parent_id_2, group_sort) values "
                  for (let k = 0; k < loser_match.length; k += 2) {
                    let parent_id_2 = 0
                    if (typeof temp[k + 1] != 'undefined') {
                      parent_id_2 = temp[k + 1].id
                    }
                    sql = sql + (k == 0 ? "" : ",") +`(${game[0].id}, ${loser_level + 1}, ${i}, 2, ${loser_match[k].id}, ${parent_id_2}, ${group_sort++})`
                  }
                  await this.$db.execute(sql)
                  loser_level++
                }
              }
            } else {
              break
            }
            winner_level++
          }
          
          if (game[0].game_format == 1 && (game[0].group_wins == 0 || group_max == 1)) { // 双败且(每组只有一个出线或只有一个组)需要创建最后两轮胜者（reset）
            const winner_last = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_type = 1 order by id desc limit 1", [game[0].id, i])
            const loser_last = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_type = 2 order by id desc limit 1", [game[0].id, i])
            await this.$db.execute("insert into matchs (game_id, match_type, parent_id_1, parent_id_2, group_sort, match_level, match_group) values(?,?,?,?,?,?,?)", [game[0].id, 1, winner_last[0].id, loser_last[0].id, group_sort++, winner_level + 1,i])
            const temp = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_type = 1 order by id desc limit 1", [game[0].id, i])
            await this.$db.execute("insert into matchs (game_id, match_type, parent_id_1, parent_id_2, group_sort,match_level,match_group) values(?,?,?,?,?,?,?)", [game[0].id, 1, temp[0].id, temp[0].id, group_sort++, winner_level + 2,i])
          }
        }
        // 写决赛
        if(group_max > 0){
          // 胜者第一轮
          let sql = "insert into matchs (game_id, match_level, match_type, parent_id_1, parent_id_2, group_sort, is_final) values"
          group_sort = 0
          let temp, temp1
          for(let i = 0; i < group_max; i += 2){
            temp = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_type = 1 order by id desc", [id, i])
            temp1 = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_type = 1 order by id desc", [id, i + 1])
            sql += (i == 0 ? "" : ",") +`(${game[0].id}, 0, 1, ${temp[0].id}, ${temp1[0] ? temp1[0].id : 0}, ${group_sort++}, 1)`
          }
          await this.$db.execute(sql)
          // 败者第一轮
          if(game[0].game_format == 1){
            sql = `insert into matchs (game_id, match_level, match_type, parent_id_1, parent_id_2, group_sort, is_final) values`
            for(let i = 0; i < group_max; i += 2){
              temp = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_type = 2 order by id desc", [id, i])
              temp1 = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_type = 2 order by id desc", [id, i + 1])
              sql += (i == 0 ? "" : ",") + `(${game[0].id}, 0, 2, ${temp[0].id}, ${temp1[0] ? temp1[0].id : 0}, ${group_sort++}, 1)`
            }
            await this.$db.execute(sql)
          }
          let winner_level = 0
          let loser_level = 0
          while(true){
            const winner_match = await this.$db.select("select * from matchs where game_id = ? and match_level = ? and match_type = 1 and is_final = 1 order by id asc", [game[0].id, winner_level])
            if (winner_match.length > 1) {
              // 胜者
              sql = "insert into matchs (game_id, match_level, match_type, parent_id_1, parent_id_2, group_sort, is_final) values "
              for (let k = 0; k < winner_match.length; k += 2) {
                let parent_id_2 = 0
                if (typeof winner_match[k + 1] != 'undefined') {
                  parent_id_2 = winner_match[k + 1].id
                }
                sql = sql + (k == 0 ? "" : ",") +`(${game[0].id}, ${winner_level + 1}, 1, ${winner_match[k].id}, ${parent_id_2}, ${group_sort++}, 1)`
              }
              await this.$db.execute(sql)
              // 败者
              if (game[0].game_format == 1) {
                const temp = await this.$db.select("select * from matchs where game_id = ? and match_level = ? and match_type = 1 and is_final = 1 order by id " + (winner_level % 2 == 0 ? 'desc' : 'asc'), [game[0].id, winner_level])
                sql = "insert into matchs (game_id, match_level, match_type, parent_id_1, parent_id_2, group_sort, is_final) values "
                let z = 0
                for (let k = 0; k < temp.length; k++) {
                  if (temp[k].parent_id_2 == 0) {
                    continue
                  }
                  let temp1 = await this.$db.select(`select * from matchs where game_id = ? and match_type = 2 and match_level = ? and is_final = 1 limit ${z}, 1`, [game[0].id, loser_level])
                  sql = sql + (z == 0 ? "" : ",") +`(${game[0].id}, ${loser_level + 1}, 2, ${temp[k].id}, ${temp1[0] ? temp1[0].id : 0}, ${group_sort++}, 1)`
                  z ++
                }
                await this.$db.execute(sql)
                // 如果还有一场败者没有创建一下场
                let last_ = await this.$db.select("select id from matchs where game_id = ? and match_level = ? and match_type = 2 and is_final = 1 order by id desc limit 1", [game[0].id, loser_level])
                let last__ = await this.$db.select("select id from matchs where parent_id_1 = ? or parent_id_2 = ?", [last_[0].id, last_[0].id])
                if(last__.length == 0){
                  await this.$db.execute("insert into matchs (game_id, match_level, match_type, parent_id_1, parent_id_2, group_sort, is_final) values (?, ?, ?, ?, ?, ?, ?)", [game[0].id, loser_level + 1, 2, 0, last_[0].id, group_sort++, 1])
                }
                loser_level++
                const loser_match = await this.$db.select("select * from matchs where game_id = ? and match_level = ? and match_type = 2 and is_final = 1 order by id asc", [id, loser_level])
                if (loser_match.length > 1) {
                  sql = "insert into matchs (game_id, match_level, match_type, parent_id_1, parent_id_2, group_sort, is_final) values "
                  for (let k = 0; k < loser_match.length; k += 2) {
                    let parent_id_2 = 0
                    if (typeof loser_match[k + 1] != 'undefined') {
                      parent_id_2 = loser_match[k + 1].id
                    }
                    sql = sql + (k == 0 ? "" : ",") +`(${game[0].id}, ${loser_level + 1}, 2, ${loser_match[k].id}, ${parent_id_2}, ${group_sort++}, 1)`
                  }
                  await this.$db.execute(sql)
                  loser_level++
                }
              }
            } else {
              break
            }
            winner_level++
          }
          
          if (game[0].game_format == 1) { // 双败有一个最终决赛
            const winner_last = await this.$db.select("select * from matchs where game_id = ? and is_final = 1 and match_type = 1 order by id desc limit 1", [game[0].id])
            let loser_last = await this.$db.select("select * from matchs where game_id = ? and is_final = 1 and match_type = 2 order by id desc limit 1", [game[0].id])
            await this.$db.execute("insert into matchs (game_id, match_type, parent_id_1, parent_id_2, group_sort, match_level, is_final) values (?, ?, ?, ?, ?, ?, ?)", [game[0].id, 2, winner_last[0].id, loser_last[0].id, group_sort++, loser_last[0].match_level + 1, 1])
            loser_last = await this.$db.select("select * from matchs where game_id = ? and is_final = 1 and match_type = 2 order by id desc limit 1", [game[0].id])
            await this.$db.execute("insert into matchs (game_id, match_type, parent_id_1, parent_id_2, group_sort, match_level, is_final) values(?,?,?,?,?,?,1)", [game[0].id, 1, winner_last[0].id, loser_last[0].id, group_sort++, winner_last[0].match_level + 1])
            const temp = await this.$db.select("select * from matchs where game_id = ? and is_final = 1 and match_type = 1 order by id desc limit 1", [game[0].id])
            await this.$db.execute("insert into matchs (game_id, match_type, parent_id_1, parent_id_2, group_sort,match_level,is_final) values(?,?,?,?,?,?,1)", [game[0].id, 1, temp[0].id, temp[0].id, group_sort++, winner_last[0].match_level + 2])
          }
        }
        await this.$db.execute("update games set game_status = ? where id = ?", [1, id])
        // 写比赛信息进入文件
        await writeTextFile('obs/game_name.txt', game[0].title, { dir: BaseDirectory.App })
        // this.getList()
        layer.close(loading)
        this.$router.push('/matchs/' + id)
      } else {
        layer.msg('未找到该比赛')
        return
      }
    },
    endGames(id) {
      layer.confirm('确定结束比赛吗？', async () => {
        const sqlreturn = await this.$db.select("select id,game_status from games where id = ?", [id])
        if (sqlreturn.length > 0) {
          if (sqlreturn[0].game_status == 0) {
            layer.msg('比赛还未开始')
            return
          }
          await this.$db.execute("update games set game_status = ? where id = ?", [3, id])
          this.getList()
        } else {
          layer.msg('未找到该比赛')
          return
        }
      })
      layer.closeAll()
    },
    viewmatchs(item) {
      if(item.game_format != 3){
        if(item.game_status == 2){
          this.$router.push(`/matchs/${item.id}/1`)
        }else{
          this.$router.push(`/matchs/${item.id}`)
        }
      }else{
        this.$router.push(`/matchs_friend/${item.id}`)
      }
    },
    async clearData() {
      layer.load()
      await this.$db.execute("DELETE FROM matchs")
      await this.$db.execute("UPDATE sqlite_sequence SET seq = 0 WHERE name='matchs'")
      await this.$db.execute("UPDATE games SET game_status = 0")
      await this.$db.execute("UPDATE players SET sort_num = 0")
      await this.$db.execute("delete from players where is_display = 0")
      layer.closeAll()
      this.getList()
    },
  }
}
</script>