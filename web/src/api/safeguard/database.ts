import request from '@/utils/http'

/**
 * 数据表维护API
 */
export default {
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/core/database/table/list',
      params
    })
  },

  getDataSource(params: Record<string, any> = {}) {
    return request.get<any>({
      url: '/api/core/database/table/dataSource',
      params
    })
  },

  getDetailed(params: Record<string, any> = {}) {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/core/database/table/detailed',
      params
    })
  },

  getRecycle(params: Record<string, any> = {}) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/core/database/recycle/list',
      params
    })
  },

  delete(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/core/database/recycle/destroy',
      data: params
    })
  },

  recovery(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/core/database/recycle/recovery',
      data: params
    })
  },

  optimize(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/core/database/table/optimize',
      data: params
    })
  },

  fragment(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/core/database/table/fragment',
      data: params
    })
  },

  getDdl(params: Record<string, any>) {
    return request.get<any>({
      url: '/api/core/database/table/createSql',
      params
    })
  }
}
