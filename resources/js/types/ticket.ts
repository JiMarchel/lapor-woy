export type Ticket = {
    id: string;
    title: string;
    description: string;
    priority: string;
    status: string;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
    user_id: string;
    image_url: string | null;
}