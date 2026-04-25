import request from '@/utils/http'

/**
 * 通用API
 */
export default {
  /**
   * GET请求
   * @param url 请求URL
   * @param params 搜索参数
   * @returns 数据列表
   */
  get(url: string, params?: Record<string, any>) {
    return request.get<any>({
      url: url,
      params
    })
  },

  /**
   * POST请求
   * @param url 请求URL
   * @param data 请求参数
   * @returns 数据列表
   */
  post(url: string, data: Record<string, any>) {
    return request.post<any>({
      url: url,
      data
    })
  },

  /**
   * 下载文件
   * @param url
   * @returns
   */
  download(url: string) {
    return request.request<any>({
      url: url,
      method: 'post',
      timeout: 0,
      responseType: 'blob'
    })
  }
}
