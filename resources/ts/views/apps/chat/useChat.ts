export const useChat = () => {
  const resolveStatusColor = (status: 'open' | 'closed') => {
    if (status === 'open')
      return 'success'

    return 'error'
  }

  return {
    resolveStatusColor,
  }
}
