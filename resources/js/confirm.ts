import { ref } from 'vue';

export interface ConfirmState {
    isOpen: boolean;
    message: string;
    onConfirm: (() => void) | null;
    onCancel: (() => void) | null;
}

export const confirmState = ref<ConfirmState>({
    isOpen: false,
    message: '',
    onConfirm: null,
    onCancel: null,
});

export const showConfirm = (message: string): Promise<boolean> => {
    return new Promise<boolean>((resolve) => {
        confirmState.value = {
            isOpen: true,
            message,
            onConfirm: () => {
                confirmState.value.isOpen = false;
                resolve(true);
            },
            onCancel: () => {
                confirmState.value.isOpen = false;
                resolve(false);
            },
        };
    });
};

if (typeof window !== 'undefined') {
    (window as any).showConfirm = showConfirm;
}
