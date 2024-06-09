<template>
  <div class="all">
    <h3>{{ game.title }}</h3>
    <div class="top">
      <div class="btn-group" role="group">
        <button type="button" class="btn btn-sm btn-danger" @click="endMatch()">结束比赛</button>
        <button type="button" class="btn btn-sm btn-secondary" @click="$router.push('/games')">返回比赛列表</button>
        <!-- <button type="button" class="btn btn-sm btn-danger" @click="clearMatch()" v-if="$debug">清除数据</button> -->
      </div>
    </div>
    <div class="list">
      <div class="input-group p1">
        <div class="input-group-text">1p</div>
        <div class="input-group-text title" @click="fastCopy(p1.fast_copy)">{{ p1.title }}</div>
        <input class="" type="number" :value="p1.score" @change="changeScore(1, $event.target.value)" min="0" max="99"
          step="1" name="scorep1" :disabled="game.game_status != 1">
      </div>
      <div class="input-group p2">
        <input class="" type="number" :value="p2.score" @change="changeScore(2, $event.target.value)" min="0" max="99"
          step="1" name="scorep2" :disabled="game.game_status != 1">
        <div class="input-group-text title" @click="fastCopy(p2.fast_copy)">{{ p2.title }}</div>
        <div class="input-group-text">2p</div>
      </div>
    </div>
    <div class="user-list">
      <div class="input-group mb-3">
        <input type="text" class="form-control" placeholder="搜索用户" aria-label="search" name="search" v-model="search">
        <button type="button" class="btn btn-primary" name="searchbutton" @click="searchPlayers()" :disabled="game.game_status != 1">搜索</button>
        <button type="button" class="btn btn-primary" name="clearsearchbutton"
          @click="clearSearchPlayers()" :disabled="game.game_status != 1">清除搜索</button>
      </div>
      <div class="user-items">
        <div class="input-group" v-for="player in players" v-show="player.show">
          <div class="input-group-text" @click="fastCopy(player.fast_copy)">
            {{ player.title }}
          </div>
          <button type="button" :class="{btn: true, 'btn-sm':true, 'btn-primary': game.game_status == 1 ? true : false, 'btn-secondary': game.game_status != 1 ? true : false}" :disabled="game.game_status != 1" @click="start(player, 1)">1p</button>
          <button type="button" :class="{btn: true, 'btn-sm':true, 'btn-primary': game.game_status == 1 ? true : false, 'btn-secondary': game.game_status != 1 ? true : false}" :disabled="game.game_status != 1" class="btn btn-sm btn-primary" @click="start(player, 2)">2p</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { BaseDirectory, writeTextFile } from '@tauri-apps/api/fs'
import { writeText } from '@tauri-apps/api/clipboard'
export default {
  async created() {
    const game = await this.$getGame(this.$route.params.id)
    if (typeof game == 'string') {
      layer.msg(game, () => {
        this.$router.push('/games')
      })
    }
    this.game = game
    await this.getPlayers()
  },
  data() {
    return {
      game: {},
      players: [],
      search: '',
      p1: {
        title: '',
        score: 0,
        fast_copy: '',
      },
      p2: {
        title: '',
        score: 0,
        fast_copy: '',
      },
    }
  },
  methods: {
    async endMatch() {
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
      })
    },
    async changeScore(p, score) {
      await writeTextFile(`obs/p${p}_score.txt`, `${score}`, { dir: BaseDirectory.App })
    },
    async start(user, p) {
      await writeTextFile(`obs/p${p}_title.txt`, user.title, { dir: BaseDirectory.App })
      await writeTextFile(`obs/p${p}_score.txt`, '0', { dir: BaseDirectory.App })
      this[`p${p}`].title = user.title
      this[`p${p}`].score = 0
      this[`p${p}`].fast_copy = user.fast_copy
    },
    searchPlayers() {
      for (let i = 0; i < this.players.length; i++) {
        if (this.players[i].title.indexOf(this.search) > -1) {
          this.players[i].show = true
        } else {
          this.players[i].show = false
        }
      }
    },
    async getPlayers() {
      let players = await this.$db.select("select * from players where game_id = ?", [this.game.id])
      for (let i = 0; i < players.length; i++) {
        players[i].show = true
      }
      this.players = players
    },
    clearSearchPlayers() {
      this.search = ''
      for (let i = 0; i < this.players.length; i++) {
        this.players[i].show = true
      }
    },
    async fastCopy(text){
      if (text.length == 0) {
        return
      }
      await writeText(text)
      layer.msg('复制成功')
    },
  }
}
</script>

<style scoped>
.all>*{
  margin-bottom: 10px;
}
.list {
  display: flex;
}

.list>.input-group {
  width: 50%;
  display: flex;
}
.list>.input-group.p1{
  justify-content: flex-end;
}
.list>.input-group.p2{
  justify-content: flex-start;
}

.list>.input-group>.title{
  width: 200px;
}

.list>.input-group>input{
  text-align: center;
}
.user-items{
  display: flex;
  flex-wrap: wrap;
}
.user-items>.input-group{
  width: 20%;
}
</style>