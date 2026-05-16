import request from '@/utils/http'

/**
 * 附件分类API
 */
export default {
  /**
   * 数据列表（传 { tree: true } 返回树形结构）
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/attachment-category/list',
      params
    })
  },

  /**
   * 读取数据
   */
  read(id: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/api/system/attachment-category/detail/' + id
    })
  },

  /**
   * 创建分类
   */
  save(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/system/attachment-category/create',
      data: params
    })
  },

  /**
   * 更新分类
   */
  update(params: Record<string, any>) {
    return request.put<any>({
      url: '/api/system/attachment-category/update/' + params.id,
      data: params
    })
  },

  /**
   * 删除分类
   */
  delete(id: number | string) {
    return request.del<any>({
      url: '/api/system/attachment-category/delete/' + id
    })
  }
}
