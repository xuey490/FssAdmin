import { AppRouteRecord } from '@/types/router'
import { dashboardRoutes } from './dashboard'
import { systemRoutes } from './system'
import { resultRoutes } from './result'
import { exceptionRoutes } from './exception'
import { articleRoutes } from './article'
import { flowRoutes } from './flow'

/**
 * 导出所有模块化路由
 */
export const routeModules: AppRouteRecord[] = [
  dashboardRoutes,
  systemRoutes,
  flowRoutes,
  resultRoutes,
  exceptionRoutes,
  articleRoutes
]
