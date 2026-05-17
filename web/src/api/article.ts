import request from '@/utils/http'

/**
 * 文章管理API
 */
export default {
  /**
   * 获取文章列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/article/list',
      params
    })
  },

  /**
   * 获取文章详情
   * @param id 文章ID
   * @returns 数据详情
   */
  detail(id: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/api/article/detail/' + id
    })
  },

  /**
   * 创建文章
   * @param params 文章参数（title, category_id 必填）
   * @returns 执行结果
   */
  create(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/article/create',
      data: params
    })
  },

  /**
   * 更新文章
   * @param params 文章参数（包含 id 字段）
   * @returns 执行结果
   */
  update(params: Record<string, any>) {
    return request.put<any>({
      url: '/api/article/update/' + params.id,
      data: params
    })
  },

  /**
   * 删除文章
   * @param params 数据ID（数字）或包含 ids 的对象
   * @returns 执行结果
   */
  delete(params: number | Record<string, any>) {
    const data = typeof params === 'number' ? { ids: [params] } : params
    return request.del<any>({
      url: '/api/article/delete',
      data
    })
  },

  /**
   * 更新文章状态
   * @param id 文章ID
   * @param status 状态值
   * @returns 执行结果
   */
  updateStatus(id: number | string, status: number) {
    return request.put<any>({
      url: '/api/article/status/' + id,
      data: { status }
    })
  }
}
