import { createApp } from "vue"
import "./styles.css"
import App from "./App.vue"
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap/dist/js/bootstrap.bundle.min.js"
import "bootstrap/dist/js/bootstrap.min.js"
import "layui/dist/css/layui.css"
import "layui/dist/layui.js"
import router from './router'
import { invoke } from '@tauri-apps/api'
import Database from "tauri-plugin-sql-api"
import { type } from "@tauri-apps/api/os"
import Vue3Dragscroll from 'vue3-dragscroll'
import tooltip from "./components/tooltip/directive"
import { BaseDirectory, writeTextFile, exists, createDir, writeBinaryFile } from '@tauri-apps/api/fs'
import { fetch as tfetch, Body, getClient } from "@tauri-apps/api/http"

const app = createApp(App)

const db_patch = await exists('database', {dir: BaseDirectory.App})
if(!db_patch){
  await createDir('database', {dir: BaseDirectory.App, recursive: true})
  const db_file = await exists('database/database', {dir: BaseDirectory.App})
  if(!db_file){
    await writeBinaryFile('database/database', new Uint8Array(), {dir: BaseDirectory.App})
    const db = await Database.load("sqlite:./database/database")
    db.execute(`CREATE TABLE games (
      id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
      title TEXT(255) NOT NULL
    , game_status INTEGER DEFAULT (0) NOT NULL, group_nums INTEGER DEFAULT (0) NOT NULL, game_format INTEGER DEFAULT (1) NOT NULL, game_sort INTEGER DEFAULT (0), group_wins INTEGER DEFAULT (1));
    
    CREATE INDEX games_game_sort_IDX ON games (game_sort);
    
    CREATE TABLE "matchs" (
      id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
      game_id INTEGER DEFAULT (0) NOT NULL,
      player_one_id INTEGER DEFAULT (0) NOT NULL,
      player_two_id INTEGER DEFAULT (0) NOT NULL,
      player_one_score INTEGER DEFAULT (0) NOT NULL,
      player_two_score INTEGER DEFAULT (0) NOT NULL,
      winner INTEGER DEFAULT (0) NOT NULL,
      match_status INTEGER DEFAULT (0) NOT NULL,
      parent_id_1 INTEGER DEFAULT (0) NOT NULL,
      parent_id_2 INTEGER DEFAULT (0) NOT NULL,
      match_group INTEGER DEFAULT (0) NOT NULL,
      match_level INTEGER DEFAULT (0) NOT NULL,
      match_type INTEGER DEFAULT (1) NOT NULL,
      change_user_place INTEGER DEFAULT (0),
      group_sort INTEGER DEFAULT (0) NOT NULL
    , is_final INTEGER DEFAULT (0));
    
    CREATE INDEX matchs_player_one_id_IDX ON matchs (player_one_id);
    CREATE INDEX matchs_player_two_id_IDX ON matchs (player_two_id);
    CREATE INDEX matchs_game_id_IDX ON matchs (game_id);
    CREATE INDEX matchs_parent_id_1_IDX ON matchs (parent_id_1);
    CREATE INDEX matchs_parent_id_2_IDX ON matchs (parent_id_2);
    CREATE INDEX matchs_match_group_IDX ON matchs (match_group);
    CREATE INDEX matchs_match_level_IDX ON matchs (match_level);
    CREATE INDEX matchs_match_type_IDX ON matchs (match_type);
    CREATE INDEX matchs_group_sort_IDX ON matchs (group_sort);
    CREATE INDEX matchs_is_final_IDX ON matchs (is_final);
    CREATE INDEX matchs_winner_IDX ON matchs (winner);
    
    CREATE TABLE "players" (
      id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
      game_id INTEGER NOT NULL,
      title TEXT(255) NOT NULL,
      sort_num INTEGER DEFAULT (0)
    , fast_copy TEXT);
    
    CREATE INDEX player_games_id_IDX ON "players" (game_id);
    CREATE INDEX player_sort_num_IDX ON "players" (sort_num);`)
  }
}

let base_dir = await exists('obs', {dir: BaseDirectory.App})
if(!base_dir){
  await createDir('obs', {dir: BaseDirectory.App, recursive: true})
}
let file_game_name = await exists('obs/game_name.txt', {dir: BaseDirectory.App})
if(!file_game_name){
  await writeTextFile('obs/game_name.txt', '', { dir: BaseDirectory.App })
}
let p1_title = await exists('obs/p1_title.txt', {dir: BaseDirectory.App})
if(!p1_title){
  await writeTextFile('obs/p1_title.txt', '', { dir: BaseDirectory.App })
}
let p2_title = await exists('obs/p2_title.txt', {dir: BaseDirectory.App})
if(!p2_title){
  await writeTextFile('obs/p2_title.txt', '', { dir: BaseDirectory.App })
}
let p1_score = await exists('obs/p1_score.txt', {dir: BaseDirectory.App})
if(!p1_score){
  await writeTextFile('obs/p1_score.txt', '0', { dir: BaseDirectory.App })
}
let p2_score = await exists('obs/p2_score.txt', {dir: BaseDirectory.App})
if(!p2_score){
  await writeTextFile('obs/p2_score.txt', '0', { dir: BaseDirectory.App })
}
let match = await exists('obs/match.txt', {dir: BaseDirectory.App})
if(!match){
  await writeTextFile('obs/match.txt', '', { dir: BaseDirectory.App })
}
if(import.meta.env.VITE_DEBUG){
  app.config.globalProperties.$debug = true
}else{
  app.config.globalProperties.$debug = false
}
app.config.globalProperties.$createDb = async () => {
  try {
    const db = await Database.load("sqlite:./database/database")
    return db
  } catch (error) {
    console.error(error)
  }
}


