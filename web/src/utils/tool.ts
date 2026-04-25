import { useUserStore } from '@/store/modules/user'

/**
 * 检查权限
 * @param permission
 * @returns
 */
export function checkAuth(permission: string) {
  const userStore = useUserStore()
  const userButtons = userStore.getUserInfo.buttons
  // 超级管理员
  if (userButtons?.includes('*')) {
    return true
  }

  // 如果按钮为空或未定义，移除元素
  if (!userButtons?.length) {
    return false
  }

  const hasPermission = userButtons.some((item) => item === permission)

  // 如果没有权限，移除元素
  if (hasPermission) {
    return true
  }
  return false
}

/**
 * 下载文件
 * @param res 响应数据
 * @param downName 下载文件名
 */
export function downloadFile(res: any, downName: string = '') {
  const aLink = document.createElement('a')
  let fileName = downName
  let blob = res //第三方请求返回blob对象

  //通过后端接口返回
  if (res.headers && res.data) {
    blob = new Blob([res.data], {
      type: res.headers['content-type'].replace(';charset=utf8', '')
    })
    if (!downName) {
      const contentDisposition = decodeURI(res.headers['content-disposition'])
      const result = contentDisposition.match(/filename="(.+)/gi)
      fileName = result?.[0].replace(/filename="(.+)/gi, '') || ''
      fileName = fileName.replace('"', '')
    }
  }

  aLink.href = URL.createObjectURL(blob)
  // 设置下载文件名称
  aLink.setAttribute('download', fileName)
  document.body.appendChild(aLink)
  aLink.click()
  document.body.removeChild(aLink)
  URL.revokeObjectURL(aLink.href)
}
