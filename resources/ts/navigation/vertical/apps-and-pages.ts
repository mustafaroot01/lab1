export default [
  { heading: 'الدعم والمحادثات المباشرة' },
  {
    title: 'الدردشة والدعم المباشر ',
    icon: { icon: 'tabler-message-dots' },
    to: 'apps-chat',
  },

  { heading: 'المرضى والطلبات المنزلية' },
  {
    title: 'طلبات التحاليل المنزلية ',
    icon: { icon: 'tabler-clipboard-list' },
    to: 'orders',
  },
  {
    title: 'المرضى والعملاء المسجلون ',
    icon: { icon: 'tabler-users' },
    to: 'patients',
  },

  { heading: 'قاموس التحاليل المخبرية' },
  {
    title: 'قاموس التحاليل ',
    icon: { icon: 'tabler-flask' },
    children: [
      {
        title: 'قائمة جميع التحاليل',
        to: 'medical-dictionary-tests',
      },
      {
        title: 'مجموعات التحاليل',
        to: 'medical-dictionary-groups',
      },
      {
        title: 'أنواع العينات المخبرية',
        to: 'medical-dictionary-sample-types',
      },
      {
        title: 'أنواع أنابيب السحب',
        to: 'medical-dictionary-tube-types',
      },
    ],
  },

  { heading: 'التسويق والعروض والمحتوى' },
  {
    title: 'الباقات والعروض المخبرية ',
    icon: { icon: 'tabler-packages' },
    to: 'packages',
  },
  {
    title: 'إعلانات الستوري والبوب أب ',
    icon: { icon: 'tabler-device-mobile-message' },
    to: 'apps-popup-stories',
  },
  {
    title: 'الكوبونات ورموز الخصم ',
    icon: { icon: 'tabler-discount-2' },
    to: 'coupons',
  },
  {
    title: 'البنرات الإعلانية ',
    icon: { icon: 'tabler-photo' },
    to: 'banners',
  },

  { heading: 'التغطية الجغرافية والفنيون' },
  {
    title: 'نظام التغطية الجغرافية',
    icon: { icon: 'tabler-map-search' },
    children: [
      {
        title: 'لوحة القيادة (Dashboard)',
        to: 'coverage-dashboard',
      },
      {
        title: 'إدارة المناطق (Zones)',
        to: 'coverage-zones',
      },
      {
        title: 'أداة المحاكاة (Simulator)',
        to: 'coverage-simulator',
      },
      {
        title: 'سجلات الأداء (Logs)',
        to: 'coverage-logs',
      },
      {
        title: 'إعدادات التغطية (Settings)',
        to: 'coverage-settings',
      },
    ],
  },
  {
    title: 'إدارة الفنيين الميدانيين ',
    icon: { icon: 'tabler-stethoscope' },
    to: 'technicians',
  },

  { heading: 'الصفحات والأسئلة الشائعة' },
  {
    title: 'الأسئلة الشائعة ',
    icon: { icon: 'tabler-help-circle' },
    to: 'faqs',
  },
  {
    title: 'الصفحات القانونية ',
    icon: { icon: 'tabler-file-text' },
    to: 'legal-pages',
  },
  {
    title: 'معلومات التواصل ',
    icon: { icon: 'tabler-phone-call' },
    to: 'contact-infos',
  },

  { heading: 'إعدادات النظام والأمان' },
  {
    title: 'الإعدادات العامّة ',
    icon: { icon: 'tabler-settings' },
    to: 'settings-general',
  },
  {
    title: 'إعدادات تحقق الواتساب (OTP)',
    icon: { icon: 'tabler-lock-square' },
    to: 'settings-otp',
  },
  {
    title: 'إعدادات الإشعارات (OneSignal)',
    icon: { icon: 'tabler-bell-ringing' },
    to: 'settings-onesignal',
  },
  {
    title: 'إعدادات الدردشة (Supabase)',
    icon: { icon: 'tabler-database' },
    to: 'settings-supabase',
  },
  {
    title: 'أوقات وساعات العمل',
    icon: { icon: 'tabler-clock' },
    to: 'settings-working-hours',
  },
]

