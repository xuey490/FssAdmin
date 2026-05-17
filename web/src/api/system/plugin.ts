import request from '@/utils/http'

export interface PluginItem {
  name: string
  title: string
  version: string
  author: string
  installed: boolean
  enabled: boolean
  status_text: string
  dependencies?: Record<string, string> | string[] | null
}

export default {
  list() {
    return request.get<PluginItem[]>({
      url: '/api/system/plugin/list'
    })
  },

  detail(name: string) {
    return request.get<Api.Common.ApiData>({
      url: `/api/system/plugin/detail/${name}`
    })
  },

  create(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/system/plugin/create',
      data: params
    })
  },

  install(params: { name: string; auto_install_dependencies?: boolean; force?: boolean }) {
    return request.post<any>({
      url: '/api/system/plugin/install',
      data: params
    })
  },

  uninstall(params: { name: string; force?: boolean }) {
    return request.post<any>({
      url: '/api/system/plugin/uninstall',
      data: params
    })
  },

  enable(name: string) {
    return request.put<any>({
      url: `/api/system/plugin/enable/${name}`
    })
  },

  disable(name: string) {
    return request.put<any>({
      url: `/api/system/plugin/disable/${name}`
    })
  },

  getConfig(name: string) {
    return request.get<Record<string, any>>({
      url: `/api/system/plugin/config/${name}`
    })
  },

  updateConfig(name: string, config: Record<string, any>) {
    return request.put<any>({
      url: `/api/system/plugin/config/${name}`,
      data: { config }
    })
  },

  doctor(params?: { name?: string }) {
    return request.get<Record<string, any>>({
      url: '/api/system/plugin/doctor',
      params
    })
  }
}
