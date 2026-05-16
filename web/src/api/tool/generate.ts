import request from '@/utils/http'

/**
 * 代码生成API
 */
export default {
  /**
   * 数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/tool/code/index',
      params
    })
  },

  /**
   * 读取表结构
   * @param params 搜索参数
   * @returns 表结构
   */
  read(params: Record<string, any>) {
    return request.get<Api.Common.ApiData>({
      url: '/api/tool/code/read',
      params
    })
  },

  /**
   * 更新数据
   * @param params 数据参数
   * @returns 执行结果
   */
  update(params: Record<string, any>) {
    return request.put<any>({
      url: '/api/tool/code/update',
      data: params
    })
  },

  /**
   * 删除数据
   * @param id 数据ID
   * @returns 执行结果
   */
  delete(params: Record<string, any> | number | string) {
    const data = typeof params === 'object' ? params : { ids: [params] }
    return request.del<any>({
      url: '/api/tool/code/destroy',
      data
    })
  },

  /**
   * 获取表字段
   * @param params 搜索参数
   * @returns 表字段
   */
  getTableColumns(params: Record<string, any>) {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/tool/code/getTableColumns',
      params
    })
  },

  /**
   * 装载数据表
   * @param params 搜索参数
   * @returns 装载结果
   */
  loadTable(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/tool/code/loadTable',
      params
    })
  },

  /**
   * 同步数据表
   * @param params 搜索参数
   * @returns 装载结果
   */
  async(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/tool/code/sync',
      params
    })
  },

  /**
   * 预览代码
   * @param params 搜索参数
   * @returns 预览结果
   */
  preview(params: Record<string, any>) {
    return request.get<any>({
      url: '/api/tool/code/preview',
      params
    })
  },

  /**
   * 生成代码
   * @returns
   */
  generateCode(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/tool/code/generate',
      responseType: 'blob',
      timeout: 20 * 1000,
      params
    })
  },

  /**
   * 生成到文件
   * @returns
   */
  generateFile(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/tool/code/generateFile',
      params
    })
  }
}
