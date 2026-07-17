import apps from './apps'
import dashboard from './dashboard'
import type { HorizontalNavItems } from '@layouts/types'

export default [...dashboard, ...apps] as HorizontalNavItems
