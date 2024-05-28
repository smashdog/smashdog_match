import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap/dist/js/bootstrap.bundle.min.js"
import "bootstrap/dist/js/bootstrap.min.js"
import "layui/dist/css/layui.css"
import "layui/dist/layui.js"

const app = createApp(App)

app.use(router)

app.mount('#app')
