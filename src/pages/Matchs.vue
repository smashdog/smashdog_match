<template>
  <h3>{{ game.title }}</h3>
  <div class="top">
    <div class="btn-group" role="group">
      <button type="button" class="btn btn-sm btn-primary" @click="share()" v-if="config.shareApi.url.length > 0 && config.shareApi.key.length > 0">复制对阵表链接</button>
      <button type="button" class="btn btn-sm btn-primary" @click="toFinal()" v-if="game.game_status == 1 && group_count.length > 1">结束小组赛</button>
      <button type="button" class="btn btn-sm btn-primary" @click="reload()">{{ is_final == 0 ? '查看决赛' : '查看小组赛' }}</button>
      <button type="button" class="btn btn-sm btn-danger" @click="endMatch()" v-if="game.game_status != 3">结束比赛</button>
      <button type="button" class="btn btn-sm btn-secondary" @click="$router.push('/games')">返回比赛列表</button>
      <!-- <button type="button" class="btn btn-sm btn-danger" @click="clearMatch()" v-if="$debug">清除数据</button> -->
    </div>
  </div>
  <fieldset class="groups" v-for="i in group_count">
    <legend>{{ group_count.length == 1 ? '决赛' : `第${i+1}组` }}</legend>
    <div>
      <div class="tx-line">
        <span class="text">胜者组</span>
      </div>
      <div v-if="game.game_format == 1 || game.game_format == 2" class="matchs" v-dragscroll>
        <div class="scoll_main">
          <div class="list" v-for="(levels, j) in winner_matchs[i]">
            <div class="list_title">胜者第{{ j + 1 }}轮</div>
            <div class="player_list">
              <div v-for="(match, k) in levels" class="item"
                :style="{ paddingTop: (j >= winner_idx ? winner_idx : j) > 0 ? 30 * 2 ** (j >= winner_idx ? winner_idx : j) - 30 + 'px' : 0, paddingBottom: (j >= winner_idx ? winner_idx : j) > 0 ? 30 * 2 ** (j >= winner_idx ? winner_idx : j) - 30 + 'px' : 0 }">
                <div class="item_left">
                  <div class="round layui-badge"> {{ match.group_sort + 1 }}</div>
                </div>
                <div
                  class="list-group">
                  <div class="list-group-item list-group-item-action player">
                    <div class="sort_num">{{ match.player_one_sort_num }}</div>
                    <div class="title" :title="match.player_one_title"
                      @click="fastCopy(match.change_user_place == 0 ? match.player_one_fast_copy : match.player_two_fast_copy)" :style="{color: match.change_user_place == 0 ? (match.player_one_id == 0 ? '#cccccc' : '#000000') : (match.player_two_id == 0 ? '#cccccc' : '#000000')}">
                      {{ match.change_user_place == 0 ? match.player_one_title : match.player_two_title }}</div>
                    <input :class="{nums: true, 'bg-danger-subtle':  match.change_user_place == 0 ? (match.winner == 1 ? true : false) : (match.winner == 2 ? true : false)}" type="number"
                      :value="match.change_user_place == 0 ? match.player_one_score : match.player_two_score"
                      @change="changeScore(i, j, k, match.id, match.change_user_place == 0 ? 'player_one_score' : 'player_two_score', $event.target.value, 'winner_matchs')"
                      min="0" max="99" step="1" :disabled="(match.is_final == 0 && game.game_status == 2) || (match.match_status == 2) || (match.player_one_id == 0)" :name="`score${match.id}1`">
                  </div>
                  <div class="list-group-item list-group-item-action player">
                    <div class="sort_num">{{ match.player_two_sort_num }}</div>
                    <div class="title" :title="match.player_two_title"
                      @click="fastCopy(match.change_user_place == 0 ? match.player_two_fast_copy : match.player_one_fast_copy)" :style="{color: match.change_user_place == 0 ? (match.player_two_id == 0 ? '#cccccc' : '#000000') : (match.player_one_id == 0 ? '#cccccc' : '#000000')}">
                      {{ match.change_user_place == 0 ? match.player_two_title : match.player_one_title }}</div>
                    <input :class="{nums: true, 'bg-danger-subtle':  match.change_user_place == 0 ? (match.winner == 2 ? true : false) : (match.winner == 1 ? true : false)}" type="number"
                      :value="match.change_user_place == 0 ? match.player_two_score : match.player_one_score"
                      @change="changeScore(i, j, k, match.id, match.change_user_place == 0 ? 'player_two_score' : 'player_one_score', $event.target.value, 'winner_matchs')"
                      min="0" max="99" step="1" :disabled="(match.is_final == 0 && game.game_status == 2) || (match.match_status == 2) || (match.player_two_id == 0)" :name="`score${match.id}2`">
                  </div>
                </div>
                <div class="manager">
                  <button @click="startMatch('winner_matchs', i, j, k)" v-if="(game.game_status == 1 && is_final == 0) || (game.game_status == 2 && is_final == 1)"
                    v-show="(start_id == 0 && match.player_one_id && match.player_two_id) || start_id == match.id"
                    :class="{ btn: true, 'btn-success': start_id == 0, 'btn-danger': start_id == match.id, 'btn-sm': true }"
                    style="--bs-btn-font-size: .8rem;" :disabled="start_id != match.id && start_id != 0">{{ start_id ==
                      0 || start_id != match.id ? '开始' : '结束' }}</button>
                  <button class="btn btn-sm btn-success btn-sm" style="--bs-btn-font-size: .8rem;" v-if="(game.game_status == 1 && is_final == 0) || (game.game_status == 2 && is_final == 1)"
                    @click="edit('winner_matchs', i, j, k)"
                    v-show="(match.player_one_id && match.player_two_id) || (match.parent_id_1 > 0 && match.player_one_id > 0 && match.parent_id_2 == 0) || (match.parent_id_2 > 0 && match.player_two_id > 0 && match.parent_id_1 == 0) || (match.match_level == 0 && match.match_type == 1)">编辑</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="game.game_format == 1">
      <div class="tx-line">
        <span class="text">败者组</span>
      </div>
      <div v-if="game.game_format == 1 || game.game_format == 2" class="matchs" v-dragscroll>
        <div class="scoll_main">
          <div class="list" v-for="(levels, j) in loser_matchs[i]">
            <div class="list_title">败者第{{ j + 1 }}轮</div>
            <div class="player_list">
              <div v-for="(match, k) in levels" class="item"
                :style="{ paddingTop: (Math.floor(j / 2) >= loser_idx ? loser_idx : Math.floor(j / 2)) > 0 ? 30 * 2 ** (Math.floor(j / 2) >= loser_idx ? loser_idx : Math.floor(j / 2)) - 30 + 'px' : 0, paddingBottom: Math.floor(j / 2) > 0 ? 30 * 2 ** (Math.floor(j / 2) >= loser_idx ? loser_idx : Math.floor(j / 2)) - 30 + 'px' : 0 }">
                <div class="item_left">
                  <div class="round layui-badge"> {{ match.group_sort + 1 }}</div>
                </div>
                <div
                  class="list-group">
                  <div class="list-group-item list-group-item-action player">
                    <div class="sort_num">{{ match.player_one_sort_num }}</div>
                    <div class="title" :title="match.player_one_title"
                      @click="fastCopy(match.change_user_place == 0 ? match.player_one_fast_copy : match.player_two_fast_copy)" :style="{color: match.change_user_place == 0 ? (match.player_one_id == 0 ? '#cccccc' : '#000000') : (match.player_two_id == 0 ? '#cccccc' : '#000000')}">
                      {{ match.change_user_place == 0 ? match.player_one_title : match.player_two_title }}</div>
                    <input :class="{nums: true, 'bg-danger-subtle':  match.change_user_place == 0 ? (match.winner == 1 ? true : false) : (match.winner == 2 ? true : false)}" type="number"
                      :value="match.change_user_place == 0 ? match.player_one_score : match.player_two_score"
                      @change="changeScore(i, j, k, match.id, match.change_user_place == 0 ? 'player_one_score' : 'player_two_score', $event.target.value, 'loser_matchs')"
                      min="0" max="99" step="1" :disabled="(match.is_final == 0 && game.game_status == 2) || (match.match_status == 2) || (match.player_one_id == 0)" :name="`score${match.id}1`">
                  </div>
                  <div class="list-group-item list-group-item-action player">
                    <div class="sort_num">{{ match.player_two_sort_num }}</div>
                    <div class="title" :title="match.player_two_title"
                      @click="fastCopy(match.change_user_place == 0 ? match.player_two_fast_copy : match.player_one_fast_copy)" :style="{color: match.change_user_place == 0 ? (match.player_two_id == 0 ? '#cccccc' : '#000000') : (match.player_one_id == 0 ? '#cccccc' : '#000000')}">
                      {{ match.change_user_place == 0 ? match.player_two_title : match.player_one_title }}</div>
                    <input :class="{nums: true, 'bg-danger-subtle':  match.change_user_place == 0 ? (match.winner == 2 ? true : false) : (match.winner == 1 ? true : false)}" type="number"
                      :value="match.change_user_place == 0 ? match.player_two_score : match.player_one_score"
                      @change="changeScore(i, j, k, match.id, match.change_user_place == 0 ? 'player_two_score' : 'player_one_score', $event.target.value, 'loser_matchs')"
                      min="0" max="99" step="1" :disabled="(match.is_final == 0 && game.game_status == 2) || (match.match_status == 2) || (match.player_two_id == 0)" :name="`score${match.id}2`">
                  </div>
                </div>
                <div class="manager">
                  <button @click="startMatch('loser_matchs', i, j, k)" v-if="(game.game_status == 1 && is_final == 0) || (game.game_status == 2 && is_final == 1)"
                    v-show="(start_id == 0 && match.player_one_id && match.player_two_id) || start_id == match.id"
                    :class="{ btn: true, 'btn-success': start_id == 0, 'btn-danger': start_id == match.id, 'btn-sm': true }"
                    style="--bs-btn-font-size: .8rem;" :disabled="start_id != match.id && start_id != 0">{{ start_id ==
                      0 || start_id != match.id ? '开始' : '结束' }}</button>
                      <button class="btn btn-sm btn-success btn-sm" style="--bs-btn-font-size: .8rem;" v-if="(game.game_status == 1 && is_final == 0) || (game.game_status == 2 && is_final == 1)"
                    @click="edit('loser_matchs', i, j, k)"
                    v-show="(match.player_one_id && match.player_two_id) || (match.parent_id_1 > 0 && match.player_one_id > 0 && match.parent_id_2 == 0) || (match.parent_id_2 > 0 && match.player_two_id > 0 && match.parent_id_1 == 0) || (match.match_level == 0 && match.match_type == 1)">编辑</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </fieldset>
  <div class="edit window" v-show="showEditor">
    <form class="div_form">
      <div class="mb-3" v-if="form.player_one_id">
        <label for="player_one_score" class="form-label">{{ form.player_one_title }}比分</label>
        <input type="text" class="form-control" id="player_one_score" name="player_one_score" autocomplete="off"
          v-model="form.player_one_score">
      </div>
      <div class="mb-3" v-if="form.player_two_id">
        <label for="player_two_score" class="form-label">{{ form.player_two_title }}比分</label>
        <input type="text" class="form-control" id="player_two_score" name="player_two_score" autocomplete="off"
          v-model="form.player_two_score">
      </div>
      <div class="mb-3" v-if="form.player_one_id && form.player_two_id">
        <div class="form-label">交换1p2p位置</div>
        <div class="input_inline">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="change_user_place" id="change_user_place0" value="0"
              v-model="form.change_user_place">
            <label class="form-check-label" for="change_user_place0">
              还原
            </label>
          </div>
          <div class="form-check" v-if="form.player_one_id">
            <input class="form-check-input" type="radio" name="change_user_place" id="change_user_place1" value="1"
              v-model="form.change_user_place">
            <label class="form-check-label" for="change_user_place1">
              交换
            </label>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <div class="form-label">谁胜</div>
        <div class="input_inline">
          <div class="form-check" v-if="enableWinner">
            <input class="form-check-input" type="radio" name="winner" id="winner0" value="0" v-model="form.winner" @click="winnerClick(0)">
            <label class="form-check-label" for="game_format0">
              还没结束
            </label>
          </div>
          <div class="form-check" v-if="form.player_one_id">
            <input class="form-check-input" type="radio" name="winner" id="winner1" value="1"
              v-model="form.winner" @click="winnerClick(1)">
            <label class="form-check-label" for="game_format1">
              {{ form.player_one_title }}胜
            </label>
          </div>
          <div class="form-check" v-if="form.player_two_id">
            <input class="form-check-input" type="radio" name="winner" id="winner2" value="2"
              v-model="form.winner" @click="winnerClick(2)">
            <label class="form-check-label" for="game_format2">
              {{ form.player_two_title }}胜
            </label>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <div class="form-label">比赛状态</div>
        <div class="input_inline">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="match_status" id="match_status0" value="0"
              v-model="form.match_status" ref="match_status0">
            <label class="form-check-label" for="match_status0">
              未开始
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="match_status" id="match_status1" value="1"
              v-model="form.match_status" @click="checkStatus()">
            <label class="form-check-label" for="match_status1">
              进行中
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="match_status" id="match_status2" value="2"
              v-model="form.match_status" ref="match_status2">
            <label class="form-check-label" for="match_status2">
              已结束
            </label>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <button type="button" class="btn btn-sm btn-success" @click="submit()">提交</button>
        <button type="button" class="btn btn-sm btn-secondary" @click="cancel()">取消</button>
      </div>
    </form>
  </div>
