import { format } from 'date-fns'

/**
 * Format time string - handles both time-only (HH:MM:SS) and datetime formats
 * @param {string} timeString - Time string in HH:MM:SS or datetime format
 * @param {string} formatStr - Date-fns format string (default: 'HH:mm')
 * @returns {string} Formatted time string
 */
export const formatTime = (timeString, formatStr = 'HH:mm') => {
  if (!timeString) return ''
  
  try {
    // If it's just a time string (HH:MM:SS), create a date object for today
    if (timeString.match(/^\d{1,2}:\d{2}(:\d{2})?$/)) {
      const today = new Date().toISOString().split('T')[0] // Get today's date
      const timeWithSeconds = timeString.length === 5 ? `${timeString}:00` : timeString
      return format(new Date(`${today}T${timeWithSeconds}`), formatStr)
    }
    
    // If it's a full datetime, use as is
    return format(new Date(timeString), formatStr)
  } catch (error) {
    console.error('Error formatting time:', error, 'Input:', timeString)
    return timeString || ''
  }
}

/**
 * Format datetime string
 * @param {string} datetime - Datetime string
 * @param {string} formatStr - Date-fns format string (default: 'HH:mm')
 * @returns {string} Formatted datetime string
 */
export const formatDateTime = (datetime, formatStr = 'HH:mm') => {
  if (!datetime) return ''
  
  try {
    return format(new Date(datetime), formatStr)
  } catch (error) {
    console.error('Error formatting datetime:', error, 'Input:', datetime)
    return datetime || ''
  }
}

/**
 * Format date string
 * @param {string} datetime - Datetime string
 * @param {string} formatStr - Date-fns format string (default: 'MMM dd, yyyy')
 * @returns {string} Formatted date string
 */
export const formatDate = (datetime, formatStr = 'MMM dd, yyyy') => {
  if (!datetime) return ''
  
  try {
    return format(new Date(datetime), formatStr)
  } catch (error) {
    console.error('Error formatting date:', error, 'Input:', datetime)
    return datetime || ''
  }
}
