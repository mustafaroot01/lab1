-- 002_create_messages.sql
-- إنشاء جدول الرسائل الفورية
CREATE TABLE IF NOT EXISTS public.messages (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    client_message_id VARCHAR(255) NULL, -- لدعم الـ Offline Queueing
    conversation_id UUID NOT NULL REFERENCES public.conversations(id) ON DELETE CASCADE,
    
    sender_type VARCHAR(50) NOT NULL, -- Patient / Admin / System
    sender_id VARCHAR(255) NOT NULL, -- Laravel ID
    
    message_type VARCHAR(50) DEFAULT 'TEXT', -- TEXT, IMAGE, SYSTEM
    text TEXT NULL,
    attachment_url TEXT NULL, -- الرابط يأتي من Laravel (S3 / Local)
    metadata JSONB NULL, -- لمعلومات مثل عرض/ارتفاع الصورة أو الحجم
    
    status VARCHAR(50) DEFAULT 'SENT', -- SENT, DELIVERED, READ
    
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    edited_at TIMESTAMP WITH TIME ZONE NULL
);

-- تفعيل Real-time على جدول الرسائل
ALTER PUBLICATION supabase_realtime ADD TABLE public.messages;
ALTER PUBLICATION supabase_realtime ADD TABLE public.conversations;
