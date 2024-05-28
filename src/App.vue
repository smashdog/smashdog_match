<template>
  <div class="container">
    <router-view></router-view>
  </div>
</template>
<script>
export default {
  created() {
    // this.$router.push(`/games`)
  },
  mounted() {
    if (!this.$debug) {
      document.addEventListener('contextmenu', function (event) {
        event.preventDefault()
      })
      document.addEventListener('keydown', function (event) {
        // Prevent F5 or Ctrl+R (Windows/Linux) and Command+R (Mac) from refreshing the page
        if (event.key === 'F5' || (event.ctrlKey && event.key === 'r') || (event.metaKey && event.key === 'r')) {
          event.preventDefault()
        }
      })
    }
  },
}
</script>
<style>
body {
  min-height: 100vh;
}

body>div {
  position: relative;
}

.container {
  text-align: left;
}

.window {
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

.window>.div_form {
  max-width: 100vw;
  background: #ffffff;
  padding: 20px;
}

.input_inline {
  display: flex;
}

.input_inline>div {
  margin-left: 5px;
}

td {
  vertical-align: middle;
}

th {
  text-align: center;
}

ul.pagination>li {
  cursor: pointer;
}

.in_progress_color {
  background-color: #31bdec;
}

.in_progress_color * {
  background-color: #31bdec;
}

.end {
  background-color: #16b777;
}

.end * {
  background-color: #16b777;
}
</style>
