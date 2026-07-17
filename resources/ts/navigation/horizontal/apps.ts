export default [
  {
    title: 'الدعم والدردشة 💬',
    icon: { icon: 'tabler-message-dots' },
    to: 'apps-chat',
  },
  {
    title: 'الطلبات والمرضى 📋',
    icon: { icon: 'tabler-clipboard-list' },
    children: [
      { title: 'طلبات التحاليل المنزلية', to: 'orders' },
      { title: 'المرضى والعملاء المسجلون', to: 'patients' },
    ],
  },
  {
    title: 'التحاليل والعروض 🧪',
    icon: { icon: 'tabler-flask' },
    children: [
      { title: 'قائمة جميع التحاليل', to: 'medical-dictionary-tests' },
      { title: 'مجموعات التحاليل', to: 'medical-dictionary-groups' },
      { title: 'أنواع العينات المخبرية', to: 'medical-dictionary-sample-types' },
      { title: 'أنواع أنابيب السحب', to: 'medical-dictionary-tube-types' },
      { title: 'الباقات والعروض المخبرية', to: 'packages' },
      { title: 'إعلانات الستوري والبوب أب', to: 'apps-popup-stories' },
      { title: 'الكوبونات ورموز الخصم', to: 'coupons' },
      { title: 'البنرات الإعلانية', to: 'banners' },
    ],
  },
  {
    title: 'المناطق والفروع 🏥',
    icon: { icon: 'tabler-building-store' },
    children: [
      { title: 'إدارة الفروع المخبرية', to: 'branches' },
      { title: 'الأقضية والمناطق (التغطية)', to: 'districts' },
      { title: 'كلفة ورسوم الخدمة', to: 'service-fees' },
      { title: 'إدارة الفنيين الميدانيين', to: 'technicians' },
    ],
  },
  {
    title: 'الصفحات والإعدادات ⚙️',
    icon: { icon: 'tabler-settings' },
    children: [
      { title: 'الأسئلة الشائعة', to: 'faqs' },
      { title: 'الصفحات القانونية', to: 'legal-pages' },
      { title: 'معلومات التواصل', to: 'contact-infos' },
      { title: 'الإعدادات العامّة', to: 'settings-general' },
      { title: 'إعدادات تحقق الواتساب (OTP)', to: 'settings-otp' },
    ],
  },
]
