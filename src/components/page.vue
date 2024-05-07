<template>
  <nav aria-label="...">
    <ul class="pagination">
      <li :class="{'page-item': true, 'disabled': page == 1}" @click="getList(1)">
        <a class="page-link"><</a>
      </li>
      <li v-for="i in maxPage" :class="{'page-item': true, 'active': page == i}" :aria-current="page == i ? 'page' : ''" @click="getList(i)">
        <a class="page-link">{{ i }}</a>
      </li>
      <li :class="{'page-item': true, 'disabled': page == maxPage}" @click="getList(maxPage)">
        <a class="page-link">></a>
      </li>
      <li>
        <select class="form-select form-select-sm" aria-label="Default select example" @change="changePageSize($event.target.value)" id="pageSize">
          <option v-for="i in pageSizeList" :value="i" :selected="i == pageSize">{{ i }}</option>
        </select>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  created() {
    this.pageSize = localStorage.getItem('pageSize')
  },
  name: 'mypage',
  data() {
    return {
      pageSizeList: [20,50,100,200],
      pageSize: 0
    }
  },
  props: {
    maxPage: 1,
    page: 1,
    count: 1,
  },
  emits:['getList'],
  methods: {
    getList(page) {
      this.$emit('getList', page)
    },
    changePageSize(pageSize){
      this.$setPageSize(pageSize)
      this.$emit('getList', 1)
    }
  }
}
</script>

<style scoped>
ul.pagination>li{
  cursor: pointer;
}
</style>