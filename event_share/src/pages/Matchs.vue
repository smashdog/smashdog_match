<template>
  <h3>{{ game.title }}</h3>
  <div class="btn-group" role="group">
    <button type="button" class="btn btn-primary" @click="getAllMatchs()">刷新</button>
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
                      :style="{color: match.change_user_place == 0 ? (match.player_one_id == 0 ? '#cccccc' : '#000000') : (match.player_two_id == 0 ? '#cccccc' : '#000000')}">
                      {{ match.change_user_place == 0 ? match.player_one_title : match.player_two_title }}</div>
                    <div :class="{nums: true, 'bg-danger-subtle':  match.change_user_place == 0 ? (match.winner == 1 ? true : false) : (match.winner == 2 ? true : false)}">
                    {{ match.change_user_place == 0 ? match.player_one_score : match.player_two_score }}</div>
                  </div>
                  <div class="list-group-item list-group-item-action player">
                    <div class="sort_num">{{ match.player_two_sort_num }}</div>
                    <div class="title" :title="match.player_two_title"
                      :style="{color: match.change_user_place == 0 ? (match.player_two_id == 0 ? '#cccccc' : '#000000') : (match.player_one_id == 0 ? '#cccccc' : '#000000')}">
                      {{ match.change_user_place == 0 ? match.player_two_title : match.player_one_title }}</div>
                    <div :class="{nums: true, 'bg-danger-subtle':  match.change_user_place == 0 ? (match.winner == 2 ? true : false) : (match.winner == 1 ? true : false)}">
                    {{ match.change_user_place == 0 ? match.player_two_score : match.player_one_score }}</div>
                  </div>
                </div>
                <div class="manager">
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
                    <div class="title" :title="match.player_one_title" :style="{color: match.change_user_place == 0 ? (match.player_one_id == 0 ? '#cccccc' : '#000000') : (match.player_two_id == 0 ? '#cccccc' : '#000000')}">
                      {{ match.change_user_place == 0 ? match.player_one_title : match.player_two_title }}</div>
                    <div :class="{nums: true, 'bg-danger-subtle':  match.change_user_place == 0 ? (match.winner == 1 ? true : false) : (match.winner == 2 ? true : false)}">
                    {{ match.change_user_place == 0 ? match.player_one_score : match.player_two_score }}</div>
                  </div>
                  <div class="list-group-item list-group-item-action player">
                    <div class="sort_num">{{ match.player_two_sort_num }}</div>
                    <div class="title" :title="match.player_two_title" :style="{color: match.change_user_place == 0 ? (match.player_two_id == 0 ? '#cccccc' : '#000000') : (match.player_one_id == 0 ? '#cccccc' : '#000000')}">
                      {{ match.change_user_place == 0 ? match.player_two_title : match.player_one_title }}</div>
                    <div :class="{nums: true, 'bg-danger-subtle':  match.change_user_place == 0 ? (match.winner == 2 ? true : false) : (match.winner == 1 ? true : false)}">
                      {{ match.change_user_place == 0 ? match.player_two_score : match.player_one_score }}</div>
                  </div>
                </div>
                <div class="manager">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </fieldset>
</template>

<script>
export default {
  data() {
    return {
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
      key: '',
    }
  },
  async created() {
    if(!this.$route.params.key){
      layer.msg('key错误')
      return
    }
    this.key = this.$route.params.key
    if(!this.$route.params.id){
      layer.msg('id错误')
      return
    }
    this.game.id = this.$route.params.id
    if(!this.$route.params.end){
      this.is_final = 0
    }else{
      this.is_final = 1
    }
    await this.getAllMatchs()
  },
  methods: {
    async reload(){
      this.group_count = []
      this.winner_matchs = []
      this.loser_matchs = []
      await this.getAllMatchs()
    },
    async getAllMatchs(){
      let index = layer.load()
      let res = await this.postData(`${location.origin}/api/index.php`, {
        key: this.key,
        id: this.game.id,
      })
      if(res.code != 0){
        layer.msg(res.msg)
        return
      }
      this.winner_idx = res.data.winner_idx
      this.loser_idx = res.data.loser_idx
      this.group_count = res.data.group_count
      this.game = res.data.game
      this.winner_matchs = res.data.winner_matchs
      this.loser_matchs = res.data.loser_matchs
      layer.close(index)
    },
    async postData(url = "", data = {}) {
      let formdata = new FormData()
      for(let key in data){
        formdata.append(key, data[key])
      }
      let controller = new AbortController()
      controller.signal.addEventListener('abort', () => {
        layer.msg('获取比赛信息超时')
      })
      let t = setTimeout(() => {
        controller.abort()
      }, 3000)
      const response = await fetch(url, {
        method: "POST",
        cache: "no-cache",
        body: formdata,
        signal: controller.signal,
      })
      clearTimeout(t)
      return await response.json()
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