</template>

<script>
import { BaseDirectory, writeTextFile } from '@tauri-apps/api/fs'
import { open } from "@tauri-apps/api/shell"
import { appDataDir } from "@tauri-apps/api/path"
import { writeText } from '@tauri-apps/api/clipboard'
export default {
  data() {
    return {
      config: {
        shareApi: {
          url: '',
          key: ''
        }
      },
      game: {
        title: '',
        match_status: 1,
        game_format: 1,
        game_status: 1,
      },
      groups: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split(''),
      winner_matchs: [[[]]],
      loser_matchs: [[[]]],
      winner_idx: 0,
      loser_idx: 0,
      showEditor: false,
      form: {
        player_one_title: '',
        player_two_title: '',
        player_one_score: 0,
        player_two_score: 0,
        winner: 0,
        match_id: 0,
        match_status: 0,
        id: 0,
        change_user_place: 0,
      },
      enableWinner: false,
      source: {},
      editParams: {
        chars: '',
        i: 0,
        k: 0,
      },
      group_count: [],
      start_id: 0,
      is_final: 0,
    }
  },
  async created() {
    if(localStorage.getItem('config')){
      let config = JSON.parse(localStorage.getItem('config'))
      if(typeof config.shareApi != 'undefined'){
        this.config = config
      }
    }
    if(!this.$route.params.end){
      this.is_final = 0
    }else{
      this.is_final = 1
    }
    const game = await this.$getGame(this.$route.params.id)
    if (typeof game == 'string') {
      layer.msg(game, () => {
        this.$router.push('/games')
      })
    }
    this.game = game
    await this.getAllMatchs(true)
  },
  methods: {
    async share(){
      let url = this.config.shareApi.url.match(/(https?:\/\/[^/]+)\//i)
      await writeText(`${url[0]}#/matchs/${this.config.shareApi.key}/${this.game.id}`)
      layer.msg('已把对阵表链接复制到剪贴板，请粘贴到想要分享的地方吧！')
    },
    async openDir(){
      const appDataDirPath = await appDataDir()
      await open(appDataDirPath+'obs')
    },
    async reload(end){
      if(!this.$route.params.end || end){
        this.$router.push(`/matchs/${this.game.id}/1`)
        this.is_final = 1
      }else{
        this.$router.push(`/matchs/${this.game.id}`)
        this.is_final = 0
      }
      this.game = await this.$getGame(this.game.id)
      this.group_count = []
      this.winner_matchs = []
      this.loser_matchs = []
      this.getAllMatchs(true)
    },
    winnerClick(v){
      if(v == 0){
        this.form.game_status = 0
        this.$refs.match_status0.click()
      }else{
        this.form.game_status = 2
        this.$refs.match_status2.click()
      }
    },
    async getAllMatchs(send){
      const temp = await this.$db.select("select max(match_group) as max_group from matchs where game_id = ? and match_type = 1 and is_final = ?", [this.game.id, this.is_final])
      const group_count = temp[0].max_group
      for (let i = 0; i <= group_count; i++) {
        this.group_count.push(i)
        // 胜者
        const winner_max_level = await this.$db.select("select max(match_level) max_level from matchs where game_id = ? and match_type = 1 and match_group = ? and is_final = ?", [this.game.id, i, this.is_final])
        this.winner_matchs[i] = []
        for (let j = 0; j <= winner_max_level[0].max_level; j++) {
          this.winner_matchs[i][j] = {}
          let match = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_level = ? and match_type = 1 and is_final = ?", [this.game.id, i, j, this.is_final])
          if (this.winner_idx == 0 && match.length == 1) {
            this.winner_idx = j
          }
          for (let k = 0; k < match.length; k++) {
            this.winner_matchs[i][j][match[k].id] = await this.getMatch(match[k])
            if (match[k].match_status == 1) {
              this.start_id = match[k].id
              await this.writeFile('winner_matchs', i, j, match[k].id)
            }
          }
        }
        if(this.game.game_format == 1){
          // 败者
          const loser_max_level = await this.$db.select("select max(match_level) max_level from matchs where game_id = ? and match_type = 2 and match_group = ? and is_final = ?", [this.game.id, i, this.is_final])
          this.loser_matchs[i] = []
          for (let j = 0; j <= loser_max_level[0].max_level; j++) {
            this.loser_matchs[i][j] = {}
            let match = await this.$db.select("select * from matchs where game_id = ? and match_group = ? and match_level = ? and match_type = 2 and is_final = ?", [this.game.id, i, j, this.is_final])
            if (this.loser_idx == 0 && match.length == 1) {
              this.loser_idx = j
            }
            for (let k = 0; k < match.length; k++) {
              this.loser_matchs[i][j][match[k].id] = await this.getMatch(match[k])
              if (match[k].match_status == 1) {
                this.start_id = match[k].id
                await this.writeFile('loser_matchs', i, j, match[k].id)
              }
            }
          }
        }
      }
      if(send){
        await this.sendshareFunc()
      }
    },
    async sendshareFunc(){
      if(this.config.shareApi.url.length > 0 && this.config.shareApi.key.length > 0){
        await this.$sendShare({game: JSON.stringify(this.game), group_count: JSON.stringify(this.group_count), winner_matchs: JSON.stringify(this.winner_matchs), loser_matchs: JSON.stringify(this.loser_matchs), id: this.game.id, winner_idx: this.winner_idx, looser_idx: this.loser_idx})
      }
    },
    async clearMatch(){
      layer.load()
      await this.$db.execute("DELETE FROM matchs")
      await this.$db.execute("UPDATE sqlite_sequence SET seq = 0 WHERE name='matchs'")
      await this.$db.execute("UPDATE games SET game_status = 0")
      await this.$db.execute("UPDATE players SET sort_num = 0")
      layer.closeAll()
      this.$router.push('/games')
    },
    async getMatch(match) {
      const player1 = await this.$db.select("select title, (sort_num + 1) as sort_num, fast_copy from players where id = ?", [match.player_one_id])
      const player2 = await this.$db.select("select title, (sort_num + 1) as sort_num, fast_copy from players where id = ?", [match.player_two_id])
      let player_one_title = player1[0] ? player1[0].title : ''
      match.gray1 = false
      match.gray2 = false
      if (player_one_title == '' && match.parent_id_1 != 0) {
        const parent_match = await this.$db.select("select group_sort,match_type,match_group from matchs where id = ?", [match.parent_id_1])
        if(match.is_final == 1 && match.match_level == 0){
          player_one_title = (parent_match[0].match_group + 1) + `组${parent_match.match_type == 1 ? '胜者冠军' : '败者冠军'}`
        }else{
          player_one_title = `${parent_match[0].group_sort + 1}的${parent_match[0].match_type == 1 && match.match_type == 1 ? '胜者' : (parent_match[0].match_type == 1 && match.match_type == 2 ? '败者' : '胜者')}`
        }
        match.gray1 = true
      }
      let player_two_title = player2[0] ? player2[0].title : ''
      if (player_two_title == '' && match.parent_id_2 != 0) {
        const parent_match = await this.$db.select("select group_sort,match_type,match_group from matchs where id = ?", [match.parent_id_2])
        if(match.is_final == 1 && match.match_level == 0){
          player_two_title = (parent_match[0].match_group + 1) + `组${parent_match.match_type == 1 ? '胜者冠军' : '败者冠军'}`
        }else{
          player_two_title = `${parent_match[0].group_sort + 1}的${parent_match[0].match_type == 1 && match.match_type == 1 ? '胜者' : (parent_match[0].match_type == 1 && match.match_type == 2 ? '败者' : '胜者')}`
        }
        match.gray2 = true
      }
      match.player_one_title = player_one_title
      match.player_two_title = player_two_title
      match.player_one_sort_num = player1[0] ? player1[0].sort_num : ''
      match.player_two_sort_num = player2[0] ? player2[0].sort_num : ''
      match.player_one_fast_copy = player1[0] ? player1[0].fast_copy : ''
      match.player_two_fast_copy = player2[0] ? player2[0].fast_copy : ''
      return match
    },
    async fastCopy(text) {
      if (text.length == 0) {
        return
      }
      await writeText(text)
      layer.msg('复制成功')
    },
    async writeFile(chars, i, j, k) {
      await writeTextFile('obs/match.txt', `第${i+1}组${chars == 'winner_matchs' ? '胜者' : '败者'}第${j + 1}轮`, { dir: BaseDirectory.App })
      await writeTextFile('obs/p1_title.txt', this[chars][i][j][k].player_one_title, { dir: BaseDirectory.App })
      await writeTextFile('obs/p2_title.txt', this[chars][i][j][k].player_two_title, { dir: BaseDirectory.App })
      await writeTextFile('obs/p1_score.txt', this[chars][i][j][k].player_one_score + '', { dir: BaseDirectory.App })
      await writeTextFile('obs/p2_score.txt', this[chars][i][j][k].player_two_score + '', { dir: BaseDirectory.App })
    },
    async startMatch(chars, i, j, k) {
      if (this.start_id != 0 && this.start_id != this[chars][i][j][k].id) {
        return
      }
      layer.load()
      if (this.start_id == this[chars][i][j][k].id) {
        layer.closeAll()
        this.edit(chars, i, j, k)
        return
      }
      if (this[chars][i][j][k].parent_id_1 != 0) {
        const parent_match = await this.$db.select("select match_status from matchs where id = ?", [this[chars][i][j][k].parent_id_1])
        if (parent_match[0].match_status == 0) {
          layer.closeAll()
          layer.msg('前置场次未结束不能开始此场比赛')
          return
        }
      }
      if (this[chars][i][j][k].parent_id_2 != 0) {
        const parent_match = await this.$db.select("select match_status from matchs where id = ?", [this[chars][i][j][k].parent_id_2])
        if (parent_match[0].match_status == 0) {
          layer.closeAll()
          layer.msg('前置场次未结束不能开始此场比赛')
          return
        }
      }
      this.start_id = this[chars][i][j][k].id
      await this.$db.execute("update matchs set match_status = 1 where id = ?", [this[chars][i][j][k].id])
      await this.writeFile(chars, i, j, k)
      this[chars][i][j][k].match_status = 1
      layer.closeAll()
    },
    async changeScore(i, j, k, id, p, v, chars) {
      if (this.game.match_status == 3) {
        return
      }
      this[chars][i][j][k][p] = v
      await this.$db.execute("update matchs set " + p + " = ? where id = ?", [v, id])
      await this.sendshareFunc()
      if (this[chars][i][j][k].id == this.start_id) {
        let filename = ''
        if (p == 'player_one_score') {
          filename = `obs/p${this[chars][i][j][k].change_user_place == 1 ? 2 : 1}_score.txt`
        } else {
          filename = `obs/p${this[chars][i][j][k].change_user_place == 1 ? 1 : 2}_score.txt`
        }
        await writeTextFile(filename, this[chars][i][j][k][p] + '', { dir: BaseDirectory.App })
      }
    },
    edit(chars, i, j, k) {
      this.form = this[chars][i][j][k]
      if (this.form.winner > 0) {
        this.enableWinner = false
      } else {
        this.enableWinner = true
      }
      this.editParams = {
        chars: chars,
        i: i,
        j: j,
        k: k
      }
      this.showEditor = true
      localStorage.setItem('source', JSON.stringify(this.form))
    },
    async submit() {
      let errmsg = '', source = JSON.parse(localStorage.getItem('source'))
      if (this.form.match_status == 2 && this.winner == 0) {
        errmsg = '请选择胜负'
      }
      if (this.form.match_status == 2 && this.form.winner == 0) {
        errmsg = '要结此场比赛请先选择胜负'
      }
      if (this.form.match_status == 1 && this.form.id != this.start_id && this.start_id > 0) {
        errmsg = '已有比赛在进行中，请先结束该比赛才可以开始新的比赛'
        this.form.match_status = 0
      }
      if (this.form.match_status == 2 && this.form.winner == 0) {
        errmsg = '要结此场比赛请先选择胜负'
        this.form.match_status = 0
      }
      if(source.match_status == 2 && this.form.match_status == 0){
        errmsg = '已结束的比赛不能改为未开始'
      }
      if(source.winner > 0 && this.form.winner == 0){
        errmsg = '胜负关系确定后不能改为还未结束'
      }
      if(this.form.winner > 0 && this.form.match_status != 2){
        errmsg = '选择胜负后必须结束比赛'
      }
      if (errmsg.length > 0) {
        layer.msg(errmsg)
        this.form = source
        return
      }
      layer.load()
      await this.$db.execute("update matchs set player_one_score = ?, player_two_score = ?, match_status = ?, winner = ?, change_user_place = ? where id = ?", [this.form.player_one_score, this.form.player_two_score, this.form.match_status, this.form.winner, this.form.change_user_place, this.form.id])
      this[this.editParams.chars][this.editParams.i][this.editParams.j][this.editParams.k] = this.form
      this.showEditor = false
      if(this.form.winner != 0){
        let loadIds = []
        //取下一场比赛
        const p1 = await this.$db.select("select * from matchs where parent_id_1 = ? or parent_id_2 = ?", [this.form.id, this.form.id])
        let next_winer_match = false, next_loser_match = false
        if(p1.length == 2){
          if(p1[0].match_type == 1){
            next_winer_match = p1[0]
            next_loser_match = p1[1]
          }else{
            next_winer_match = p1[1]
            next_loser_match = p1[0]
          }
        }else if(p1.length == 1 && p1[0].parent_id_1 == p1[0].parent_id_2){// 下一场是最后一场不需要处理
        }else if(p1.length == 1){//只有一场的情况
          if(p1[0].match_type == 1){
            next_winer_match = p1[0]
          }else{
            next_loser_match = p1[0]
          }
        }
        // 修改后面场次数据
        if(next_winer_match){
          let chars = ''
          if(this.form.id == next_winer_match.parent_id_1){
            chars = 'player_one_id'
          }else{
            chars = 'player_two_id'
          }
          if(next_winer_match[chars] == 0){
            await this.$db.execute('update matchs set ' + chars + ' = ? where id = ?', [this.form.winner == 1 ? this.form.player_one_id : this.form.player_two_id, next_winer_match.id])
            loadIds.push(next_winer_match.id)
          }
        }
        if(next_loser_match){
          let chars = ''
          if(this.form.id == next_loser_match.parent_id_1){
            chars = 'player_one_id'
          }else{
            chars = 'player_two_id'
          }
          let player_id
          if(this.form.match_type == 1){
            player_id = this.form.winner == 1 ? this.form.player_two_id : this.form.player_one_id
          }else{
            player_id = this.form.winner == 1 ? this.form.player_one_id : this.form.player_two_id
          }
          if(next_loser_match[chars] == 0){
            await this.$db.execute('update matchs set ' + chars + ' = ? where id = ?', [player_id, next_loser_match.id])
            loadIds.push(next_loser_match.id)
          }
        }
        if(this.form.winner != source.winner && source.winner != 0){//已有胜负关系的比赛修改了胜负关系
          let where = ''
          if(this.is_final == 1){
            where = ' and is_final = 1'
          }else{
            where = ' and is_final = 0'
          }
          let temp1 = [], temp2 = [], temp3 = [], temp4 = [], temp5 = [], temp6 = [], temp7 = [], temp8 = []
          if(next_winer_match){
            temp1 = await this.$db.select(`select id, is_final from matchs where game_id = ${this.game.id} and match_level >= ${next_winer_match.match_level} and match_group = ${next_winer_match.match_group} and match_type = 1 and player_one_id = ${source.player_one_id}${where}`)
            temp2 = await this.$db.select(`select id, is_final from matchs where game_id = ${this.game.id} and match_level >= ${next_winer_match.match_level} and match_group = ${next_winer_match.match_group} and match_type = 1 and player_two_id = ${source.player_one_id}${where}`)
            temp3 = await this.$db.select(`select id, is_final from matchs where game_id = ${this.game.id} and match_level >= ${next_winer_match.match_level} and match_group = ${next_winer_match.match_group} and match_type = 1 and player_one_id = ${source.player_two_id}${where}`)
            temp4 = await this.$db.select(`select id, is_final from matchs where game_id = ${this.game.id} and match_level >= ${next_winer_match.match_level} and match_group = ${next_winer_match.match_group} and match_type = 1 and player_two_id = ${source.player_two_id}${where}`)
          }
          if(next_loser_match){
            temp5 = await this.$db.select(`select id, is_final from matchs where game_id = ${this.game.id} and match_level >= ${next_loser_match.match_level} and match_group = ${next_loser_match.match_group} and match_type = 2 and player_one_id = ${source.player_one_id}${where}`)
            temp6 = await this.$db.select(`select id, is_final from matchs where game_id = ${this.game.id} and match_level >= ${next_loser_match.match_level} and match_group = ${next_loser_match.match_group} and match_type = 2 and player_two_id = ${source.player_one_id}${where}`)
            temp7 = await this.$db.select(`select id, is_final from matchs where game_id = ${this.game.id} and match_level >= ${next_loser_match.match_level} and match_group = ${next_loser_match.match_group} and match_type = 2 and player_one_id = ${source.player_two_id}${where}`)
            temp8 = await this.$db.select(`select id, is_final from matchs where game_id = ${this.game.id} and match_level >= ${next_loser_match.match_level} and match_group = ${next_loser_match.match_group} and match_type = 2 and player_two_id = ${source.player_two_id}${where}`)
          }
          let tempidsql = ``
          for(let i = 0; i < temp1.length; i++){
            if((this.is_final == 0 && temp1[i].is_final == 0) || (this.is_final == 1 && temp1[i].is_final == 1)){
              loadIds.push(temp1[i].id)
            }
            tempidsql += `update matchs set player_one_id = ${source.player_two_id} where id = ${temp1[i].id};`
          }
          for(let i = 0; i < temp2.length; i++){
            if((this.is_final == 0 && temp2[i].is_final == 0) || (this.is_final == 1 && temp2[i].is_final == 1)){
              loadIds.push(temp2[i].id)
            }
            tempidsql += `update matchs set player_two_id = ${source.player_two_id} where id = ${temp2[i].id};`
          }
          for(let i = 0; i < temp3.length; i++){
            if((this.is_final == 0 && temp3[i].is_final == 0) || (this.is_final == 1 && temp3[i].is_final == 1)){
              loadIds.push(temp3[i].id)
            }
            tempidsql += `update matchs set player_one_id = ${source.player_one_id} where id = ${temp3[i].id};`
          }
          for(let i = 0; i < temp4.length; i++){
            if((this.is_final == 0 && temp4[i].is_final == 0) || (this.is_final == 1 && temp4[i].is_final == 1)){
              loadIds.push(temp4[i].id)
            }
            tempidsql += `update matchs set player_two_id = ${source.player_one_id} where id = ${temp4[i].id};`
          }
          for(let i = 0; i < temp5.length; i++){
            if((this.is_final == 0 && temp5[i].is_final == 0) || (this.is_final == 1 && temp5[i].is_final == 1)){
              loadIds.push(temp5[i].id)
            }
            tempidsql += `update matchs set player_one_id = ${source.player_two_id} where id = ${temp5[i].id};`
          }
          for(let i = 0; i < temp6.length; i++){
            if((this.is_final == 0 && temp6[i].is_final == 0) || (this.is_final == 1 && temp6[i].is_final == 1)){
              loadIds.push(temp6[i].id)
            }
            tempidsql += `update matchs set player_two_id = ${source.player_two_id} where id = ${temp6[i].id};`
          }
          for(let i = 0; i < temp7.length; i++){
            if((this.is_final == 0 && temp7[i].is_final == 0) || (this.is_final == 1 && temp7[i].is_final == 1)){
              loadIds.push(temp7[i].id)
            }
            tempidsql += `update matchs set player_one_id = ${source.player_one_id} where id = ${temp7[i].id};`
          }
          if(this.is_final == 0){
            let temp9 = await this.$db.select(`select id from matchs where game_id = ${this.game.id} and player_one_id = ${source.player_one_id} and is_final = 1`)
            let temp10 = await this.$db.select(`select id from matchs where game_id = ${this.game.id} and player_two_id = ${source.player_one_id} and is_final = 1`)
            let temp11 = await this.$db.select(`select id from matchs where game_id = ${this.game.id} and player_one_id = ${source.player_two_id} and is_final = 1`)
            let temp12 = await this.$db.select(`select id from matchs where game_id = ${this.game.id} and player_two_id = ${source.player_two_id} and is_final = 1`)
            for(let i = 0; i < temp9.length; i++){
              tempidsql += `update matchs set player_one_id = ${source.player_two_id} where id = ${temp9[i].id};`
            }
            for(let i = 0; i < temp10.length; i++){
              tempidsql += `update matchs set player_two_id = ${source.player_two_id} where id = ${temp10[i].id};`
            }
            for(let i = 0; i < temp11.length; i++){
              tempidsql += `update matchs set player_one_id = ${source.player_one_id} where id = ${temp11[i].id};`
            }
            for(let i = 0; i < temp12.length; i++){
              tempidsql += `update matchs set player_two_id = ${source.player_one_id} where id = ${temp12[i].id};`
            }
          }
          for(let i = 0; i < temp8.length; i++){
            if((this.is_final == 0 && temp8[i].is_final == 0) || (this.is_final == 1 && temp8[i].is_final == 1)){
              loadIds.push(temp8[i].id)
            }
            tempidsql += `update matchs set player_two_id = ${source.player_one_id} where id = ${temp8[i].id};`
          }
          if(tempidsql != ''){
            await this.$db.execute(tempidsql)
          }
        }
        if(loadIds.length > 0){
          const loadMatchs = await this.$db.select("select * from matchs where id in (" + loadIds.join(',') + ") and is_final = ?", [this.is_final])
          for(let i = 0; i < loadMatchs.length; i++){
            let str = loadMatchs[i].match_type == 1 ? 'winner_matchs' : 'loser_matchs'
            this[str][loadMatchs[i].match_group][loadMatchs[i].match_level][loadMatchs[i].id] = await this.getMatch(loadMatchs[i])
          }
        }
      }
      if (this.form.id == this.start_id && (this.form.match_status == 0 || this.form.match_status == 2)) {
        await writeTextFile('obs/p1_title.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p2_title.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p1_score.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p2_score.txt', '', { dir: BaseDirectory.App })
        this.start_id = 0
      }
      if (this.form.match_status == 1 && this.start_id != this.form.id) {
        await this.startMatch(this.form.match_type == 1 ? 'winner_matchs' : 'loser_matchs', this.form.match_group, this.form.match_level, this.form.id)
      }
      if (this.form.match_status == 2) {
        this.start_id = 0
      }
      if(this.form.change_user_place != source.change_user_place && this.form.match_status == 1){
        if(this.form.change_user_place == 0){
          await writeTextFile('obs/p1_title.txt', this.form.player_one_title, { dir: BaseDirectory.App })
          await writeTextFile('obs/p2_title.txt', this.form.player_two_title, { dir: BaseDirectory.App })
        }else{
          await writeTextFile('obs/p2_title.txt', this.form.player_one_title, { dir: BaseDirectory.App })
          await writeTextFile('obs/p1_title.txt', this.form.player_two_title, { dir: BaseDirectory.App })
        }
      }
      if(this.form.change_user_place == 0){
        await writeTextFile('obs/p1_score.txt', this.form.player_one_score + '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p2_score.txt', this.form.player_two_score + '', { dir: BaseDirectory.App })
      }else{
        await writeTextFile('obs/p2_score.txt', this.form.player_one_score + '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p1_score.txt', this.form.player_two_score + '', { dir: BaseDirectory.App })
      }
      this.form = {
        player_one_title: '',
        player_two_title: '',
        player_one_score: 0,
        player_two_score: 0,
        winner: 0,
        match_id: 0,
        match_status: 0,
        id: 0,
        change_user_place: 0,
      }
      this.sendshareFunc()
      layer.closeAll()
    },
    cancel() {
      this.showEditor = false
      this.form = JSON.parse(localStorage.getItem('source'))
      this[this.editParams.chars][this.editParams.i][this.editParams.j][this.editParams.k] = this.form
      localStorage.removeItem('source')
    },
    async checkStatus() {
      const temp = await this.$db.select("select id from matchs where game_id = ? and match_status = 1 and id <> ?", [this.form.game_id, this.form.id])
      if (temp.length > 0) {
        layer.msg('已有比赛在进行中，请先结束该比赛才可以开始新的比赛')
        this.form.match_status = 0
        return
      }
    },
    async toFinal(){
      const temp = await this.$db.select("select id from matchs where game_id = ? and match_status = 0 and match_type in(1,2) and is_final = 0 limit 1", [this.game.id])
      if(temp.length > 0){
        layer.msg("小组赛的比赛必须全部结束后才能进入决赛")
        return
      }
      layer.confirm('结束小组赛将进入决赛，小组赛的信息将不能再更改，确定进入决赛吗？', async index => {
        await this.$db.execute("update games set game_status = 2 where id = ?", [this.game.id])
        this.$router.push({ path: `/matchs/${this.game.id}/1` })
        await this.reload()
        await this.sendshareFunc()
        layer.close(index)
      })
    },
    async endMatch(){
      layer.confirm('结束比赛将不能再更改比赛信息，确定结束比赛吗？', async index => {
        try {
          localStorage.removeItem(`signMatch${this.game.id}`)
        } catch (error) {
          
        }
        await writeTextFile('obs/game_name.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/match.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p1_title.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p2_title.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p1_score.txt', '', { dir: BaseDirectory.App })
        await writeTextFile('obs/p2_score.txt', '', { dir: BaseDirectory.App })
        await this.$db.execute("update games set game_status = 3 where id = ?", [this.game.id])
        layer.close(index)
        this.game = await this.$getGame(this.game.id)
        await this.reload(1)
      })
    },
    convertToTwoteenSix(num){
      var str=''
      while(num!==0){
        var temp = num%26
        str+=String.fromCharCode(temp+64)
        num = parseInt(num/26)
      }
      //从后往前存储，进行反转
      return str.split('').reverse().join('')
    },
  }
}
</script>

<style scoped>
.top {
  margin-bottom: 10px;
}

h5 {
  margin: 10px 0;
}

.matchs {
  width: 100%;
  overflow-x: scroll;
}

.matchs::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.matchs::-webkit-scrollbar-thumb {
  background-color: rgba(144, 147, 153, .5);
  background-clip: padding-box;
  min-height: 6px;
  -webkit-border-radius: 2em;
  -moz-border-radius: 2em;
  border-radius: 2em;
  transition: background-color .3s;
  cursor: pointer;
}

.matchs::-webkit-scrollbar-track {
  width: 6px;
  background: rgba(#101F1C, 0.1);
  -webkit-border-radius: 2em;
  -moz-border-radius: 2em;
  border-radius: 2em;
}

.matchs::-webkit-scrollbar-thumb:hover {
  background-color: rgba(144, 147, 153, .3);
}

.matchs>.scoll_main {
  display: flex;
  flex-wrap: nowrap;
}

.list-group {
  margin: 0;
  padding: 0;
}

.matchs>.scoll_main>.list {
  display: flex;
  flex-direction: column;
  margin-right: 20px;
}

.player {
  width: auto;
  display: flex;
  align-items: center;
  padding: 0;
  height: 30px;
  overflow: hidden;
}

.player>div {
  height: 30px;
  line-height: 30px;
  text-align: center;
}

.player>.title {
  padding: 0 10px;
  width: 120px;
}

.player>.nums {
  width: 39px;
  border-left: 1px solid #dee2e6;
}

.player>.sort_num {
  width: 39px;
  border-right: 1px solid #dee2e6;
}

.line {
  display: flex;
}

.line>.left.ji {
  margin-top: 34px;
  width: 29px;
  height: 35px;
  border-top: 1px solid #dee2e6;
  border-right: 1px solid #dee2e6;
}

.line>.left.ou {
  width: 29px;
  height: 35px;
  border-bottom: 1px solid #dee2e6;
  border-right: 1px solid #dee2e6;
}

.line>.right {
  margin-top: 69px;
  width: 30px;
  height: 1px;
  background: #dee2e6;
}

.player_list>.item {
  display: flex;
  align-items: flex-start;
}
.player_list>.item:last-child{
  padding-bottom: 0 !important;
}

.player_list>.item>.round {
  width: 35px;
  text-align: center;
}

.item_left {
  height: 60px;
  width: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.item_left>* {
  width: 100%;
}

input.nums {
  height: 30px;
  border: 0;
  outline: none;
  border-radius: 0;
  background: rgba(0, 0, 0, 0);
  text-align: center;
}

.list_title {
  text-align: center;
}

.item>.manager {
  display: flex;
  flex-direction: column;
  width: 60px;
}

fieldset {
  width: 100%;
  padding: .35em .625em .75em;
  margin: 0 0 20px 0;
  border: 1px solid silver;
  color: #333;
  border: #06c dashed 1px;
}

legend {
  margin-bottom: 0px;
  padding: .5em;
  width: auto;
  color: #06c;
  font-weight: 800;
  background: #fff;
}

.tx-line {
  height: 0;
  border-top: 1px solid #000;
  text-align: center;
  margin: 20px 0;
}

.tx-line>.text {
  position: relative;
  top: -14px;
  background-color: #fff;
}
</style>