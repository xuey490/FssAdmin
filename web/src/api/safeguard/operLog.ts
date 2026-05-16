import request from '@/utils/http'

/**
 * 操作日志数据API
 */
export default {
  /**
   * 数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/core/logs/getOperLogPageList',
      params
    })
  },

  /**
   * 删除数据
   * @param params 数据ID（数字）或包含 ids 的对象
   * @returns
   */
  delete(params: number | Record<string, any>) {
    // 兼容单行删除（传数字）和批量删除（传 { ids: [...] }）
    const data = typeof params === 'number' ? { ids: [params] } : params
    return request.del<any>({
      url: '/api/core/logs/deleteOperLog',
      data
    })
  }
}
