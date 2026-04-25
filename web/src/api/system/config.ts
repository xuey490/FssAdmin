import request from '@/utils/http'

/**
 * 系统设置API
 */
export default {
  /**
   * 获取数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  groupList(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/core/configGroup/list',
      params
    })
  },

  /**
   * 创建数据
   * @param params 数据参数
   * @returns 执行结果
   */
  save(params: Record<string, any>) {
    return request.post<Api.Common.ApiData>({
      url: '/api/core/configGroup/save',
      data: params
    })
  },

  /**
   * 更新数据
   * @param params 数据参数
   * @returns 执行结果
   */
  update(params: Record<string, any>) {
    return request.put<any>({
      url: `/api/core/configGroup/update/${params.id}`,
      data: params
    })
  },

  /**
   * 删除数据
   * @param id 数据ID
   * @returns 执行结果
   */
  delete(params: number | Record<string, any>) {
    // 兼容单行删除（传数字）和对象格式
    const id = typeof params === 'number' ? params : params.id
    return request.del<any>({
      url: `/api/core/configGroup/delete/${id}`
    })
  },

  /**
   * 系统设置项数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  configList(params: Record<string, any>) {
    return request.get<Api.Common.ApiData>({
      url: '/api/core/config/list',
      params
    })
  },

  /**
   * 创建系统设置项数据
   * @param params 数据参数
   * @returns 执行结果
   */
  configSave(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/core/config/save',
      data: params
    })
  },

  /**
   * 更新系统设置项数据
   * @param params 数据参数
   * @returns 执行结果
   */
  configUpdate(params: Record<string, any>) {
    return request.put<any>({
      url: `/api/core/config/update/${params.id}`,
      data: params
    })
  },

  /**
   * 删除数据
   * @param params 数据ID（数字）或包含 ids 的对象
   * @returns 执行结果
   */
  configDelete(params: number | Record<string, any>) {
    // 兼容单行删除（传数字）和批量删除（传 { ids: [...] }）
    const data = typeof params === 'number' ? { ids: [params] } : params
    return request.del<any>({
      url: '/api/core/config/delete',
      data
    })
  },

  /**
   * 批量修改配置
   * @param params
   * @returns
   */
  batchUpdate(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/core/config/batchUpdate',
      data: params
    })
  },

  /**
   * 邮件测试
   * @param params
   * @returns
   */
  emailTest(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/core/configGroup/testEmail',
      data: params
    })
  }
}
