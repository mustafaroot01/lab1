-- 003_indexes.sql
-- تسريع الاستعلامات وعمليات البحث (Performance)

-- تسريع جلب رسائل محادثة معينة
CREATE INDEX IF NOT EXISTS idx_messages_conversation_id ON public.messages(conversation_id);

-- تسريع جلب الرسائل حسب التاريخ
CREATE INDEX IF NOT EXISTS idx_messages_created_at ON public.messages(created_at DESC);

-- تسريع جلب محادثات المريض
CREATE INDEX IF NOT EXISTS idx_conversations_patient_id ON public.conversations(patient_id);

-- تسريع جلب المحادثات حسب الترتيب الزمني لآخر رسالة
CREATE INDEX IF NOT EXISTS idx_conversations_last_message_at ON public.conversations(last_message_at DESC);

-- تسريع جلب المحادثات الخاصة بموظف معين (Assignee)
CREATE INDEX IF NOT EXISTS idx_conversations_assigned_to ON public.conversations(assigned_to);

-- تسريع جلب الإشعارات والمقروء للمشاركين
CREATE INDEX IF NOT EXISTS idx_participants_user ON public.conversation_participants(user_type, user_id);
