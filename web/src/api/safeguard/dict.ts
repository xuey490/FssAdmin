import request from '@/utils/http'

/**
 * 字典数据API
 */
export default {
  // ==================== 字典类型 ====================

  /**
   * 获取字典类型列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  typeList(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/system/dict/type/list',
      params
    })
  },

  /**
   * 创建字典类型
   * @param params 数据参数
   * @returns 执行结果
   */
  save(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/system/dict/type/create',
      data: params
    })
  },

  /**
   * 更新字典类型
   * @param params 数据参数
   * @returns 执行结果
   */
  update(params: Record<string, any>) {
    return request.put<any>({
      url: '/api/system/dict/type/update/' + params.id,
      data: params
    })
  },

  /**
   * 删除字典类型
   * @param id 数据ID
   * @returns 执行结果
   */
  delete(id: number | string) {
    return request.del<any>({
      url: '/api/system/dict/type/delete/' + id
    })
  },

  /**
   * 更新字典类型状态
   * @param id 数据ID
   * @param status 状态
   * @returns 执行结果
   */
  typeUpdateStatus(id: number | string, status: number) {
    return request.put<any>({
      url: '/api/system/dict/type/status/' + id,
      data: { status }
    })
  },

  // ==================== 字典数据 ====================

  /**
   * 字典项数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  dataList(params: Record<string, any>) {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/dict/data/list',
      params
    })
  },

  /**
   * 根据字典编码获取字典数据
   * @param dictCode 字典编码
   * @returns 字典数据列表
   */
  dataByCode(dictCode: string) {
    return request.get<Api.Common.ApiData[]>({
      url: '/api/system/dict/data/code/' + dictCode
    })
  },

  /**
   * 创建字典项数据
   * @param params 数据参数
   * @returns 执行结果
   */
  dataSave(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/system/dict/data/create',
      data: params
    })
  },

  /**
   * 更新字典项数据
   * @param params 数据参数
   * @returns 执行结果
   */
  dataUpdate(params: Record<string, any>) {
    return request.put<any>({
      url: '/api/system/dict/data/update/' + params.id,
      data: params
    })
  },

  /**
   * 删除字典项数据
   * @param id 数据ID
   * @returns 执行结果
   */
  dataDelete(id: number | string) {
    return request.del<any>({
      url: '/api/system/dict/data/delete/' + id
    })
  },

  /**
   * 批量删除字典项数据
   * @param params { ids: number[] }
   * @returns 执行结果
   */
  dataBatchDelete(params: { ids: (number | string)[] }) {
    return request.del<any>({
      url: '/api/system/dict/data/batchDelete',
      data: params
    })
  },

  /**
   * 更新字典项数据状态
   * @param id 数据ID
   * @param status 状态
   * @returns 执行结果
   */
  dataUpdateStatus(id: number | string, status: number) {
    return request.put<any>({
      url: '/api/system/dict/data/status/' + id,
      data: { status }
    })
  }
}
