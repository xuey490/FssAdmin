import request from '@/utils/http'

/**
 * 定时任务API
 */
export default {
  /**
   * 获取定时任务列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/tool/crontab/list',
      params
    })
  },

  /**
   * 获取定时任务详情
   * @param id 任务ID
   * @returns 数据详情
   */
  detail(id: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/api/tool/crontab/detail/' + id
    })
  },

  /**
   * 创建定时任务
   * @param params 任务参数（name, target 必填）
   * @returns 执行结果
   */
  create(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/tool/crontab/create',
      data: params
    })
  },

  /**
   * 更新定时任务
   * @param id 任务ID
   * @param params 任务参数
   * @returns 执行结果
   */
  update(id: number | string, params: Record<string, any>) {
    return request.put<any>({
      url: '/api/tool/crontab/update/' + id,
      data: params
    })
  },

  /**
   * 删除定时任务
   * @param params 数据ID（数字）或包含 ids 的对象
   * @returns 执行结果
   */
  delete(params: number | Record<string, any>) {
    // 兼容单行删除（传数字）和批量删除（传 { ids: [...] }）
    const data = typeof params === 'number' ? { ids: [params] } : params
    return request.del<any>({
      url: '/api/tool/crontab/delete',
      data
    })
  },

  /**
   * 立即执行定时任务
   * @param id 任务ID
   * @returns 执行结果
   */
  run(id: number | string) {
    return request.post<any>({
      url: '/api/tool/crontab/run/' + id
    })
  },

  /**
   * 获取执行日志列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  logList(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/tool/crontab/log/list',
      params
    })
  },

  /**
   * 删除执行日志
   * @param idOrParams 单个id 或包含 ids 数组的对象
   * @returns 执行结果
   */
  logDelete(idOrParams: number | string | Record<string, any>) {
    const data = typeof idOrParams === 'object' ? idOrParams : { id: idOrParams }
    return request.del<any>({
      url: '/api/tool/crontab/log/delete',
      data
    })
  }
}
