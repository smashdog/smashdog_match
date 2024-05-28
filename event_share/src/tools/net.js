export async function myFetch (url, params) {
  let formdata = new FormData()
  if (typeof params == 'object') {
    for (let i in params) {
      formdata.append(i, params[i])
    }
  }
  try {
    let loading = layer.load()
    let res = await fetch(`${import.meta.env.VITE_HOST}${url}`, {
      method: 'POST',
      body: formdata,
      signal: AbortSignal.timeout(5000)
    })
    layer.close(loading)
    if (res.status != 200) {
      return { code: -1, msg: '网络错误' }
    }
    let data = await res.json()
    return data
  } catch (err) {
    if (err.name === "TimeoutError") {
      return { code: -1, msg: '请求超时' }
    } else if (err.name === "AbortError") {
      return { code: -1, msg: '用户请求中止' }
    } else if (err.name === "TypeError") {
      return { code: -1, msg: '浏览器不支持此请求' }
    } else {
      return { code: -1, msg: '服务器错误，请联系心情过客' }
    }
    layer.closeAll()
  }
}