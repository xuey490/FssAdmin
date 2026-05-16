/**
 * 基础类型定义模块
 *
 * @module types/sai/index
 * @author saithink
 */

/**
 * 对话框Props类型
 */
export interface Props {
  visible: boolean
  dialogType: string
  dialogData?: Partial<Record<string, any>>
}

/**
 * 对话框Emits类型
 */
export interface Emits {
  (e: 'update:visible', value: boolean): void
  (e: 'submit'): void
}
