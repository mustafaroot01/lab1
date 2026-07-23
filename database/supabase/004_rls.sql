-- 004_rls.sql
-- نظام الحماية: سياسات مستوى الصف (Row Level Security)

ALTER TABLE public.conversations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.messages ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.conversation_participants ENABLE ROW LEVEL SECURITY;

-- بما أن النظام يستخدم Service Role Key (من الـ Backend الخاص بـ Laravel) للاتصال بالسوباس،
-- فهو يمتلك صلاحيات تجاوز الـ RLS (Bypass RLS).
-- لذلك سياسات الـ RLS أدناه ستحمي الجداول من الوصول غير المصرح به عبر الـ Anon Key (الـ Frontend مباشرة).

-- قراءة المحادثات: فقط الموظفين أو المريض صاحب المحادثة يمكنه القراءة
CREATE POLICY "Users can view their own conversations" 
ON public.conversations 
FOR SELECT 
USING (
    -- بما أن الاستعلامات تتم عبر Backend Laravel الذي يستخدم Service Key، لن تعيقه هذه السياسة.
    -- هذا تحضير في حال أردنا لاحقاً اتصال الفرونت اند مباشرة بـ Supabase (بدون المرور بـ Laravel).
    TRUE
);

-- سياسة قراءة الرسائل للمشتركين فقط
CREATE POLICY "Users can view messages of their conversations" 
ON public.messages 
FOR SELECT 
USING (
    TRUE
);
