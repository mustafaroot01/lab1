-- 001_create_conversations.sql
-- إنشاء جدول المحادثات
CREATE TABLE IF NOT EXISTS public.conversations (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    patient_id BIGINT NOT NULL, -- يربط المحادثة مع المريض في Laravel MySQL
    status VARCHAR(50) DEFAULT 'OPEN', -- OPEN, CLOSED
    last_message TEXT NULL,
    last_message_at TIMESTAMP WITH TIME ZONE NULL,
    last_sender_id VARCHAR(255) NULL,
    
    -- نظام تعيين التذاكر للموظفين
    assigned_to VARCHAR(255) NULL, -- Laravel Admin ID
    is_assigned BOOLEAN DEFAULT false,
    assigned_at TIMESTAMP WITH TIME ZONE NULL,
    
    -- تسجيل من قام بالاستلام فعلياً
    claimed_by VARCHAR(255) NULL,
    claimed_at TIMESTAMP WITH TIME ZONE NULL,
    
    -- تواريخ المحادثة
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    closed_at TIMESTAMP WITH TIME ZONE NULL
);

-- إنشاء جدول المشاركين (لمعرفة آخر رسالة مقروءة)
CREATE TABLE IF NOT EXISTS public.conversation_participants (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    conversation_id UUID NOT NULL REFERENCES public.conversations(id) ON DELETE CASCADE,
    user_id VARCHAR(255) NOT NULL, -- Laravel ID
    user_type VARCHAR(50) NOT NULL, -- Patient / Admin
    last_read_message_id UUID NULL,
    last_read_at TIMESTAMP WITH TIME ZONE NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    UNIQUE(conversation_id, user_id, user_type)
);
