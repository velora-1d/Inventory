export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    warehouse_id?: number | null;
    status?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
