import request from '@/utils/http'

/**
 * 附件API
 */
export default {
  /**
   * 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/system/attachment/list',
      params
    })
  },

  /**
   * 获取附件详情
   */
  read(id: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/api/system/attachment/detail/' + id
    })
  },

  /**
   * 上传附件
   */
  upload(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/system/attachment/upload',
      data: params,
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  /**
   * 更新附件名称
   */
  update(params: Record<string, any>) {
    return request.put<any>({
      url: '/api/system/attachment/update/' + params.id,
      data: params
    })
  },

  /**
   * 删除附件（单条）
   */
  delete(id: number | string) {
    return request.del<any>({
      url: '/api/system/attachment/delete/' + id
    })
  },

  /**
   * 批量删除附件
   */
  batchDelete(params: { ids: (number | string)[] }) {
    return request.del<any>({
      url: '/api/system/attachment/batchDelete',
      data: params
    })
  },

  /**
   * 移动附件到分类
   */
  move(params: { ids: (number | string)[]; category_id: number | null }) {
    return request.put<any>({
      url: '/api/system/attachment/move',
      data: params
    })
  },

  /**
   * 下载附件
   */
  download(id: number | string) {
    return request.get<any>({
      url: '/api/system/attachment/download/' + id,
      responseType: 'blob'
    })
  },

  /**
   * 获取存储统计
   */
  stats() {
    return request.get<any>({
      url: '/api/system/attachment/stats'
    })
  },

  /**
   * 附件分类列表（tree=true 返回树形）
   */
  categoryList(params?: Record<string, any>) {
    return request.get<any>({
      url: '/api/system/attachment-category/list',
      params
    })
  }
}
