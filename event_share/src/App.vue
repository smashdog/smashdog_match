<script setup>
</script>

<template>
  <router-view></router-view>
</template>
<style>
body{
  min-height: 100vh;
  margin: 0;
  padding: 0;
  overflow-x: hidden;
}
body>div{
    position: relative;
}
.container{
  text-align: left;
}
.window{
    position: fixed;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, .5);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 10;
}
.window>.div_form{
    max-width: 100vw;
    background: #ffffff;
    padding: 20px;
}
.input_inline{
    display: flex;
}
.input_inline>div{
    margin-left: 5px;
}
td{
  vertical-align: middle;
}
th{
  text-align: center;
}
ul.pagination>li{
  cursor: pointer;
}
.in_progress_color{
  background-color: #31bdec ;
}
.in_progress_color *{
  background-color: #31bdec ;
}
.end{
  background-color: #16b777;
}
.end *{
  background-color: #16b777;
}
</style>
