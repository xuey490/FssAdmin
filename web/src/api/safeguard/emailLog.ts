import request from '@/utils/http'

/**
 * 邮件日志数据API
 */
export default {
  /**
   * 数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/core/email/index',
      params
    })
  },

  /**
   * 删除数据
   * @param params
   * @returns
   */
  delete(params: Record<string, any>) {
    return request.del<any>({
      url: '/api/core/email/destroy',
      data: params
    })
  }
}
