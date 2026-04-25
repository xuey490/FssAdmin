/**
 * 菜单处理器
 *
 * 负责菜单数据的获取、过滤和处理
 *
 * @module router/core/MenuProcessor
 * @author Art Design Pro Team
 */

import type { AppRouteRecord } from '@/types/router'
import { useUserStore } from '@/store/modules/user'
import { useAppMode } from '@/hooks/core/useAppMode'
import { fetchGetMenuList } from '@/api/auth'
import { asyncRoutes } from '../routes/asyncRoutes'
import { RoutesAlias } from '../routesAlias'

export class MenuProcessor {
  /**
   * 获取菜单数据
   */
  async getMenuList(): Promise<AppRouteRecord[]> {
    const { isFrontendMode } = useAppMode()

    let menuList: AppRouteRecord[]
    if (isFrontendMode.value) {
      menuList = await this.processFrontendMenu()
    } else {
      menuList = await this.processBackendMenu()
    }

    // 规范化路径（将相对路径转换为完整路径）
    return this.normalizeMenuPaths(menuList)
  }

  /**
   * 处理前端控制模式的菜单
   */
  private async processFrontendMenu(): Promise<AppRouteRecord[]> {
    const userStore = useUserStore()
    const roles = userStore.info?.roles

    let menuList = [...asyncRoutes]

    // 根据角色过滤菜单
    if (roles && roles.length > 0) {
      menuList = this.filterMenuByRoles(menuList, roles)
    }

    return this.filterEmptyMenus(menuList)
  }

  /**
   * 处理后端控制模式的菜单
   */
  private async processBackendMenu(): Promise<AppRouteRecord[]> {
    const list = await fetchGetMenuList()
    // 转换后端菜单数据为前端路由格式
    const convertedList = this.convertBackendMenuToRoute(list)
    return this.filterEmptyMenus(convertedList)
  }

  /**
   * 将后端菜单数据转换为前端路由格式
   */
  private convertBackendMenuToRoute(menuList: any[]): AppRouteRecord[] {
    if (!Array.isArray(menuList) || menuList.length === 0) {
      return []
    }

    return menuList.map((menu) => {
      // 强制把所有一级菜单且组件为空（或者只是目录类型）的组件指向布局容器
      if (!menu.parent_id && !menu.component) {
        menu.component = RoutesAlias.Layout
      }

      const route: AppRouteRecord = {
        id: menu.id,
        path: menu.path || '',
        name: menu.path ? this.generateRouteName(menu.path) : '',
        component: menu.component || '',
        meta: {
          title: menu.name || '',
          icon: menu.icon || '',
          isHide: menu.is_hidden === 1,
          isCache: menu.is_keep_alive === 1,
          fixedTab: menu.is_fixed_tab === 1,
          isIframe: menu.is_iframe === 1,
          link: menu.is_iframe === 1 ? menu.path : undefined,
          roles: [],
          permissions: menu.slug ? [menu.slug] : []
        }
      }

      // 递归处理子菜单
      if (menu.children && Array.isArray(menu.children) && menu.children.length > 0) {
        route.children = this.convertBackendMenuToRoute(menu.children)
      }

      return route
    })
  }

  /**
   * 根据路径生成路由名称
   */
  private generateRouteName(path: string): string {
    if (!path) return ''
    // 移除开头的斜杠，将斜杠替换为驼峰命名
    return path
      .replace(/^\//, '')
      .split('/')
      .map((segment, index) => {
        if (index === 0) return segment
        return segment.charAt(0).toUpperCase() + segment.slice(1)
      })
      .join('')
  }

  /**
   * 根据角色过滤菜单
   */
  private filterMenuByRoles(menu: AppRouteRecord[], roles: string[]): AppRouteRecord[] {
    return menu.reduce((acc: AppRouteRecord[], item) => {
      const itemRoles = item.meta?.roles
      const hasPermission = !itemRoles || itemRoles.some((role) => roles?.includes(role))

      if (hasPermission) {
        const filteredItem = { ...item }
        if (filteredItem.children?.length) {
          filteredItem.children = this.filterMenuByRoles(filteredItem.children, roles)
        }
        acc.push(filteredItem)
      }

      return acc
    }, [])
  }

  /**
   * 递归过滤空菜单项
   */
  private filterEmptyMenus(menuList: AppRouteRecord[]): AppRouteRecord[] {
    return menuList
      .map((item) => {
        // 如果有子菜单，先递归过滤子菜单
        if (item.children && item.children.length > 0) {
          const filteredChildren = this.filterEmptyMenus(item.children)
          return {
            ...item,
            children: filteredChildren
          }
        }
        return item
      })
      .filter((item) => {
        // 如果定义了 children 属性且有子节点，保留
        if (item.children && item.children.length > 0) {
          return true
        }

        // 如果有外链或 iframe，保留
        if (item.meta?.isIframe === true || item.meta?.link) {
          return true
        }

        // 如果有有效的 component，保留
        if (item.component && item.component !== '' && item.component !== RoutesAlias.Layout) {
          return true
        }
        
        // 允许顶级空目录被保留（由 RouteValidator 验证和处理）
        if (item.path && !item.component && (!item.children || item.children.length === 0)) {
           return true
        }

        // 其他情况过滤掉
        return false
      })
  }

  /**
   * 验证菜单列表是否有效
   */
  validateMenuList(menuList: AppRouteRecord[]): boolean {
    return Array.isArray(menuList) && menuList.length > 0
  }

  /**
   * 规范化菜单路径
   * 将相对路径转换为完整路径，确保菜单跳转正确
   */
  private normalizeMenuPaths(menuList: AppRouteRecord[], parentPath = ''): AppRouteRecord[] {
    return menuList.map((item) => {
      // 构建完整路径
      const fullPath = this.buildFullPath(item.path || '', parentPath)

      // 递归处理子菜单
      const children = item.children?.length
        ? this.normalizeMenuPaths(item.children, fullPath)
        : item.children

      return {
        ...item,
        path: fullPath,
        children
      }
    })
  }

  /**
   * 构建完整路径
   */
  private buildFullPath(path: string, parentPath: string): string {
    if (!path) return ''

    // 外部链接直接返回
    if (path.startsWith('http://') || path.startsWith('https://')) {
      return path
    }

    // 如果已经是绝对路径，直接返回
    if (path.startsWith('/')) {
      return path
    }

    // 拼接父路径和当前路径
    if (parentPath) {
      // 移除父路径末尾的斜杠，移除子路径开头的斜杠，然后拼接
      const cleanParent = parentPath.replace(/\/$/, '')
      const cleanChild = path.replace(/^\//, '')
      return `${cleanParent}/${cleanChild}`
    }

    // 没有父路径，添加前导斜杠
    return `/${path}`
  }
}