app.config.globalProperties.$db = await app.config.globalProperties.$createDb()
app.config.globalProperties.$pageSize = 20

// 判断更新表是否存在，不存在创建
let update_table = await app.config.globalProperties.$db.select(`SELECT name FROM sqlite_master WHERE type='table' AND name='update_table'`);
if(update_table.length == 0){
  await app.config.globalProperties.$db.execute(`CREATE TABLE update_table (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    version_1 INTEGER NOT NULL,
    version_2 INTEGER NOT NULL,
    version_3 INTEGER NOT NULL
  );
  
  CREATE INDEX update_table_version_1_IDX ON update_table (version_1);
  CREATE INDEX update_table_version_2_IDX ON update_table (version_2);
  CREATE INDEX update_table_version_3_IDX ON update_table (version_3);`)
}
import update from './update/sql.json' assert { type: 'JSON' }
for(let k in update){
  const version = k.split('.')
  let updated = await app.config.globalProperties.$db.select(`select id from update_table where version_1 = ${version[0]} and version_2 = ${version[1]} and version_3 = ${version[2]}`)
  if(updated.length == 0){
    for(let k1 = 0; k1 < update[k].length; k1++){
      let need_update = true
      if(update[k][k1].prepare.length > 0){
        let prepare = await app.config.globalProperties.$db.select(update[k][k1].prepare)
        if(prepare.length > 0){
          need_update = false
        }
      }
      if(need_update){
        await app.config.globalProperties.$db.execute(update[k][k1].update)
      }
    }
    await app.config.globalProperties.$db.execute(`insert into update_table (version_1, version_2, version_3) values(${version[0]}, ${version[1]}, ${version[2]})`)
  }
}

app.config.globalProperties.$page = async (table, page, pageSize, where, fields, order) => {
  if(!pageSize || isNaN(pageSize) || pageSize <= 0 || pageSize > 200){
    pageSize = app.config.globalProperties.$pageSize
  }
  if(!page || isNaN(page) || page < 1){
    page = 1
  }
  let whereStr = ``
  if(where){
    whereStr = ` WHERE ${where}`
  }
  let result = await app.config.globalProperties.$db.select(`SELECT COUNT(*) AS mycount FROM ${table} ${whereStr}`)
  let maxPage = 1, count = 0
  if(result.length > 0){
    count = result[0].mycount
    maxPage = Math.ceil(count / pageSize)
  }
  const limit = ` LIMIT ${pageSize} OFFSET ${(page - 1) * pageSize}`
  if(!order){
    order = ' ORDER BY id DESC'
  }else{
    order = ` ORDER BY ${order}`
  }
  if(!fields){
    fields = '*'
  }
  result = await app.config.globalProperties.$db.select(`SELECT ${fields} FROM ${table} ${whereStr} ${order} ${limit}`)
  return {
    page: page,
    pageSize: pageSize,
    count: count,
    maxPage: maxPage,
    data: result,
  }
}

app.config.globalProperties.$setPageSize = (pageSize) => {
  if(!pageSize || isNaN(pageSize) || pageSize <= 0 || pageSize > 200){
    pageSize = 200
  }
  localStorage.setItem('pageSize', pageSize)
}
if(!localStorage.getItem('pageSize')){
  app.config.globalProperties.$setPageSize()
}

app.config.globalProperties.$getGame = async (id) => {
  if(!id || isNaN(id) || id <= 0){
    return '比赛id错误'
  }
  let result = await app.config.globalProperties.$db.select('SELECT * FROM games WHERE id = ?', [id])
  if(result.length > 0){
    return result[0]
  }else{
    return '比赛id不存在'
  }
}

app.config.globalProperties.$tfetch = async (url = "", data = {}) => {
  const index = layer.load()
  let formdata = new FormData()
  for(let key in data){
    formdata.append(key, data[key])
  }
  const body = Body.form(formdata)
  try {
    let res = await tfetch(url, {
      method: "POST",
      timeout: 5,
      body: body
    })
    layer.close(index)
    return res.data
  } catch (error) {
    layer.close(index)
    return {code: -1, msg: '网络错误'}
  }
}

app.config.globalProperties.$postData = async (url = "", data = {}) => {
  let formdata = new FormData()
  for(let key in data){
    formdata.append(key, data[key])
  }
  const response = await fetch(url, {
    method: "POST",
    cache: "no-cache",
    mode: "no-cors",
    body: formdata,
  })
  try {
    return await response.text()
  } catch (error) {
    console.error(error)
  }
  try {
  } catch (error) {
    
  }
}
app.config.globalProperties.$sendShare = async (data) => {
  if(!localStorage.getItem('config')){
    return false
  }
  let config = JSON.parse(localStorage.getItem('config'))
  if(!config.shareApi){
    return false
  }
  if(config.shareApi.url.length == 0 || config.shareApi.key.length == 0){
    return false
  }
  data.action = 'update'
  data.key = config.shareApi.key
  await app.config.globalProperties.$postData(config.shareApi.url, data)
}

app.use(router).use(Vue3Dragscroll).use(tooltip).mount('#app')
