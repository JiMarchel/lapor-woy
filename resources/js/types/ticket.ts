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
    unread_replies_count?: number;
    user?: {
        name: string;
    };
}

export type TicketReply = {
    id: string;
    ticket_id: string;
    user_id: string;
    content: string;
    is_read: boolean;
    created_at: string;
    updated_at: string;
    user?: {
        name: string;
        role: string;
    }
}