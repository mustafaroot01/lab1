export const paginationMeta = <T extends { page: number; itemsPerPage: number }>(options: T, total: number) => {
  const start = (options.page - 1) * options.itemsPerPage + 1
  const end = Math.min(options.page * options.itemsPerPage, total)

  if (total === 0) return 'لا توجد عناصر للعرض'

  return `عرض ${start} إلى ${end} من إجمالي ${total} عنصر`
}

