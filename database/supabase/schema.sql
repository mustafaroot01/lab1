-- =========================================================
-- Supabase Schema for Hybrid Chat System
-- =========================================================
-- Note: Enable the "uuid-ossp" extension in Supabase if not already enabled.
-- CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Conversations Table
CREATE TABLE conversations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    status VARCHAR(50) DEFAULT 'OPEN', -- e.g. OPEN, CLOSED
    last_message TEXT,
    last_message_at TIMESTAMP WITH TIME ZONE,
    last_sender_id VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 2. Conversation Participants Table
-- Tracks who is in the conversation and their read status
CREATE TABLE conversation_participants (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    conversation_id UUID NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
    user_type VARCHAR(50) NOT NULL, -- e.g. 'Patient', 'Admin'
    user_id VARCHAR(255) NOT NULL, -- The ID from Laravel Database
    last_read_message_id UUID,
    last_read_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 3. Messages Table
CREATE TABLE messages (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    client_message_id UUID UNIQUE, -- Sent from frontend to prevent duplicates (Idempotency)
    conversation_id UUID NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
    sender_type VARCHAR(50) NOT NULL,
    sender_id VARCHAR(255) NOT NULL,
    message_type VARCHAR(50) DEFAULT 'TEXT', -- TEXT, IMAGE
    text TEXT,
    attachment_url TEXT,
    metadata JSONB, -- For extra data like {"width": 1080, "height": 1920, "size": 254123}
    status VARCHAR(50) DEFAULT 'SENT', -- SENT, DELIVERED, READ
    deleted_at TIMESTAMP WITH TIME ZONE, -- Soft deletes
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =========================================================
-- Indexes for Performance
-- =========================================================
CREATE INDEX idx_conv_participants_conv_id ON conversation_participants(conversation_id);
CREATE INDEX idx_conv_participants_user ON conversation_participants(user_type, user_id);
CREATE INDEX idx_messages_conv_id ON messages(conversation_id);
CREATE INDEX idx_messages_client_msg_id ON messages(client_message_id);
CREATE INDEX idx_messages_created_at ON messages(created_at DESC);

-- =========================================================
-- Realtime Replication
-- =========================================================
-- IMPORTANT: Make sure to enable replication for these tables in the Supabase Dashboard
-- or run the following:
ALTER PUBLICATION supabase_realtime ADD TABLE conversations;
ALTER PUBLICATION supabase_realtime ADD TABLE conversation_participants;
ALTER PUBLICATION supabase_realtime ADD TABLE messages;
